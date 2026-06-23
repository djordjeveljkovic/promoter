<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateTicketImagesJob;
use App\Jobs\SendCustomerTicketsEmailJob;
use App\Jobs\OrderCompleted;
use App\Events\TicketOrderStatusUpdated;
use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Generates a cryptic, unique order number.
     */
    private function generateUniqueCrypticOrderNumber(int $length = 6): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $uniqueOrderNumber = '';

        do {
            $currentRandomString = '';
            for ($i = 0; $i < $length; $i++) {
                try {
                    // Use random_int for cryptographically secure random numbers
                    $currentRandomString .= $characters[random_int(0, $charactersLength - 1)];
                } catch (\Exception $e) {
                    // Fallback for environments where random_int might fail (highly unlikely)
                    // or handle error appropriately
                    // Log::error('random_int failed, falling back to mt_rand: ' . $e->getMessage());
                    $currentRandomString .= $characters[mt_rand(0, $charactersLength - 1)];
                }
            }
            $uniqueOrderNumber = $currentRandomString;
            // Check for uniqueness in the 'ticket_orders' table
        } while (DB::table('ticket_orders')->where('order_number', $uniqueOrderNumber)->exists());

        return $uniqueOrderNumber;
    }

    /**
     * Display a listing of the orders placed by the promoter.
     */
    public function index()
    {
        $promoterId = Auth::id();
        $user = Auth::user();
        // A promoter-manager sees both his own orders AND the orders placed by
        // his sub-promoters, so he has a full picture of the activity he
        // manages.
        $subIds = $user?->isPromoterManager()
            ? $user->subPromoters()->pluck('id')->all()
            : [];

        $query = TicketOrder::with(['items.ticketType', 'orderedBy', 'requestedBy'])
            ->latest();

        if (!empty($subIds)) {
            $query->where(function ($q) use ($promoterId, $subIds) {
                $q->where('requested_by', $promoterId)
                  ->orWhereIn('requested_by', $subIds);
            });
        } else {
            $query->where('requested_by', $promoterId);
        }

        $orders = $query->paginate(15);

        // Pre-compute the "Seller" label for each order so the view can
        // distinguish own orders from sub-promoter orders without N+1
        // lookups.
        $sellerLabelsByOrder = [];
        foreach ($orders as $order) {
            if ((int) $order->requested_by === (int) $promoterId) {
                $sellerLabelsByOrder[$order->id] = [
                    'name'      => $user->name,
                    'is_self'   => true,
                ];
            } else {
                $seller = $order->requestedBy;
                $sellerLabelsByOrder[$order->id] = [
                    'name'      => $seller?->name ?? __('orders.seller_unknown'),
                    'is_self'   => false,
                ];
            }
        }

        // Pass status colors for job_status to the view
        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
        ];

        return view('pages.promoters.orders.index', compact('orders', 'jobStatusColors', 'sellerLabelsByOrder', 'subIds'));
    }

    /**
     * Show the form for creating a new order.
     */
    public function create()
    {
        $ticketTypes = TicketType::orderBy('name')->get();
        return view('pages.promoters.orders.create', compact('ticketTypes'));
    }

    /**
     * Store a newly created order in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|max:255',
            'items' => 'required|array|min:1',
            'items.*.ticket_type_id' => 'required|exists:ticket_types,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'items.required' => 'Please add at least one ticket type to the order.',
            'items.min' => 'Please add at least one ticket type to the order.',
        ]);

        DB::beginTransaction();

        try {
            // --- Find or create the customer user ---
            $customerUser = User::firstOrCreate(
                ['email' => $validatedData['email']],
                [
                    'name' => Str::before($validatedData['email'], '@'),
                    'password' => Hash::make(Str::random(16)), // Use a random password
                    'role' => 'buyer' // Ensure you have roles set up if using this
                ]
            );

            // --- Get the Promoter User instance ---
            $promoterUser = Auth::user();
            if (!$promoterUser) {
                // This should ideally be caught by auth middleware, but as a safeguard
                throw new \Exception("Promoter not authenticated.");
            }

            $orderNumber = $this->generateUniqueCrypticOrderNumber(); // e.g., "aK9ZxP4qR7Vc"
            // --- Create the main order record ---
            $ticketOrder = TicketOrder::create([
                'order_number' => $orderNumber,
                'ordered_by' => $customerUser->id,
                'requested_by' => $promoterUser->id, // Use promoter's ID
                'email' => $validatedData['email'],
                'job_status' => 'processing', // Or 'pending' if jobs handle setting to 'processing'
                'paid' => 0.00, // Assuming payment handling is separate or comes later
                'total' => 0.00 // Will be updated after calculating
            ]);

            $ticketTypeIds = collect($validatedData['items'])->pluck('ticket_type_id')->unique();
            $ticketTypes = TicketType::findMany($ticketTypeIds)->keyBy('id');

            $orderTotal = 0.00;
            // $orderTotalCommission = 0.00; // If you intend to sum and store commission here

            foreach ($validatedData['items'] as $itemData) {
                $ticketType = $ticketTypes->get($itemData['ticket_type_id']);

                if (!$ticketType) {
                    throw new \Exception("Invalid ticket type ID: " . $itemData['ticket_type_id']);
                }

                $orderItem = TicketOrderItem::create([
                    'ticket_order_id' => $ticketOrder->id,
                    'ticket_type_id' => $itemData['ticket_type_id'],
                    'quantity' => $itemData['quantity'],
                    'price_at_order' => $ticketType->price,
                    // 'commission_earned' will be calculated and stored by the OrderCompleted job
                ]);

                for ($i = 0; $i < $itemData['quantity']; $i++) {
                    Ticket::create([
                        'code' => Str::uuid()->toString(),
                        'ticket_type_id' => $itemData['ticket_type_id'],
                        'ticket_order_id' => $ticketOrder->id,
                        'is_active' => true, // Or based on your business logic
                    ]);
                }

                $orderTotal += $itemData['quantity'] * $ticketType->price;

                $itemEstimatedCommission = User::calculateCommission(
                    $itemData['ticket_type_id'],
                    $ticketOrder->id,
                    $itemData['quantity'],
                    $promoterUser,          // Pass the promoter User model instance
                    $ticketOrder->created_at // Pass the order's creation timestamp
                );
                Log::info("OrderController@store: Estimated commission for order {$ticketOrder->id}, item type {$itemData['ticket_type_id']}: {$itemEstimatedCommission}");
            }

            $ticketOrder->total = $orderTotal;
            // If you summed $orderTotalCommission:
            // $ticketOrder->total_commission_estimate = $orderTotalCommission; // Example custom field
            $ticketOrder->save();

            DB::commit();

            Log::info("Order {$ticketOrder->id} created by promoter {$promoterUser->id}. Dispatching job chain.");
            Bus::chain([
                new GenerateTicketImagesJob($ticketOrder->id),
                new SendCustomerTicketsEmailJob($ticketOrder->id, $validatedData['email']),
                new OrderCompleted($ticketOrder)
            ])->dispatch();

            return redirect()->route('promoter.orders.index') // Adjust route as necessary
                ->with('success', __('alert.order_created_success', ['orderId' => $ticketOrder->id]));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('OrderController@store - Order creation failed: ' . $e->getMessage(), [
                'request' => $request->except(['password', '_token']),
                'trace_snippet' => substr($e->getTraceAsString(), 0, 1000)
            ]);
            return back()->withInput()
                ->with('error', __('alert.order_created_failure', ['message' => $e->getMessage()]));
        }
    }


    /**
     * Display the specified order.
     */
    public function show(TicketOrder $order) // Route model binding
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'Unauthorized action.');
        }

        // The seller can always view his own order. A promoter-manager can
        // additionally view orders placed by his sub-promoters.
        $isSeller = $order->requested_by === $user->id;
        $isManagerOfSeller = $user->isPromoterManager()
            && $user->subPromoters()->where('id', $order->requested_by)->exists();

        if (!$isSeller && !$isManagerOfSeller) {
            abort(403, 'Unauthorized action.');
        }

        $order->load(['items.ticketType', 'tickets.ticketType', 'orderedBy', 'requestedBy']);

        // Calculate total price for display (if not stored directly on order)
        $totalPrice = 0;
        foreach ($order->items as $item) {
            $totalPrice += $item->quantity * $item->ticketType->price;
        }

        return view('pages.promoters.orders.show', compact('order', 'totalPrice'));
    }

    public function rerunImageGeneration(TicketOrder $order)
    {
        if (in_array($order->job_status, ['failed', 'pending', 'processing'])) {
            $previousStatus = (string) $order->job_status;
            $order->job_status = 'pending'; // Reset status to re-trigger processing pipeline
            $order->job_failure_reason = null;
            $order->save();
            $this->broadcastOrderStatus($order, $previousStatus);

            GenerateTicketImagesJob::dispatch($order->id);

            return back()->with('success', __('alert.image_generation_requeued', ['orderId' => $order->id]));
        }

        return back()->with('info', __('alert.image_generation_cannot_rerun', [
            'orderId' => $order->id,
            'status' => $order->job_status
        ]));
    }

    public function rerunEmailSending(TicketOrder $order)
    {
        if (in_array($order->job_status, ['failed', 'completed', 'sent', 'processing'])) {
            $originalStatusBeforeRetry = $order->job_status;
            $order->job_status = 'pending';
            if ($originalStatusBeforeRetry === 'failed') {
                $order->job_failure_reason = null;
            }
            $order->save();
            $this->broadcastOrderStatus($order, $originalStatusBeforeRetry);

            SendCustomerTicketsEmailJob::dispatch($order->id, $order->email);
            return back()->with('success', __('alert.email_requeued_success', ['orderId' => $order->id]));
        }
        return back()->with('info', __('alert.email_cannot_resent', [
            'orderId' => $order->id,
            'status' => $order->job_status
        ]));
    }

    /**
     * Broadcast a status change so admin/supreme dashboards refresh in
     * real time without polling. Wrapped in try/catch — a failed broadcast
     * must never cause a user-facing rerun action to 500.
     */
    private function broadcastOrderStatus(TicketOrder $order, ?string $previousStatus): void
    {
        try {
            TicketOrderStatusUpdated::dispatch(
                (int) $order->id,
                (string) ($order->job_status ?? 'unknown'),
                $order->job_failure_reason,
                $previousStatus,
            );
        } catch (\Throwable $e) {
            \Log::warning('[OrderController] Failed to broadcast TicketOrderStatusUpdated for order ' . $order->id . ': ' . $e->getMessage());
        }
    }
}
