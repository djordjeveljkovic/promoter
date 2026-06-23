<?php

namespace App\Http\Controllers;

use App\Models\PromoterCommissionOverride;
use App\Models\SubPromoterPayment;
use App\Models\TicketOrder;
use App\Models\TicketOrderCommission;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SubPromoterController extends Controller
{
    /**
     * Sub-promoter dashboard: shows orders they placed, commission earned,
     * top-performing ticket types, the commission-split their manager set
     * per ticket type, order status breakdown, a recent-orders list AND
     * the running debt to the manager plus the payment history.
     */
    public function dashboard()
    {
        $sub = Auth::user();
        abort_unless($sub && $sub->role === 'sub_promoter', 403);

        $successfulSaleStatuses = ['completed', 'sent'];
        $endDate = now();
        $startDate30Days = now()->subDays(30);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);
        $debtSummary = $debt->subPromoterDebt($sub);

        // ---- Financials (all time) ----
        // The sub's commission is the per-beneficiary row in
        // ticket_order_commissions, scoped strictly to the sub. The gross
        // sales figure mirrors the promoter dashboard logic: sum the
        // order.total for every successful order the sub placed.
        $subCommissionAllTime = $debtSummary['sub_commission'];
        $subGrossSalesAllTime = $debtSummary['gross_sales'];
        $amountAlreadyPaid = $debtSummary['amount_already_paid'];
        $amountOwedToManager = $debtSummary['amount_owed_to_manager'];

        // ---- Financials (last 30 days) ----
        // For commission earned in the last 30 days we filter the per-
        // beneficiary commission rows by their parent order's created_at
        // (commission rows don't carry their own timestamp).
        $subCommissionLast30Days = (float) TicketOrderCommission::where('beneficiary_user_id', $sub->id)
            ->whereHas('ticketOrder', function ($q) use ($sub, $startDate30Days, $endDate) {
                $q->where('requested_by', $sub->id)
                    ->whereBetween('created_at', [$startDate30Days, $endDate]);
            })
            ->sum('commission_amount');

        $subGrossSalesLast30Days = (float) TicketOrder::where('requested_by', $sub->id)
            ->whereIn('job_status', $successfulSaleStatuses)
            ->whereBetween('created_at', [$startDate30Days, $endDate])
            ->sum('total');

        // ---- Performance counts (all time + last 30 days) ----
        $subOrdersAllTime = TicketOrder::where('requested_by', $sub->id)
            ->whereIn('job_status', $successfulSaleStatuses)
            ->count();

        $subOrdersLast30Days = TicketOrder::where('requested_by', $sub->id)
            ->whereBetween('created_at', [$startDate30Days, $endDate])
            ->count();

        $subTicketsSoldAllTime = (int) TicketOrderItem::whereHas('ticketOrder', function ($q) use ($sub, $successfulSaleStatuses) {
            $q->where('requested_by', $sub->id)->whereIn('job_status', $successfulSaleStatuses);
        })->sum('quantity');

        $subTicketsSoldLast30Days = (int) TicketOrderItem::whereHas('ticketOrder', function ($q) use ($sub, $successfulSaleStatuses, $startDate30Days, $endDate) {
            $q->where('requested_by', $sub->id)
                ->whereIn('job_status', $successfulSaleStatuses)
                ->whereBetween('created_at', [$startDate30Days, $endDate]);
        })->sum('quantity');

        // ---- Top ticket types by quantity sold (top 5) ----
        $subTicketTypePerformance = TicketType::select(
                'ticket_types.id',
                'ticket_types.name',
                DB::raw('SUM(ticket_order_items.quantity) as total_quantity_sold'),
                DB::raw('SUM(ticket_order_items.quantity * ticket_types.price) as total_revenue_generated')
            )
            ->join('ticket_order_items', 'ticket_types.id', '=', 'ticket_order_items.ticket_type_id')
            ->join('ticket_orders', 'ticket_order_items.ticket_order_id', '=', 'ticket_orders.id')
            ->where('ticket_orders.requested_by', $sub->id)
            ->whereIn('ticket_orders.job_status', $successfulSaleStatuses)
            ->groupBy('ticket_types.id', 'ticket_types.name')
            ->orderBy('total_quantity_sold', 'desc')
            ->take(5)
            ->get();

        // ---- Order status breakdown ----
        $subOrderStatusCounts = TicketOrder::where('requested_by', $sub->id)
            ->select('job_status', DB::raw('count(*) as count'))
            ->groupBy('job_status')
            ->pluck('count', 'job_status');

        // ---- Recent orders (latest 5) ----
        $recentOrders = TicketOrder::where('requested_by', $sub->id)
            ->with(['items.ticketType', 'orderedBy'])
            ->latest()
            ->take(5)
            ->get();

        // ---- Manager + commission split overrides ----
        $manager = $sub->promoterManager();

        // Resolve the override rows the manager has set, per ticket type.
        $overrides = [];
        if ($manager) {
            $rows = PromoterCommissionOverride::where('promoter_manager_id', $manager->id)
                ->where('sub_promoter_id', $sub->id)
                ->get();
            foreach ($rows as $ov) {
                $overrides[$ov->ticket_type_id] = [
                    'type'         => $ov->commission_type ?: PromoterCommissionOverride::TYPE_PERCENTAGE,
                    'percentage'   => $ov->commission_percentage !== null ? (float) $ov->commission_percentage : null,
                    'fixed_amount' => $ov->fixed_commission_amount !== null ? (float) $ov->fixed_commission_amount : null,
                ];
            }
        }

        // ---- Recent payment history involving the sub-promoter ----
        $recentPayments = $debt->recentPaymentsForUser($sub, 8);

        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:text-teal-600 dark:text-teal-100',
        ];

        return view('pages.subpromoters.dashboard', compact(
            'sub',
            'debtSummary',
            'subCommissionAllTime',
            'subCommissionLast30Days',
            'subGrossSalesAllTime',
            'subGrossSalesLast30Days',
            'amountAlreadyPaid',
            'amountOwedToManager',
            'subOrdersAllTime',
            'subOrdersLast30Days',
            'subTicketsSoldAllTime',
            'subTicketsSoldLast30Days',
            'subTicketTypePerformance',
            'subOrderStatusCounts',
            'recentOrders',
            'manager',
            'overrides',
            'recentPayments',
            'jobStatusColors'
        ));
    }

    /**
     * Show the "place new order" form for a sub-promoter. Same fields as the
     * regular promoter order form.
     */
    public function create()
    {
        $sub = Auth::user();
        abort_unless($sub && $sub->role === 'sub_promoter', 403);

        $ticketTypes = TicketType::orderBy('name')->get();

        $manager = $sub->promoterManager();
        // ticket_type_id => ['type', 'percentage', 'fixed_amount']
        $overrides = [];
        if ($manager) {
            $rows = PromoterCommissionOverride::where('promoter_manager_id', $manager->id)
                ->where('sub_promoter_id', $sub->id)
                ->get();
            foreach ($rows as $ov) {
                $overrides[$ov->ticket_type_id] = [
                    'type'         => $ov->commission_type ?: PromoterCommissionOverride::TYPE_PERCENTAGE,
                    'percentage'   => $ov->commission_percentage !== null ? (float) $ov->commission_percentage : null,
                    'fixed_amount' => $ov->fixed_commission_amount !== null ? (float) $ov->fixed_commission_amount : null,
                ];
            }
        }

        return view('pages.promoters.orders.create', compact('ticketTypes', 'overrides', 'manager'));
    }

    /**
     * Persist a new order placed by a sub-promoter. The order creation flow
     * is identical to the promoter flow (delegated to OrderController@store),
     * which already calculates tier-based commission and creates the
     * per-beneficiary detail rows for the sub-promoter and his manager.
     */
    public function placeOrder(Request $request)
    {
        // Defer to OrderController@store so the same business rules apply.
        return app(OrderController::class)->store($request);
    }

    /**
     * Sub-promoter "My Orders" page: full paginated list of every order
     * the sub-promoter has placed, with the commission they personally
     * earned on each one. Sub-promoters must NOT see orders placed by
     * other sub-promoters - this query is strictly scoped to the
     * currently authenticated user.
     */
    public function ordersIndex()
    {
        $sub = Auth::user();
        abort_unless($sub && $sub->role === 'sub_promoter', 403);

        $orders = TicketOrder::where('requested_by', $sub->id)
            ->with(['items.ticketType', 'orderedBy'])
            ->latest()
            ->paginate(15);

        // Build a per-order commission lookup so the view can show "your
        // earnings" on each row without doing N+1 queries.
        $orderIds = $orders->pluck('id')->all();
        $commissionsByOrder = TicketOrderCommission::whereIn('ticket_order_id', $orderIds)
            ->where('beneficiary_user_id', $sub->id)
            ->selectRaw('ticket_order_id, SUM(commission_amount) as total')
            ->groupBy('ticket_order_id')
            ->pluck('total', 'ticket_order_id')
            ->map(fn ($v) => (float) $v)
            ->all();

        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
        ];

        return view('pages.subpromoters.orders', compact(
            'sub',
            'orders',
            'commissionsByOrder',
            'jobStatusColors'
        ));
    }
}
