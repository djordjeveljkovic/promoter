<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard()
    {
	$user = auth()->user();
	$role = $user->role;

	// Admin-level roles (admin / supreme / superadmin) all see every
	// dashboard metric. Previously the 'admin' role was scoped to only
	// orders requested by 'admin' or 'promoter' users, which hid orders
	// (and ticket sales / revenue) placed by promoter_managers and
	// sub_promoters from the SUPREME admin account (seeded as 'admin').
	$allowedRequestedByUserIds = null;

	$filterByRequestedByUserIds = function ($query) use ($allowedRequestedByUserIds) {
	    // Hide private (supreme-admin) sales from every dashboard metric.
	    $query->publicOnly();
	    if ($allowedRequestedByUserIds !== null) {
		$query->whereIn('requested_by', $allowedRequestedByUserIds);
	    }
	};

        // --- Timeframe (Example: Last 30 days and All Time) ---
        // You can extend this with a date picker on the frontend
        $endDate = now();
        $startDate30Days = now()->subDays(30);

        // --- Overall Stats (All Time) ---
        $totalRevenueAllTime = TicketOrder::where('job_status', 'completed')->tap($filterByRequestedByUserIds)->sum('total');
        $totalPaidAllTime = TicketOrder::where('job_status', 'completed')->tap($filterByRequestedByUserIds)->sum('paid');
        $totalOrdersAllTime = TicketOrder::tap($filterByRequestedByUserIds)->count();
        $totalTicketsSoldAllTime = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($filterByRequestedByUserIds) {
	    $filterByRequestedByUserIds($query);
        })->sum('quantity');
        // Consider only tickets from completed orders for "sold"
        $totalTicketsEffectivelySoldAllTime = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($filterByRequestedByUserIds) {
            $query->where('job_status', 'completed');
	    $filterByRequestedByUserIds($query);
        })->sum('quantity');


        // --- Overall Stats (Last 30 Days) ---
        $totalRevenueLast30Days = TicketOrder::where('job_status', 'completed')->tap($filterByRequestedByUserIds)->whereBetween('created_at', [$startDate30Days, $endDate])->sum('total');
        $totalOrdersLast30Days = TicketOrder::whereBetween('created_at', [$startDate30Days, $endDate])->tap($filterByRequestedByUserIds)->count();
        $totalTicketsSoldLast30Days = TicketOrderItem::whereHas('ticketOrder', function ($query) use ($startDate30Days, $endDate, $filterByRequestedByUserIds) {
            $query->whereBetween('created_at', [$startDate30Days, $endDate])->where('job_status', 'completed');
	    $filterByRequestedByUserIds($query);
        })->sum('quantity');


        // --- Ticket Type Performance (All Time, based on effectively sold) ---
	$ticketTypePerformanceQuery = TicketType::select(
		'ticket_types.name',
		DB::raw('SUM(ticket_order_items.quantity) as total_quantity_sold'),
		DB::raw('SUM(ticket_order_items.quantity * ticket_types.price) as total_revenue')
	    )
	    ->join('ticket_order_items', 'ticket_types.id', '=', 'ticket_order_items.ticket_type_id')
	    ->join('ticket_orders', 'ticket_order_items.ticket_order_id', '=', 'ticket_orders.id')
	    ->where('ticket_orders.job_status', 'completed')
	    ->where('ticket_orders.is_private', false);

	if ($allowedRequestedByUserIds !== null) {
	    $ticketTypePerformanceQuery->whereIn('ticket_orders.requested_by', $allowedRequestedByUserIds);
	}

	$ticketTypePerformance = $ticketTypePerformanceQuery
	    ->groupBy('ticket_types.id', 'ticket_types.name')
	    ->orderBy('total_quantity_sold', 'desc')
	    ->take(5)
	    ->get();

        // --- Promoter Performance (All Time, based on revenue from completed orders) ---
        // Excludes private (supreme-admin) sales.
        $promoterPerformance = User::where('role', 'promoter')
            ->select(
                'users.id',
                'users.name',
                'users.email',
                DB::raw('COUNT(DISTINCT ticket_orders.id) as total_orders_generated'),
                DB::raw('SUM(ticket_orders.total) as total_revenue_generated')
            )
            ->leftJoin('ticket_orders', function ($join) {
                $join->on('users.id', '=', 'ticket_orders.requested_by')
                    ->where('ticket_orders.job_status', '=', 'completed')
                    ->where('ticket_orders.is_private', '=', false);
            })
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderBy('total_revenue_generated', 'desc')
            ->take(5) // Top 5 promoters
            ->get();

        // --- User Statistics ---
        $userCountsByRole = User::select('role', DB::raw('count(*) as count'))->where('role','<>','superadmin')
            ->groupBy('role')
            ->pluck('count', 'role');

        // --- Order Statuses ---
        $orderStatusCounts = TicketOrder::select('job_status', DB::raw('count(*) as count'))
	    ->tap($filterByRequestedByUserIds)
            ->groupBy('job_status')
            ->pluck('count', 'job_status');

        // --- Ticket Activation ---
	$activeTicketsQuery = Ticket::where('is_active', true)
	    ->whereHas('ticketOrder', function ($query) use ($allowedRequestedByUserIds) {
		if ($allowedRequestedByUserIds !== null) {
		    $query->whereIn('requested_by', $allowedRequestedByUserIds);
		}
	    });

	$inactiveTicketsQuery = Ticket::where('is_active', false)
	    ->whereHas('ticketOrder', function ($query) use ($allowedRequestedByUserIds) {
		if ($allowedRequestedByUserIds !== null) {
		    $query->whereIn('requested_by', $allowedRequestedByUserIds);
		}
	    });

	$activeTicketsCount = $activeTicketsQuery->count();
	$inactiveTicketsCount = $inactiveTicketsQuery->count();

        // --- Recent Orders ---
        $recentOrders = TicketOrder::with(['orderedBy', 'requestedBy', 'items.ticketType'])
	    ->tap($filterByRequestedByUserIds)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // --- Define Order Status Colors for the view ---
        $statusColors = [
            'processing' => 'bg-blue-100 text-blue-800',
            'failed' => 'bg-red-100 text-red-800',
            'blocked' => 'bg-gray-100 text-gray-800',
            'completed' => 'bg-green-100 text-green-800',
            'sent' => 'bg-teal-100 text-teal-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
        ];


        return view('pages.admin.dashboard', compact(
            'totalRevenueAllTime',
            'totalPaidAllTime',
            'totalOrdersAllTime',
            'totalTicketsEffectivelySoldAllTime',
            'totalRevenueLast30Days',
            'totalOrdersLast30Days',
            'totalTicketsSoldLast30Days',
            'ticketTypePerformance',
            'promoterPerformance',
            'userCountsByRole',
            'orderStatusCounts',
            'statusColors',
            'activeTicketsCount',
            'inactiveTicketsCount',
            'recentOrders'
        ));
    }

    public function promoters()
    {
        // 1. Fetch all users with the 'promoter' role
        $promoters = User::where('role', 'promoter')->get();

        // Define successful sale statuses once
        $successfulSaleStatuses = ['completed', 'confirmed']; // Or your actual statuses

        // 2. Iterate through each promoter to calculate and attach their financial data
        foreach ($promoters as $promoter) {
            $promoterId = $promoter->id; // Get the ID of the current promoter in the loop

            // a. Total Commission Earned by Promoter (All Time)
            $promoter->totalCommissionEarned = TicketOrder::where('requested_by', $promoterId)
                ->whereIn('job_status', $successfulSaleStatuses)
                ->sum('total_commission_earned');

            // b. Gross Value of Tickets Sold by Promoter (All Time)
            $promoter->grossSalesAllTime = TicketOrder::where('requested_by', $promoterId)
                ->whereIn('job_status', $successfulSaleStatuses)
                ->sum('total');

            // c. Amount Already Paid by Promoter to Organizers
            // Assumes 'paid' is a field on your User (promoter) model or you fetch it similarly
            $promoter->amountPaidToOrganizers = $promoter->paid ?? 0.00;

            // d. Amount Owed by Promoter to Organizers
            $promoter->amountOwedToOrganizers = $promoter->grossSalesAllTime - $promoter->amountPaidToOrganizers - $promoter->totalCommissionEarned;

            // e. How much promoter made for organizers
            $promoter->madeForOrganizers = $promoter->grossSalesAllTime - $promoter->totalCommissionEarned;

            // f. How many tickets sold (count of orders)
            $promoter->ticketsSoldCount = TicketOrder::where('requested_by', $promoterId)
                ->whereIn('job_status', $successfulSaleStatuses)
                ->count(); // Count of distinct orders. If you need to sum a quantity from each order, use ->sum('ticket_quantity_column_name')
        }

        // 3. Pass the collection of promoters (now with added financial data) to the view
        return view('pages.admin.promoters.index', compact('promoters'));
    }

    public function createPromoter()
    {
        return view('pages.admin.promoters.create');
    }

    public function editPromoter($id)
    {
        $promoter = User::findOrFail($id);
        return view('pages.admin.promoters.edit', compact('promoter'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
            ],
            'password' => 'nullable|string|min:8',
        ]);

        $promoter = new User();
        $promoter->name = $validatedData['name'];
        $promoter->email = $validatedData['email'];
        $promoter->paid = $request->paid;

        if (!empty($validatedData['password'])) {
            $promoter->password = Hash::make($validatedData['password']);
        }
        $promoter->role = 'promoter';

        $promoter->save();

        return redirect()->route('admin.promoters.index')->with('success', __('alert.promoter_updated_success'));
    }

    public function updatePromoter(Request $request, $id)
    {
        $promoter = User::findOrFail($id);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($promoter->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $promoter->name = $validatedData['name'];
        $promoter->email = $validatedData['email'];
        $promoter->paid = $request->paid;

        if (!empty($validatedData['password'])) {
            $promoter->password = Hash::make($validatedData['password']);
        }

        $promoter->save();

        return redirect()->route('admin.promoters.edit', $id)->with('success', __('alert.promoter_updated_success'));
    }
}
