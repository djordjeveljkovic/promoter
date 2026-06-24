<?php

namespace App\Services;

use App\Models\SubPromoterPayment;
use App\Models\TicketOrder;
use App\Models\TicketOrderCommission;
use App\Models\TicketOrderItem;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Computes the running debt that flows up the promoter hierarchy:
 *
 *      [event organizers]
 *              ▲  (debt: manager → organizers, gross - every team commission)
 *      [promoter-manager]
 *              ▲  (debt: each sub → manager, gross - sub's own commission)
 *      [sub-promoter ...]
 *
 * The service centralises the "what does X owe Y right now" formula so
 * the dashboard, the sub-promoter list and the payment-recording forms
 * all agree on the numbers.
 */
class DebtService
{
    /** @var string[] Order statuses that count as "successful sale" for debt purposes. */
    public const SUCCESS_STATUSES = ['completed', 'sent'];

    /**
     * Amount the given sub-promoter currently owes to his promoter-manager.
     *
     * Calculation per sub-promoter:
     *   gross (sum of every successful order the sub placed)
     *   minus the sub's own commission (per-beneficiary rows)
     *   minus the payments the sub has already made to the manager.
     *
     * @return array{
     *     gross_sales: float,
     *     sub_commission: float,
     *     amount_owed_to_manager: float,
     *     amount_already_paid: float,
     *     manager_id: int|null,
     * }
     */
    public function subPromoterDebt(User $sub): array
    {
        $sub = $this->resolveSub($sub);

        $grossSales = (float) TicketOrder::where('requested_by', $sub->id)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->sum('total');

        $subCommission = (float) TicketOrderCommission::where('beneficiary_user_id', $sub->id)
            ->where('beneficiary_role', 'sub_promoter')
            ->sum('commission_amount');

        $amountAlreadyPaid = (float) SubPromoterPayment::where('payment_type', SubPromoterPayment::TYPE_SUB_TO_MANAGER)
            ->where('payer_id', $sub->id)
            ->sum('amount');

        $manager = $sub->promoterManager();
        $amountOwedToManager = $grossSales - $subCommission - $amountAlreadyPaid;

        return [
            'gross_sales'           => round($grossSales, 2),
            'sub_commission'        => round($subCommission, 2),
            'amount_owed_to_manager'=> round($amountOwedToManager, 2),
            'amount_already_paid'   => round($amountAlreadyPaid, 2),
            'manager_id'            => $manager?->id,
        ];
    }

    /**
     * Amount the given promoter-manager currently owes to the organizers.
     *
     * The math always treats the manager's own sales and his sub-promoters'
     * sales as separate buckets, so callers can render an analytics view
     * that doesn't conflate "what I personally sold" with "what my team
     * sold". The legacy `gross_sales` field is kept (= manager + subs) for
     * backward compatibility with existing callers.
     *
     * Calculation per manager:
     *   gross (sum of every successful order the manager OR any of his
     *          sub-promoters placed) — i.e. the team's gross revenue
     *   minus the FULL commission pool (manager's share + every sub's share)
     *   minus the amount the manager has already forwarded to organizers
     *   (tracked in SubPromoterPayment rows of type 'manager_to_organizers').
     *
     * @return array{
     *     gross_sales: float,
     *     manager_gross_sales: float,
     *     subs_gross_sales: float,
     *     manager_commission: float,
     *     sub_commissions: float,
     *     amount_already_paid_to_organizers: float,
     *     amount_owed_to_organizers: float,
     *     team_user_ids: list<int>,
     * }
     */
    public function promoterManagerDebt(User $manager): array
    {
        $manager = $this->resolveManager($manager);

        $subIds = $manager->subPromoters()->pluck('id');

        $teamUserIds = collect([$manager->id])
            ->merge($subIds)
            ->unique()
            ->values()
            ->all();

        // Combined (manager + subs) gross — kept for backward compat.
        $grossSales = (float) TicketOrder::whereIn('requested_by', $teamUserIds)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->sum('total');

        // Manager's PERSONAL gross sales (orders he placed himself).
        $managerGrossSales = (float) TicketOrder::where('requested_by', $manager->id)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->sum('total');

        // Subs-only gross (what the team contributed).
        $subsGrossSales = round($grossSales - $managerGrossSales, 2);

        $managerCommission = (float) TicketOrderCommission::where('beneficiary_user_id', $manager->id)
            ->where('beneficiary_role', 'promoter_manager')
            ->sum('commission_amount');

        $subCommissions = $subIds->isEmpty()
            ? 0.0
            : (float) TicketOrderCommission::whereIn('beneficiary_user_id', $subIds)
                ->where('beneficiary_role', 'sub_promoter')
                ->sum('commission_amount');

        $amountAlreadyPaidToOrganizers = (float) SubPromoterPayment::where('payment_type', SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS)
            ->where('payer_id', $manager->id)
            ->sum('amount');

        $amountOwedToOrganizers = $grossSales
            - $managerCommission
            - $subCommissions
            - $amountAlreadyPaidToOrganizers;

        return [
            'gross_sales'                    => round($grossSales, 2),
            'manager_gross_sales'            => round($managerGrossSales, 2),
            'subs_gross_sales'               => $subsGrossSales,
            'manager_commission'             => round($managerCommission, 2),
            'sub_commissions'                => round($subCommissions, 2),
            'amount_already_paid_to_organizers' => round($amountAlreadyPaidToOrganizers, 2),
            'amount_owed_to_organizers'      => round($amountOwedToOrganizers, 2),
            'team_user_ids'                  => $teamUserIds,
        ];
    }

    /**
     * Personal activity stats for the manager: orders he placed himself,
     * tickets he personally sold, and his last-30-days commission.
     *
     * Kept separate from promoterManagerDebt() so the dashboard can render
     * "My numbers" without dragging in team-wide numbers, and so the team
     * cards never accidentally fold the manager back into the team.
     *
     * @return array{
     *     gross_sales: float,
     *     commission: float,
     *     orders_count: int,
     *     tickets_sold: int,
     *     commission_last_30_days: float,
     * }
     */
    public function personalManagerActivity(User $manager, ?\DateTimeInterface $since = null): array
    {
        $manager = $this->resolveManager($manager);

        $ordersQuery = TicketOrder::where('requested_by', $manager->id)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false);

        $grossSales = (float) (clone $ordersQuery)->sum('total');
        $ordersCount = (int) (clone $ordersQuery)->count();

        $ticketsSold = (int) TicketOrderItem::whereHas('ticketOrder', function ($q) use ($manager) {
            $q->where('requested_by', $manager->id)
              ->whereIn('job_status', self::SUCCESS_STATUSES);
        })->sum('quantity');

        $commission = (float) TicketOrderCommission::where('beneficiary_user_id', $manager->id)
            ->where('beneficiary_role', 'promoter_manager')
            ->sum('commission_amount');

        $endDate = $since ?? now();
        $startDate30Days = (clone $endDate)->modify('-30 days');

        $commissionLast30Days = (float) TicketOrderCommission::where('beneficiary_user_id', $manager->id)
            ->whereHas('ticketOrder', function ($q) use ($manager, $startDate30Days, $endDate) {
                $q->where('requested_by', $manager->id)
                    ->whereBetween('created_at', [$startDate30Days, $endDate]);
            })
            ->sum('commission_amount');

        return [
            'gross_sales'             => round($grossSales, 2),
            'commission'              => round($commission, 2),
            'orders_count'            => $ordersCount,
            'tickets_sold'            => $ticketsSold,
            'commission_last_30_days' => round($commissionLast30Days, 2),
        ];
    }

    /**
     * Per-sub-promoter debt summary for a manager. Used by the sub-promoter
     * list page to render one card per sub with the live "what does this
     * sub owe me right now" figure.
     *
     * @return Collection<int, array{
     *     user: User,
     *     gross_sales: float,
     *     sub_commission: float,
     *     amount_owed_to_manager: float,
     *     amount_already_paid: float,
     * }>
     */
    public function subDebtsForManager(User $manager): Collection
    {
        $manager = $this->resolveManager($manager);

        return $manager->subPromoters()
            ->orderBy('name')
            ->get()
            ->map(function (User $sub) {
                $debt = $this->subPromoterDebt($sub);
                return [
                    'user'                   => $sub,
                    'gross_sales'            => $debt['gross_sales'],
                    'sub_commission'         => $debt['sub_commission'],
                    'amount_owed_to_manager' => $debt['amount_owed_to_manager'],
                    'amount_already_paid'    => $debt['amount_already_paid'],
                ];
            });
    }

    /**
     * Record a single payment. Wrapped in a transaction so the dashboard
     * totals and the new history row are always in sync.
     *
     * Authorization on the recorder:
     *
     *   - 'sub_to_manager'        → recorder can be the sub's promoter-manager
     *                               OR an admin/superadmin/supreme.
     *   - 'manager_to_organizers' → recorder MUST be an
     *                               admin/superadmin/supreme. The manager
     *                               himself is NOT allowed to record his
     *                               own payment to the organizers.
     */
    public function recordPayment(
        string $type,
        User $payer,
        User $receiver,
        float $amount,
        User $recorder,
        ?string $note = null,
        ?\DateTimeInterface $paidAt = null,
    ): SubPromoterPayment {
        $this->validatePaymentType($type, $payer, $receiver, $recorder);

        return DB::transaction(function () use ($type, $payer, $receiver, $amount, $recorder, $note, $paidAt) {
            return SubPromoterPayment::create([
                'payment_type' => $type,
                'payer_id'     => $payer->id,
                'receiver_id'  => $receiver->id,
                'amount'       => round($amount, 2),
                'note'         => $note,
                'recorded_by'  => $recorder->id,
                'paid_at'      => $paidAt ?? now(),
            ]);
        });
    }

    /**
     * Live cash the manager currently holds in hand: every RSD collected
     * from sub-promoters minus every RSD forwarded to the organizers.
     *
     * Mathematically this is the running balance between the two ledger
     * flows that flow through the manager:
     *
     *   + money received from sub-promoters (sub_to_manager)
     *   - money forwarded to organizers    (manager_to_organizers)
     *
     * Positive  → the manager currently has cash to forward.
     * Zero      → every RSD received has already been forwarded.
     * Negative  → the manager has paid more than he has collected
     *              (e.g. because his own commission was netted against
     *              the forwarded amount). Magnitude is what he still
     *              needs to cover from his own pocket.
     */
    public function cashInHandByManager(User $manager): float
    {
        $manager = $this->resolveManager($manager);

        $receivedFromSubs = (float) SubPromoterPayment::where('payment_type', SubPromoterPayment::TYPE_SUB_TO_MANAGER)
            ->where('receiver_id', $manager->id)
            ->sum('amount');

        $paidToOrganizers = (float) SubPromoterPayment::where('payment_type', SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS)
            ->where('payer_id', $manager->id)
            ->sum('amount');

        return round($receivedFromSubs - $paidToOrganizers, 2);
    }

    /**
     * Per-sub-promoter leaderboard rows: gross sales, sub's commission,
     * manager's commission earned from this sub's sales, amount paid,
     * amount still owed. Sorted by gross revenue DESC.
     *
     * @return Collection<int, array{
     *     user: User,
     *     gross_sales: float,
     *     sub_commission: float,
     *     manager_commission: float,
     *     amount_owed_to_manager: float,
     *     amount_already_paid: float,
     *     orders_count: int,
     *     tickets_sold: int,
     * }>
     */
    public function topSubPromotersBySales(User $manager, int $limit = 10): Collection
    {
        $manager = $this->resolveManager($manager);

        $subIds = $manager->subPromoters()->pluck('id');
        if ($subIds->isEmpty()) {
            return collect();
        }

        // Per-sub gross + order + ticket counts in a single pass so the
        // leaderboard renders in O(1) queries.
        $grossBySub = TicketOrder::whereIn('requested_by', $subIds)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->selectRaw('requested_by, COALESCE(SUM(total), 0) AS gross, COUNT(*) AS orders_count')
            ->groupBy('requested_by')
            ->pluck('gross', 'requested_by');
        $ordersBySub = TicketOrder::whereIn('requested_by', $subIds)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->selectRaw('requested_by, COUNT(*) AS orders_count')
            ->groupBy('requested_by')
            ->pluck('orders_count', 'requested_by');
        $ticketsBySub = TicketOrderItem::whereHas('ticketOrder', function ($q) use ($subIds) {
                $q->whereIn('requested_by', $subIds)
                    ->whereIn('job_status', self::SUCCESS_STATUSES);
            })
            ->selectRaw('ticket_orders.requested_by AS sub_id, COALESCE(SUM(ticket_order_items.quantity), 0) AS tickets')
            ->join('ticket_orders', 'ticket_orders.id', '=', 'ticket_order_items.ticket_order_id')
            ->groupBy('ticket_orders.requested_by')
            ->pluck('tickets', 'sub_id');

        // Sub's own commission (their share on their orders).
        $subCommissionsBySub = TicketOrderCommission::whereIn('beneficiary_user_id', $subIds)
            ->where('beneficiary_role', 'sub_promoter')
            ->selectRaw('beneficiary_user_id, COALESCE(SUM(commission_amount), 0) AS amount')
            ->groupBy('beneficiary_user_id')
            ->pluck('amount', 'beneficiary_user_id');

        // Manager's commission attributable to each sub's orders.
        // (Manager's commission rows on orders the sub placed.)
        $managerCommissionBySub = TicketOrderCommission::where('beneficiary_user_id', $manager->id)
            ->where('beneficiary_role', 'promoter_manager')
            ->whereHas('ticketOrder', function ($q) use ($subIds) {
                $q->whereIn('requested_by', $subIds);
            })
            ->selectRaw('ticket_orders.requested_by AS sub_id, COALESCE(SUM(ticket_order_commissions.commission_amount), 0) AS amount')
            ->join('ticket_orders', 'ticket_orders.id', '=', 'ticket_order_commissions.ticket_order_id')
            ->groupBy('ticket_orders.requested_by')
            ->pluck('amount', 'sub_id');

        // Existing debt rows keep the canonical "paid / owed" pair so the
        // leaderboard agrees with the per-sub cards.
        $debtsBySub = $this->subDebtsForManager($manager)->keyBy('user.id');

        $rows = $manager->subPromoters()->orderBy('name')->get()->map(function (User $sub) use (
            $grossBySub, $ordersBySub, $ticketsBySub, $subCommissionsBySub, $managerCommissionBySub, $debtsBySub
        ) {
            $debt = $debtsBySub[$sub->id] ?? null;
            return [
                'user'                    => $sub,
                'gross_sales'             => round((float) ($grossBySub[$sub->id] ?? 0), 2),
                'sub_commission'          => round((float) ($subCommissionsBySub[$sub->id] ?? 0), 2),
                'manager_commission'      => round((float) ($managerCommissionBySub[$sub->id] ?? 0), 2),
                'amount_owed_to_manager'  => round((float) ($debt['amount_owed_to_manager'] ?? 0), 2),
                'amount_already_paid'     => round((float) ($debt['amount_already_paid'] ?? 0), 2),
                'orders_count'            => (int) ($ordersBySub[$sub->id] ?? 0),
                'tickets_sold'            => (int) ($ticketsBySub[$sub->id] ?? 0),
            ];
        });

        return $rows
            ->sortByDesc('gross_sales')
            ->take($limit)
            ->values();
    }

    /**
     * Per-promoter-manager commission split for the dashboard "My earnings"
     * breakdown: the portion of the manager's commission earned from his
     * own personal sales versus the portion earned on sub-promoter sales.
     *
     * @return array{
     *     personal_commission: float,
     *     sub_commission: float,
     *     total_commission: float,
     *     personal_gross: float,
     *     subs_gross: float,
     * }
     */
    public function managerEarningsBreakdown(User $manager): array
    {
        $manager = $this->resolveManager($manager);

        $subIds = $manager->subPromoters()->pluck('id');

        $personalCommission = (float) TicketOrderCommission::where('beneficiary_user_id', $manager->id)
            ->where('beneficiary_role', 'promoter_manager')
            ->whereHas('ticketOrder', function ($q) use ($manager) {
                $q->where('requested_by', $manager->id);
            })
            ->sum('commission_amount');

        $subCommission = $subIds->isEmpty()
            ? 0.0
            : (float) TicketOrderCommission::where('beneficiary_user_id', $manager->id)
                ->where('beneficiary_role', 'promoter_manager')
                ->whereHas('ticketOrder', function ($q) use ($subIds) {
                    $q->whereIn('requested_by', $subIds);
                })
                ->sum('commission_amount');

        $personalGross = (float) TicketOrder::where('requested_by', $manager->id)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->sum('total');

        $subsGross = $subIds->isEmpty()
            ? 0.0
            : (float) TicketOrder::whereIn('requested_by', $subIds)
                ->whereIn('job_status', self::SUCCESS_STATUSES)
                ->where('is_private', false)
                ->sum('total');

        return [
            'personal_commission' => round($personalCommission, 2),
            'sub_commission'      => round($subCommission, 2),
            'total_commission'    => round($personalCommission + $subCommission, 2),
            'personal_gross'      => round($personalGross, 2),
            'subs_gross'          => round($subsGross, 2),
        ];
    }

    /**
     * Last $limit payments involving the given user in either role
     * (payer or receiver). Most recent first.
     */
    public function recentPaymentsForUser(User $user, int $limit = 10): Collection
    {
        return SubPromoterPayment::with(['payer', 'receiver', 'recorder'])
            ->where(function ($q) use ($user) {
                $q->where('payer_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Last $limit payments that the manager received from his sub-promoters.
     */
    public function recentPaymentsReceivedByManager(User $manager, int $limit = 10): Collection
    {
        $manager = $this->resolveManager($manager);

        return SubPromoterPayment::with(['payer', 'receiver', 'recorder'])
            ->where('payment_type', SubPromoterPayment::TYPE_SUB_TO_MANAGER)
            ->where('receiver_id', $manager->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Last $limit payments the manager made to organizers.
     *
     * Per the new business rules the manager does NOT self-record these
     * rows — they are recorded by an admin. This helper therefore looks
     * up the same rows by payer_id (the manager is still the economic
     * payer) regardless of who recorded them.
     */
    public function recentPaymentsToOrganizersByManager(User $manager, int $limit = 10): Collection
    {
        $manager = $this->resolveManager($manager);

        return SubPromoterPayment::with(['payer', 'receiver', 'recorder'])
            ->where('payment_type', SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS)
            ->where('payer_id', $manager->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Last $limit payments involving the given user as payer OR receiver,
     * filtered to a specific payment type. Used by views that need to
     * show only one direction (e.g. only payments FROM a manager to the
     * organizers) without scanning both columns of unrelated rows.
     */
    public function recentPaymentsForUserOfType(User $user, string $type, int $limit = 10): Collection
    {
        return SubPromoterPayment::with(['payer', 'receiver', 'recorder'])
            ->where('payment_type', $type)
            ->where(function ($q) use ($user) {
                $q->where('payer_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Per-ticket-type sales breakdown for one or more ticket-order authors.
     *
     * Aggregates TicketOrderItem rows joined with their ticket types and
     * the parent ticket_orders so we can render "Personal sales" and
     * "Sub-promoter sales" cards on the manager dashboard without
     * pulling every order into memory.
     *
     * @param  \Illuminate\Support\Collection<int, int>|array<int, int>  $userIds
     *         Authors to include (the manager himself for "personal
     *         sales", or the manager's sub-promoter ids for "team sales").
     * @return array{
     *     rows: list<array{
     *         ticket_type_id: int,
     *         name: string,
     *         quantity: int,
     *         gross: float,
     *         orders: int,
     *     }>,
     *     total_quantity: int,
     *     total_gross: float,
     *     total_orders: int,
     * }
     */
    public function ticketTypeBreakdown($userIds): array
    {
        $userIds = collect($userIds)->map(fn ($id) => (int) $id)->unique()->values();
        if ($userIds->isEmpty()) {
            return [
                'rows'          => [],
                'total_quantity'=> 0,
                'total_gross'   => 0.0,
                'total_orders'  => 0,
            ];
        }

        $byType = TicketOrderItem::query()
            ->selectRaw('ticket_order_items.ticket_type_id,
                         COALESCE(SUM(ticket_order_items.quantity), 0) AS quantity,
                         COALESCE(SUM(ticket_order_items.quantity * ticket_types.price), 0) AS gross')
            ->join('ticket_orders', 'ticket_orders.id', '=', 'ticket_order_items.ticket_order_id')
            ->join('ticket_types', 'ticket_types.id', '=', 'ticket_order_items.ticket_type_id')
            ->whereIn('ticket_orders.requested_by', $userIds)
            ->whereIn('ticket_orders.job_status', self::SUCCESS_STATUSES)
            ->where('ticket_orders.is_private', false)
            ->groupBy('ticket_order_items.ticket_type_id')
            ->get();

        $orderCountByType = TicketOrder::query()
            ->selectRaw('ticket_order_items.ticket_type_id,
                         COUNT(DISTINCT ticket_orders.id) AS orders')
            ->join('ticket_order_items', 'ticket_order_items.ticket_order_id', '=', 'ticket_orders.id')
            ->whereIn('ticket_orders.requested_by', $userIds)
            ->whereIn('ticket_orders.job_status', self::SUCCESS_STATUSES)
            ->where('ticket_orders.is_private', false)
            ->groupBy('ticket_order_items.ticket_type_id')
            ->pluck('orders', 'ticket_type_id');

        // Look up the ticket-type names in one go so we render without
        // an N+1 query.
        $typeIds = $byType->pluck('ticket_type_id')->all();
        $namesByType = \App\Models\TicketType::whereIn('id', $typeIds)
            ->pluck('name', 'id');

        // Re-compute the per-type gross using the actual order totals,
        // split among line items by quantity share. ticket_orders.total
        // is the authoritative revenue figure (it includes any taxes,
        // fees, or rounding) — falling back to quantity * ticket_type
        // .price only when the order total is missing.
        $grossByOrderType = DB::table('ticket_order_items')
            ->selectRaw('ticket_order_items.ticket_type_id,
                         ticket_order_items.ticket_order_id,
                         ticket_order_items.quantity,
                         COALESCE(ticket_orders.total, 0) AS order_total')
            ->join('ticket_orders', 'ticket_orders.id', '=', 'ticket_order_items.ticket_order_id')
            ->whereIn('ticket_orders.requested_by', $userIds)
            ->whereIn('ticket_orders.job_status', self::SUCCESS_STATUSES)
            ->where('ticket_orders.is_private', false)
            ->get();

        $orderTotalById = [];
        $orderItemCountById = [];
        foreach ($grossByOrderType as $r) {
            $orderId = (int) $r->ticket_order_id;
            $orderTotalById[$orderId] = (float) $r->order_total;
            $orderItemCountById[$orderId] = ($orderItemCountById[$orderId] ?? 0) + (int) $r->quantity;
        }

        $grossByType = [];
        foreach ($grossByOrderType as $r) {
            $orderId = (int) $r->ticket_order_id;
            $orderTotal = $orderTotalById[$orderId] ?? 0.0;
            $orderItemCount = $orderItemCountById[$orderId] ?: 1;
            $share = ((int) $r->quantity) / $orderItemCount;
            $grossByType[(int) $r->ticket_type_id] = ($grossByType[(int) $r->ticket_type_id] ?? 0.0) + ($orderTotal * $share);
        }

        $rows = $byType->map(function ($r) use ($namesByType, $orderCountByType, $grossByType) {
            $id = (int) $r->ticket_type_id;
            return [
                'ticket_type_id' => $id,
                'name'           => $namesByType[$id] ?? '—',
                'quantity'       => (int) $r->quantity,
                'gross'          => round((float) ($grossByType[$id] ?? 0), 2),
                'orders'         => (int) ($orderCountByType[$id] ?? 0),
            ];
        })
        ->sortByDesc('gross')
        ->values()
        ->all();

        return [
            'rows'           => $rows,
            'total_quantity' => array_sum(array_column($rows, 'quantity')),
            'total_gross'    => round(array_sum(array_column($rows, 'gross')), 2),
            'total_orders'   => array_sum(array_column($rows, 'orders')),
        ];
    }

    /**
     * Per-sub-promoter ticket-type breakdown. One entry per sub, each
     * entry containing the same shape as ticketTypeBreakdown() but
     * scoped to that sub's own successful orders. Used by the
     * "Lista promotera" section of the dashboard.
     *
     * @return array<int, array{
     *     user: User,
     *     rows: list<array{name: string, quantity: int, gross: float}>,
     *     total_quantity: int,
     *     total_gross: float,
     *     total_orders: int,
     *     tickets_sold: int,
     * }>
     */
    public function ticketTypeBreakdownPerSub(User $manager): array
    {
        $manager = $this->resolveManager($manager);
        $subs = $manager->subPromoters()->orderBy('name')->get();
        if ($subs->isEmpty()) {
            return [];
        }

        // Pre-compute tickets_sold + orders_count for every sub in one
        // pass so the table can sort and render the "Tickets sold"
        // column correctly even when the manager has more subs than
        // the leaderboard's top-N window.
        $subIds = $subs->pluck('id');
        $ticketsBySub = DB::table('ticket_order_items')
            ->selectRaw('ticket_orders.requested_by AS sub_id, COALESCE(SUM(ticket_order_items.quantity), 0) AS tickets')
            ->join('ticket_orders', 'ticket_orders.id', '=', 'ticket_order_items.ticket_order_id')
            ->whereIn('ticket_orders.requested_by', $subIds)
            ->whereIn('ticket_orders.job_status', self::SUCCESS_STATUSES)
            ->where('ticket_orders.is_private', false)
            ->groupBy('ticket_orders.requested_by')
            ->pluck('tickets', 'sub_id');
        $ordersBySub = DB::table('ticket_orders')
            ->selectRaw('requested_by, COUNT(*) AS orders_count')
            ->whereIn('requested_by', $subIds)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->groupBy('requested_by')
            ->pluck('orders_count', 'requested_by');

        $out = [];
        foreach ($subs as $sub) {
            $breakdown = $this->ticketTypeBreakdown([$sub->id]);
            $out[] = [
                'user'           => $sub,
                'rows'           => $breakdown['rows'],
                'total_quantity' => $breakdown['total_quantity'],
                'total_gross'    => $breakdown['total_gross'],
                'total_orders'   => $breakdown['total_orders'],
                'tickets_sold'   => (int) ($ticketsBySub[$sub->id] ?? 0),
                'orders_count'   => (int) ($ordersBySub[$sub->id]   ?? 0),
            ];
        }
        return $out;
    }

    /**
     * Combined chronological ledger of every payment that flowed
     * THROUGH this promoter-manager (in either direction):
     *
     *   + sub_to_manager         → cash the manager received from subs
     *   − manager_to_organizers  → cash the manager forwarded to orgs
     *
     * Used by the "Istorija transakcija" section of the dashboard so
     * the manager can see at a glance how today's cashInHand was
     * arrived at. Each row exposes a signed amount (negative for
     * outflows) plus a "direction" flag the view can switch on.
     *
     * @return Collection<int, object{
     *     direction: 'in'|'out',
     *     amount: float,
     *     amount_signed: float,
     *     paid_at: \Illuminate\Support\Carbon,
     *     payer: User|null,
     *     receiver: User|null,
     *     recorder: User|null,
     *     note: string|null,
     *     type: string,
     * }>
     */
    public function recentLedgerForManager(User $manager, int $limit = 25): Collection
    {
        $manager = $this->resolveManager($manager);

        return SubPromoterPayment::with(['payer', 'receiver', 'recorder'])
            ->where(function ($q) use ($manager) {
                $q->where(function ($q2) use ($manager) {
                    $q2->where('payment_type', SubPromoterPayment::TYPE_SUB_TO_MANAGER)
                       ->where('receiver_id', $manager->id);
                })->orWhere(function ($q2) use ($manager) {
                    $q2->where('payment_type', SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS)
                       ->where('payer_id', $manager->id);
                });
            })
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get()
            ->map(function (SubPromoterPayment $p) {
                $direction = $p->payment_type === SubPromoterPayment::TYPE_SUB_TO_MANAGER ? 'in' : 'out';
                return (object) [
                    'direction'     => $direction,
                    'amount'        => (float) $p->amount,
                    'amount_signed' => $direction === 'in' ? (float) $p->amount : -((float) $p->amount),
                    'paid_at'       => $p->paid_at,
                    'payer'         => $p->payer,
                    'receiver'      => $p->receiver,
                    'recorder'      => $p->recorder,
                    'note'          => $p->note,
                    'type'          => $p->payment_type,
                ];
            });
    }

    /**
     * Last $limit successful ticket orders placed by the manager OR any
     * of his sub-promoters. Eager-loads the line items with their ticket
     * type names AND the per-beneficiary commission rows so the
     * "Istorija prodatih ulaznica" section can render one order per row
     * with: seller, when, ticket types + quantities, recipient email
     * and the manager's commission share for that specific order.
     *
     * @return Collection<int, TicketOrder>
     */
    public function recentOrdersForManager(User $manager, int $limit = 25): Collection
    {
        $manager = $this->resolveManager($manager);

        $subIds = $manager->subPromoters()->pluck('id')->all();
        $authorIds = collect([$manager->id])->merge($subIds)->unique()->values();

        return TicketOrder::with([
                'requestedBy',
                'items.ticketType',
                'commissionBeneficiaries',
            ])
            ->whereIn('requested_by', $authorIds)
            ->whereIn('job_status', self::SUCCESS_STATUSES)
            ->where('is_private', false)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    /* ---------- helpers ---------- */

    private function resolveSub(User $sub): User
    {
        abort_unless($sub->isSubPromoter(), 422, 'User is not a sub-promoter.');
        return $sub;
    }

    private function resolveManager(User $manager): User
    {
        abort_unless($manager->isPromoterManager(), 422, 'User is not a promoter-manager.');
        return $manager;
    }

    /**
     * Validate the payment type AND the recorder authorization.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    private function validatePaymentType(string $type, User $payer, User $receiver, User $recorder): void
    {
        if (!in_array($type, [
            SubPromoterPayment::TYPE_SUB_TO_MANAGER,
            SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS,
        ], true)) {
            abort(422, "Unsupported payment type: {$type}");
        }

        if ($type === SubPromoterPayment::TYPE_SUB_TO_MANAGER) {
            abort_unless($payer->isSubPromoter(), 422, 'Payer must be a sub-promoter.');
            abort_unless($receiver->isPromoterManager(), 422, 'Receiver must be a promoter-manager.');
            abort_unless(
                $receiver->subPromoters()->where('id', $payer->id)->exists(),
                422,
                'The receiver is not the payer\'s promoter-manager.'
            );

            // Recorder: must be the sub's manager OR an admin-tier user.
            $isAuthorizedRecorder = $recorder->isAdmin()
                || ($recorder->isPromoterManager()
                    && $recorder->id === $receiver->id);
            abort_unless(
                $isAuthorizedRecorder,
                403,
                'A sub-to-manager payment can only be recorded by the sub\'s promoter-manager or by an admin.'
            );
        }

        if ($type === SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS) {
            abort_unless($payer->isPromoterManager(), 422, 'Payer must be a promoter-manager.');
            abort_unless($payer->id === $receiver->id, 422, 'Receiver must be the same promoter-manager (organizer is implicit).');

            // Recorder: only admin-tier users. The manager himself is
            // NOT allowed to record his own payment.
            abort_unless(
                $recorder->isAdmin(),
                403,
                'A manager-to-organizers payment can only be recorded by an admin.'
            );

            // Defense-in-depth: the manager himself can never be the
            // recorder even if his role was extended in the future.
            abort_unless(
                $recorder->id !== $payer->id,
                403,
                'A promoter-manager cannot record their own payment to the organizers.'
            );
        }
    }
}
