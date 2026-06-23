<?php

namespace App\Jobs;

use App\Models\PromoterCommissionOverride;
use App\Models\TicketOrder; // Your TicketOrder model
use App\Models\TicketOrderCommission;
use App\Models\User;       // Your User/Promoter model
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class OrderCompleted implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected TicketOrder $orderInstance; // Renamed to avoid confusion with a local $order variable

    /**
     * How many times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60; // e.g., 1 minute

    /**
     * Create a new job instance.
     *
     * @param TicketOrder $order The completed order instance
     */
    public function __construct(TicketOrder $order)
    {
        $this->orderInstance = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Fetch a fresh instance of the order to ensure up-to-date relations and status,
        // especially important if the job was delayed or retried.
        $currentOrder = TicketOrder::with([
            'requestedBy',
            'items',
            'commissionBeneficiaries',
        ])->find($this->orderInstance->id);

        if (!$currentOrder) {
            Log::error("[OrderCompleted Job] Order ID: {$this->orderInstance->id} not found. Job cannot proceed.");
            // No point in retrying if the order doesn't exist.
            $this->fail(new \Exception("Order ID {$this->orderInstance->id} not found."));
            return;
        }

        Log::info("[OrderCompleted Job] Starting for Order ID: {$currentOrder->id}. Current job_status: {$currentOrder->job_status}");

        try {
            // Calculate and store commission for the current order
            $commissionChanged = $this->storeOrderCommissionForOrder($currentOrder);

            // If the current order just got its commission calculated/updated,
            // or if it just moved to 'completed' status (meaning its sales volume now counts),
            // trigger recalculation for subsequent orders by the same promoter.
            // The act of this order completing is the trigger.
            if ($currentOrder->job_status === 'completed') {
                $this->triggerRecalculationForSubsequentOrders($currentOrder);
            }

        } catch (Throwable $e) {
            Log::error("[OrderCompleted Job] Exception for Order ID: {$currentOrder->id}. Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            $this->fail($e); // Mark the job as failed
        }
    }

    /**
     * Calculate and store commission for the given order.
     * This method can be called for initial calculation or recalculation.
     *
     * @param TicketOrder $order
     * @return bool True if commission was changed/newly set, false otherwise
     */
    private function storeOrderCommissionForOrder(TicketOrder $order): bool
    {
        if ($order->job_status !== 'completed') {
            Log::warning("[storeOrderCommission] Order ID: {$order->id} is not 'completed' (status: '{$order->job_status}'). Skipping commission calculation.");
            return false;
        }

        $originalCommission = $order->total_commission_earned;
        $seller = $order->requestedBy; // The user who created/placed the order

        if (!$seller) {
            Log::error("[storeOrderCommission] Seller (requested_by) not found for Order ID {$order->id}.");
            throw new \RuntimeException("Seller not found for order {$order->id} during commission calculation.");
        }

        if ($order->items->isEmpty()) {
            Log::info("[storeOrderCommission] Order ID {$order->id} has no items. Setting total commission to 0.");
            DB::transaction(function () use ($order) {
                $order->commissionBeneficiaries()->delete();
                $order->total_commission_earned = 0.00;
                $order->save();
            });
            return true;
        }

        // Build the new per-beneficiary detail rows. We compute them in memory
        // first, then delete-and-insert inside a single transaction to keep
        // the data consistent across recalculations.
        $newRows = [];
        $newTotal = 0.0;

        foreach ($order->items as $item) {
            // 1. Tier-based gross commission for this item (same logic as
            //    before, computed against the direct seller).
            $itemCommission = (float) User::calculateCommission(
                $item->ticket_type_id,
                $order->id,
                $item->quantity,
                $seller,
                $order->created_at
            );

            $item->commission_earned = $itemCommission;
            $item->save();

            // 2. Split the item commission across beneficiaries.
            $splits = $this->splitCommissionForBeneficiaries(
                $seller,
                $item->ticket_type_id,
                $itemCommission,
                $item->quantity
            );

            foreach ($splits as $split) {
                $newRows[] = [
                    'ticket_order_id'      => $order->id,
                    'ticket_order_item_id' => $item->id,
                    'beneficiary_user_id'  => $split['user_id'],
                    'beneficiary_role'     => $split['role'],
                    'quantity'             => $item->quantity,
                    'commission_amount'    => round($split['amount'], 2),
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
                $newTotal += $split['amount'];
            }
        }

        $newTotal = round($newTotal, 2);

        DB::transaction(function () use ($order, $newRows, $newTotal) {
            // Drop any existing detail rows - we are rewriting them.
            $order->commissionBeneficiaries()->delete();
            if (!empty($newRows)) {
                TicketOrderCommission::insert($newRows);
            }
            $order->total_commission_earned = $newTotal;
            $order->save();
        });

        $precision = 2;
        $commissionHasChanged = $originalCommission === null
            || bccomp((string)$originalCommission, (string)$newTotal, $precision) !== 0;

        if ($commissionHasChanged) {
            Log::info("[storeOrderCommission] Order ID: {$order->id}. Commission " . ($originalCommission === null ? "CALCULATED" : "RECALCULATED") . ". Old: " . ($originalCommission ?? 'NULL') . ", New: {$newTotal}.");
            return true;
        }

        Log::info("[storeOrderCommission] Order ID: {$order->id}. Commission value unchanged at {$newTotal}.");
        return false;
    }

    /**
     * Compute the per-beneficiary commission split for a single order item.
     *
     * Rules:
     *  - admin / supreme sellers earn nothing (organisers).
     *  - promoter / promoter_manager sellers earn 100% (same tier rules).
     *  - sub_promoter sellers have their commission split with their
     *    promoter_manager according to promoter_commission_overrides.
     *    The override row is interpreted based on its commission_type:
     *
     *      * 'percentage' (default / legacy): the sub-promoter earns
     *        commission_percentage % of the manager's tier-based gross
     *        commission for every ticket of that type.
     *
     *      * 'fixed': the sub-promoter earns a flat fixed_commission_amount
     *        RSD per ticket, regardless of the manager's tier. The
     *        promoter-manager keeps the remainder (gross - fixed_share);
     *        if the fixed amount exceeds the tier gross the manager gets
     *        zero for that item and the sub-promoter is capped at the
     *        tier gross (never paid more than the order generated).
     *
     *    If no override is defined for (manager, sub, ticket_type) the
     *    sub-promoter earns 100% (acts like an independent promoter) and
     *    the manager earns nothing for that item.
     *
     * @param User $seller
     * @param int $ticketTypeId
     * @param float $grossCommission Total tier-based commission for this item.
     * @param int $quantity
     * @return array<int,array{user_id:int,role:string,amount:float}>
     */
    private function splitCommissionForBeneficiaries(User $seller, int $ticketTypeId, float $grossCommission, int $quantity): array
    {
        // Admins and supreme users do not earn commission.
        if (in_array($seller->role, ['admin', 'supreme'], true)) {
            return [];
        }

        // Promoter and promoter_manager earn the full commission.
        if (in_array($seller->role, ['promoter', 'promoter_manager'], true)) {
            return [[
                'user_id' => $seller->id,
                'role'    => $seller->role,
                'amount'  => $grossCommission,
            ]];
        }

        // Sub-promoter: split with their promoter-manager (if any).
        if ($seller->role === 'sub_promoter') {
            $manager = $seller->promoterManager();

            // No manager -> sub-promoter is "independent" and earns 100%.
            if (!$manager) {
                return [[
                    'user_id' => $seller->id,
                    'role'    => 'sub_promoter',
                    'amount'  => $grossCommission,
                ]];
            }

            // Look for an explicit override for this (manager, sub, type).
            $override = PromoterCommissionOverride::where('promoter_manager_id', $manager->id)
                ->where('sub_promoter_id', $seller->id)
                ->where('ticket_type_id', $ticketTypeId)
                ->first();

            // No override at all: sub-promoter keeps 100%.
            if (!$override) {
                return [[
                    'user_id' => $seller->id,
                    'role'    => 'sub_promoter',
                    'amount'  => $grossCommission,
                ]];
            }

            $rows = [];

            // ----- Fixed-amount mode -----------------------------------
            // The promoter-manager has set a flat RSD amount per ticket.
            // The sub-promoter's share is independent of the tier: they
            // always get fixed_commission_amount * quantity. The manager
            // receives the difference between the tier gross and the fixed
            // share (never less than zero).
            if ($override->isFixed()) {
                $fixedPerTicket = (float) $override->fixed_commission_amount;
                $subShare = round($fixedPerTicket * $quantity, 2);
                // Cap the sub-promoter's share at the gross so we never
                // create a negative remainder for the manager.
                if ($subShare > $grossCommission) {
                    $subShare = round($grossCommission, 2);
                }
                $managerShare = round($grossCommission - $subShare, 2);

                if ($subShare > 0) {
                    $rows[] = [
                        'user_id' => $seller->id,
                        'role'    => 'sub_promoter',
                        'amount'  => $subShare,
                    ];
                }
                if ($managerShare > 0) {
                    $rows[] = [
                        'user_id' => $manager->id,
                        'role'    => 'promoter_manager',
                        'amount'  => $managerShare,
                    ];
                }
                return $rows;
            }

            // ----- Percentage mode (default / legacy) -------------------
            $subPct = (float) $override->commission_percentage;
            $subPct = max(0.0, min(100.0, $subPct));
            $managerPct = 100.0 - $subPct;

            if ($subPct > 0) {
                $rows[] = [
                    'user_id' => $seller->id,
                    'role'    => 'sub_promoter',
                    'amount'  => round($grossCommission * ($subPct / 100.0), 2),
                ];
            }
            if ($managerPct > 0) {
                $rows[] = [
                    'user_id' => $manager->id,
                    'role'    => 'promoter_manager',
                    'amount'  => round($grossCommission * ($managerPct / 100.0), 2),
                ];
            }
            return $rows;
        }

        // Buyers and any other roles earn no commission.
        return [];
    }

    /**
     * Finds subsequent completed orders by the same promoter and dispatches
     * OrderCompleted jobs for them to recalculate their commission.
     *
     * @param TicketOrder $justCompletedOrder The order that just had its commission processed.
     */
    private function triggerRecalculationForSubsequentOrders(TicketOrder $justCompletedOrder): void
    {
        Log::info("[triggerRecalculation] Checking for subsequent orders to Order ID: {$justCompletedOrder->id} by Promoter ID: {$justCompletedOrder->requested_by} that may need commission recalculation.");

        $subsequentCompletedOrders = TicketOrder::where('requested_by', $justCompletedOrder->requested_by)
            ->where('id', '>', $justCompletedOrder->id) // Orders created after the one that just completed
            ->where('job_status', 'completed')           // That are already marked as completed
            ->orderBy('id', 'asc') // Process them in their creation order
            ->get();

        if ($subsequentCompletedOrders->isEmpty()) {
            Log::info("[triggerRecalculation] No subsequent completed orders found requiring potential commission recalculation for Order ID: {$justCompletedOrder->id}.");
            return;
        }

        Log::info("[triggerRecalculation] Found " . $subsequentCompletedOrders->count() . " subsequent orders to re-evaluate commission for. IDs: " . $subsequentCompletedOrders->pluck('id')->implode(', '));

        foreach ($subsequentCompletedOrders as $orderToRecalculate) {
            Log::info("[triggerRecalculation] Dispatching OrderCompleted job for subsequent Order ID: {$orderToRecalculate->id} to re-evaluate commission.");
            OrderCompleted::dispatch($orderToRecalculate)->onQueue('commission_recalc');
        }
    }
}
