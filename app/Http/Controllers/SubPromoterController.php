<?php

namespace App\Http\Controllers;

use App\Models\PromoterCommissionOverride;
use App\Models\TicketOrder;
use App\Models\TicketOrderCommission;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubPromoterController extends Controller
{
    /**
     * Sub-promoter dashboard: shows orders they placed, commission earned,
     * and how much of each ticket type's commission goes to the promoter-manager
     * that supervises them.
     */
    public function dashboard()
    {
        $sub = Auth::user();
        abort_unless($sub && $sub->role === 'sub_promoter', 403);

        $successfulSaleStatuses = ['completed', 'sent'];

        $orders = TicketOrder::where('requested_by', $sub->id)
            ->with(['items.ticketType', 'orderedBy'])
            ->latest()
            ->take(10)
            ->get();

        $subOrdersAllTime = TicketOrder::where('requested_by', $sub->id)
            ->whereIn('job_status', $successfulSaleStatuses)
            ->count();

        $subTicketsSoldAllTime = (int) TicketOrderItem::whereHas('ticketOrder', function ($q) use ($sub, $successfulSaleStatuses) {
            $q->where('requested_by', $sub->id)->whereIn('job_status', $successfulSaleStatuses);
        })->sum('quantity');

        $subCommissionAllTime = (float) TicketOrderCommission::where('beneficiary_user_id', $sub->id)
            ->sum('commission_amount');

        $manager = $sub->promoterManager();

        // Resolve the override rows the manager has set, per ticket type.
        // Shape: ticket_type_id => ['type' => 'percentage'|'fixed',
        //                           'percentage' => float|null,
        //                           'fixed_amount' => float|null]
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

        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
        ];

        return view('pages.subpromoters.dashboard', compact(
            'sub',
            'orders',
            'subOrdersAllTime',
            'subTicketsSoldAllTime',
            'subCommissionAllTime',
            'manager',
            'overrides',
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
}
