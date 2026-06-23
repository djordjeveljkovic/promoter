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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class PromoterManagerController extends Controller
{
    /* ---------- Admin section: CRUD on promoter-managers ---------- */

    public function index()
    {
        $managers = User::where('role', 'promoter_manager')
            ->withCount('subPromoters')
            ->orderBy('name')
            ->get();

        /** @var DebtService $debt */
        $debt = app(DebtService::class);

        foreach ($managers as $manager) {
            $summary = $debt->promoterManagerDebt($manager);
            $manager->totalGrossSales = $summary['gross_sales'];
            $manager->totalCommissionEarned = $summary['manager_commission'];
            $manager->subCommissionsAllTime = $summary['sub_commissions'];
            $manager->amountPaidToOrganizers = $summary['amount_already_paid_to_organizers'];
            $manager->amountOwedToOrganizers = $summary['amount_owed_to_organizers'];
        }

        return view('pages.admin.promoter_managers.index', compact('managers'));
    }

    public function create()
    {
        return view('pages.admin.promoter_managers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8',
            'paid'     => 'nullable|numeric|min:0',
        ]);

        $manager = new User();
        $manager->name  = $validated['name'];
        $manager->email = $validated['email'];
        $manager->password = Hash::make($validated['password']);
        $manager->role  = 'promoter_manager';
        $manager->paid  = $validated['paid'] ?? 0;
        $manager->save();

        return redirect()->route('admin.promoter_managers.index')
            ->with('success', __('alert.promoter_manager_created_success'));
    }

    public function edit($id)
    {
        $manager = User::where('role', 'promoter_manager')->findOrFail($id);
        return view('pages.admin.promoter_managers.edit', compact('manager'));
    }

    public function update(Request $request, $id)
    {
        $manager = User::where('role', 'promoter_manager')->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($manager->id)],
            'password' => 'nullable|string|min:8',
            'paid'     => 'nullable|numeric|min:0',
        ]);

        $manager->name  = $validated['name'];
        $manager->email = $validated['email'];
        $manager->paid  = $validated['paid'] ?? 0;
        if (!empty($validated['password'])) {
            $manager->password = Hash::make($validated['password']);
        }
        $manager->save();

        return redirect()->route('admin.promoter_managers.edit', $manager->id)
            ->with('success', __('alert.promoter_manager_updated_success'));
    }

    public function destroy($id)
    {
        $manager = User::where('role', 'promoter_manager')->findOrFail($id);
        $manager->delete();
        return redirect()->route('admin.promoter_managers.index')
            ->with('success', __('alert.promoter_manager_deleted_success'));
    }

    /* ---------- Promoter-manager section: manage sub-promoters ---------- */

    /**
     * List the manager's sub-promoters with their per-ticket-type commission
     * percentages, financial summaries AND the live "what does this sub
     * owe me right now" debt figure.
     */
    public function subPromotersIndex()
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);
        $subDebts = $debt->subDebtsForManager($manager);
        $subs = $subDebts->pluck('user');

        $ticketTypes = TicketType::orderBy('name')->get();

        // Build the "by id" lookup once so the view can access debt + override
        // info for each sub in O(1) per render.
        $debtsById = $subDebts->keyBy('user.id');

        foreach ($subs as $sub) {
            $subId = $sub->id;
            $row = $debtsById[$subId];
            $sub->totalOrders = TicketOrder::where('requested_by', $subId)
                ->publicOnly()
                ->whereIn('job_status', ['completed', 'sent'])
                ->count();
            $sub->totalTicketsSold = (int) TicketOrderItem::whereHas('ticketOrder', function ($q) use ($subId) {
                $q->where('requested_by', $subId)->whereIn('job_status', ['completed', 'sent']);
            })->sum('quantity');
            $sub->totalCommissionEarned = $row['sub_commission'];
            $sub->grossSales = $row['gross_sales'];
            $sub->amountOwedToManager = $row['amount_owed_to_manager'];
            $sub->amountAlreadyPaidToManager = $row['amount_already_paid'];

            // Per-ticket-type override summaries for quick lookup in the view.
            $sub->load('commissionOverridesReceived.ticketType');
            $overridesByType = [];
            foreach ($sub->commissionOverridesReceived as $ov) {
                $overridesByType[$ov->ticket_type_id] = [
                    'type'         => $ov->commission_type ?: PromoterCommissionOverride::TYPE_PERCENTAGE,
                    'percentage'   => $ov->commission_percentage !== null ? (float) $ov->commission_percentage : null,
                    'fixed_amount' => $ov->fixed_commission_amount !== null ? (float) $ov->fixed_commission_amount : null,
                ];
            }
            $sub->overridesByType = $overridesByType;
        }

        $totalOwed = (float) $subDebts->sum('amount_owed_to_manager');
        $totalAlreadyPaid = (float) $subDebts->sum('amount_already_paid');

        return view('pages.promoter_managers.sub_promoters.index', compact('subs', 'ticketTypes', 'totalOwed', 'totalAlreadyPaid'));
    }

    public function subPromoterCreate()
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $ticketTypes = TicketType::orderBy('name')->get();
        return view('pages.promoter_managers.sub_promoters.create', compact('ticketTypes'));
    }

    /**
     * Create a new sub-promoter under the currently logged-in promoter-manager.
     * Optionally creates the initial promoter_commission_overrides rows.
     *
     * Each ticket type can be configured either as a "percentage" override
     * (the sub-promoter receives X% of the manager's tier commission) or as
     * a "fixed" override (the sub-promoter receives a flat RSD amount per
     * ticket, independent of the manager's tier).
     */
    public function subPromoterStore(Request $request)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => 'required|string|min:8',
            'overrides'                              => 'array',
            'overrides.*.ticket_type_id'             => 'required|integer|exists:ticket_types,id',
            'overrides.*.commission_type'            => 'required|in:percentage,fixed',
            'overrides.*.commission_percentage'      => 'nullable|numeric|min:0|max:100',
            'overrides.*.fixed_commission_amount'    => 'nullable|numeric|min:0',
        ], [
            'overrides.*.commission_type.in'         => 'Commission type must be either "percentage" or "fixed".',
            'overrides.*.commission_percentage.min'  => 'Percentage cannot be negative.',
            'overrides.*.commission_percentage.max'  => 'Percentage cannot exceed 100.',
            'overrides.*.fixed_commission_amount.min'=> 'Fixed commission amount cannot be negative.',
        ]);

        DB::transaction(function () use ($manager, $validated) {
            $sub = new User();
            $sub->name      = $validated['name'];
            $sub->email     = $validated['email'];
            $sub->password  = Hash::make($validated['password']);
            $sub->role      = 'sub_promoter';
            $sub->parent_id = $manager->id;
            $sub->paid      = 0;
            $sub->save();

            if (!empty($validated['overrides'])) {
                // Deduplicate by ticket_type_id, keep the last value submitted.
                $byType = [];
                foreach ($validated['overrides'] as $row) {
                    if (!isset($row['ticket_type_id'])) continue;
                    $byType[(int) $row['ticket_type_id']] = $row;
                }

                foreach ($byType as $ticketTypeId => $row) {
                    $type = $row['commission_type'] ?? PromoterCommissionOverride::TYPE_PERCENTAGE;
                    $payload = [
                        'promoter_manager_id'    => $manager->id,
                        'sub_promoter_id'        => $sub->id,
                        'ticket_type_id'         => $ticketTypeId,
                        'commission_type'        => $type,
                        'commission_percentage'  => null,
                        'fixed_commission_amount'=> null,
                    ];

                    if ($type === PromoterCommissionOverride::TYPE_FIXED) {
                        // Fixed-amount mode: store the RSD amount, default
                        // the percentage column to 0 so reports stay sane.
                        $payload['fixed_commission_amount'] = isset($row['fixed_commission_amount'])
                            ? (float) $row['fixed_commission_amount']
                            : 0.0;
                        $payload['commission_percentage']   = 0.0;
                    } else {
                        // Percentage mode: keep the legacy behaviour.
                        $payload['commission_percentage']   = isset($row['commission_percentage'])
                            ? (float) $row['commission_percentage']
                            : 100.0;
                        $payload['fixed_commission_amount'] = null;
                    }

                    PromoterCommissionOverride::create($payload);
                }
            }
        });

        return redirect()->route('promoter_manager.sub_promoters.index')
            ->with('success', __('alert.sub_promoter_created_success'));
    }

    public function subPromoterEdit($id)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $sub = User::where('role', 'sub_promoter')
            ->where('parent_id', $manager->id)
            ->with('commissionOverridesReceived')
            ->findOrFail($id);

        $ticketTypes = TicketType::orderBy('name')->get();

        // Build a per-ticket-type override summary used by the form to
        // pre-fill the right control (percentage input vs. RSD input) and
        // by the radio that selects which mode is active.
        $overridesByType = [];
        foreach ($sub->commissionOverridesReceived as $ov) {
            $overridesByType[$ov->ticket_type_id] = [
                'type'         => $ov->commission_type ?: PromoterCommissionOverride::TYPE_PERCENTAGE,
                'percentage'   => $ov->commission_percentage !== null ? (float) $ov->commission_percentage : null,
                'fixed_amount' => $ov->fixed_commission_amount !== null ? (float) $ov->fixed_commission_amount : null,
            ];
        }

        // Debt summary for the sub + recent payment history (manager's
        // view of payments he has received from this sub).
        /** @var DebtService $debt */
        $debt = app(DebtService::class);
        $debtSummary = $debt->subPromoterDebt($sub);
        $recentPayments = \App\Models\SubPromoterPayment::with(['payer', 'receiver', 'recorder'])
            ->where('payment_type', \App\Models\SubPromoterPayment::TYPE_SUB_TO_MANAGER)
            ->where('payer_id', $sub->id)
            ->orderByDesc('paid_at')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('pages.promoter_managers.sub_promoters.edit', compact(
            'sub',
            'ticketTypes',
            'overridesByType',
            'debtSummary',
            'recentPayments'
        ));
    }

    public function subPromoterUpdate(Request $request, $id)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $sub = User::where('role', 'sub_promoter')
            ->where('parent_id', $manager->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($sub->id)],
            'password' => 'nullable|string|min:8',
            'overrides'                              => 'array',
            'overrides.*.ticket_type_id'             => 'required|integer|exists:ticket_types,id',
            'overrides.*.commission_type'            => 'required|in:percentage,fixed',
            'overrides.*.commission_percentage'      => 'nullable|numeric|min:0|max:100',
            'overrides.*.fixed_commission_amount'    => 'nullable|numeric|min:0',
        ], [
            'overrides.*.commission_type.in'         => 'Commission type must be either "percentage" or "fixed".',
            'overrides.*.commission_percentage.min'  => 'Percentage cannot be negative.',
            'overrides.*.commission_percentage.max'  => 'Percentage cannot exceed 100.',
            'overrides.*.fixed_commission_amount.min'=> 'Fixed commission amount cannot be negative.',
        ]);

        DB::transaction(function () use ($manager, $sub, $validated) {
            $sub->name = $validated['name'];
            $sub->email = $validated['email'];
            if (!empty($validated['password'])) {
                $sub->password = Hash::make($validated['password']);
            }
            $sub->save();

            // Replace all overrides for this (manager, sub) pair with the new
            // set. Simpler than diffing and matches the "edit one screen" UX.
            PromoterCommissionOverride::where('promoter_manager_id', $manager->id)
                ->where('sub_promoter_id', $sub->id)
                ->delete();

            if (!empty($validated['overrides'])) {
                $byType = [];
                foreach ($validated['overrides'] as $row) {
                    if (!isset($row['ticket_type_id'])) continue;
                    $byType[(int) $row['ticket_type_id']] = $row;
                }
                foreach ($byType as $ticketTypeId => $row) {
                    $type = $row['commission_type'] ?? PromoterCommissionOverride::TYPE_PERCENTAGE;
                    $payload = [
                        'promoter_manager_id'    => $manager->id,
                        'sub_promoter_id'        => $sub->id,
                        'ticket_type_id'         => $ticketTypeId,
                        'commission_type'        => $type,
                        'commission_percentage'  => null,
                        'fixed_commission_amount'=> null,
                    ];

                    if ($type === PromoterCommissionOverride::TYPE_FIXED) {
                        $payload['fixed_commission_amount'] = isset($row['fixed_commission_amount'])
                            ? (float) $row['fixed_commission_amount']
                            : 0.0;
                        $payload['commission_percentage']   = 0.0;
                    } else {
                        $payload['commission_percentage']   = isset($row['commission_percentage'])
                            ? (float) $row['commission_percentage']
                            : 100.0;
                        $payload['fixed_commission_amount'] = null;
                    }

                    PromoterCommissionOverride::create($payload);
                }
            }
        });

        return redirect()->route('promoter_manager.sub_promoters.index')
            ->with('success', __('alert.sub_promoter_updated_success'));
    }

    public function subPromoterDestroy($id)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $sub = User::where('role', 'sub_promoter')
            ->where('parent_id', $manager->id)
            ->findOrFail($id);
        $sub->delete();

        return redirect()->route('promoter_manager.sub_promoters.index')
            ->with('success', __('alert.sub_promoter_deleted_success'));
    }

    /* ---------- Promoter-manager dashboard ---------- */

    public function dashboard()
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $successfulSaleStatuses = ['completed', 'sent'];
        $endDate = now();
        $startDate30Days = now()->subDays(30);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);

        // The hierarchy debt summary is the single source of truth for
        // "what does my team owe" and "what have I already collected".
        $debtSummary = $debt->promoterManagerDebt($manager);
        $teamUserIds = $debtSummary['team_user_ids'];

        // Per-sub-promoter debt summary used by the "team debts" cards on
        // the dashboard.
        $subDebts = $debt->subDebtsForManager($manager);
        $subPromoters = $subDebts->pluck('user');

        // Attach the count of completed/sent orders placed by each sub
        // promoter for the order-count column + the leaderboard.
        $orderCountsByUser = [];
        if ($subPromoters->isNotEmpty()) {
            $orderCountsByUser = TicketOrder::whereIn('requested_by', $subPromoters->pluck('id'))
                ->publicOnly()
                ->whereIn('job_status', $successfulSaleStatuses)
                ->select('requested_by', DB::raw('COUNT(*) as cnt'))
                ->groupBy('requested_by')
                ->pluck('cnt', 'requested_by');
            foreach ($subPromoters as $sp) {
                $sp->sub_orders_count = (int) ($orderCountsByUser[$sp->id] ?? 0);
            }
        }

        // Top sub-promoters leaderboard: rank subs by gross revenue, then
        // by orders, then alphabetically. The view consumes the same
        // per-sub fields we already populated above.
        $topSubs = $subDebts->sortBy([
            ['gross_sales', 'desc'],
            ['user.name', 'asc'],
        ])->values();

        // Aggregated "what the team owes me" — sum of every sub's debt.
        $teamOwedToManager = (float) $subDebts->sum('amount_owed_to_manager');
        $teamAlreadyPaidToManager = (float) $subDebts->sum('amount_already_paid');

        // Last 30 days commission (manager's share) — uses the
        // per-beneficiary rows joined to their parent order's created_at.
        $managerCommissionLast30Days = (float) TicketOrderCommission::where('beneficiary_user_id', $manager->id)
            ->whereHas('ticketOrder', function ($q) use ($manager, $startDate30Days, $endDate) {
                $q->where('requested_by', $manager->id)
                    ->whereBetween('created_at', [$startDate30Days, $endDate]);
            })
            ->sum('commission_amount');

        // Recent payment history shown at the bottom of the dashboard.
        $recentPaymentsFromSubs = $debt->recentPaymentsReceivedByManager($manager, 8);
        $recentPaymentsToOrganizers = $debt->recentPaymentsToOrganizersByManager($manager, 8);

        $jobStatusColors = [
            'pending'    => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-600 dark:text-yellow-100',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-600 dark:text-blue-100',
            'failed'     => 'bg-red-100 text-red-800 dark:bg-red-600 dark:text-red-100',
            'blocked'    => 'bg-gray-200 text-gray-700 dark:bg-gray-500 dark:text-gray-200',
            'completed'  => 'bg-green-100 text-green-800 dark:bg-green-600 dark:text-green-100',
            'sent'       => 'bg-teal-100 text-teal-800 dark:bg-teal-600 dark:text-teal-100',
        ];

        return view('pages.promoter_managers.dashboard', compact(
            'manager',
            'debtSummary',
            'teamUserIds',
            'subDebts',
            'subPromoters',
            'topSubs',
            'teamOwedToManager',
            'teamAlreadyPaidToManager',
            'managerCommissionLast30Days',
            'recentPaymentsFromSubs',
            'recentPaymentsToOrganizers',
            'jobStatusColors'
        ));
    }
}
