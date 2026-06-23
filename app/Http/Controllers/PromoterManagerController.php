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

        // Map of manager_id => list of sub debt rows for the admin modal.
        // Each row: ['user' => User, 'gross_sales', 'sub_commission',
        //            'amount_owed_to_manager', 'amount_already_paid']
        $subDebtsByManager = [];

        // Map of manager_id => ['fromSubs' => Collection, 'toOrganizers' => Collection]
        // for the payment-history modal on each row.
        $recentPaymentsByManager = [];

        foreach ($managers as $manager) {
            $summary = $debt->promoterManagerDebt($manager);
            $manager->totalGrossSales = $summary['gross_sales'];
            $manager->totalCommissionEarned = $summary['manager_commission'];
            $manager->subCommissionsAllTime = $summary['sub_commissions'];
            $manager->amountPaidToOrganizers = $summary['amount_already_paid_to_organizers'];
            $manager->amountOwedToOrganizers = $summary['amount_owed_to_organizers'];

            // Pre-compute per-sub debt figures so the view can render
            // the sub-promoters modal without re-querying.
            $subDebtsByManager[$manager->id] = $debt->subDebtsForManager($manager);

            // Recent payment history (capped at 20 rows each) used by
            // the History modal so the admin can review and delete
            // mistakenly-recorded rows.
            $recentPaymentsByManager[$manager->id] = [
                'fromSubs'       => $debt->recentPaymentsReceivedByManager($manager, 20),
                'toOrganizers'   => $debt->recentPaymentsToOrganizersByManager($manager, 20),
            ];
        }

        return view('pages.admin.promoter_managers.index', compact(
            'managers',
            'subDebtsByManager',
            'recentPaymentsByManager'
        ));
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
        $manager = User::where('role', 'promoter_manager')
            ->with(['subPromoters'])
            ->findOrFail($id);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);

        // Manager's own debt summary (amount owed to organizers).
        $debtSummary = $debt->promoterManagerDebt($manager);

        // Per-sub-promoter debt breakdown used by the from-sub recording
        // form on the page.
        $subDebts = $debt->subDebtsForManager($manager);
        $subs = $subDebts->pluck('user');

        // Payment histories the admin might want to inspect on this page:
        //   - payments the manager received from his sub-promoters
        //   - payments the manager made to the organizers
        $recentPaymentsFromSubs = $debt->recentPaymentsReceivedByManager($manager, 15);
        $recentPaymentsToOrganizers = $debt->recentPaymentsToOrganizersByManager($manager, 15);

        return view('pages.admin.promoter_managers.edit', compact(
            'manager',
            'debtSummary',
            'subDebts',
            'subs',
            'recentPaymentsFromSubs',
            'recentPaymentsToOrganizers'
        ));
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

        /** @var DebtService $debt */
        $debt = app(DebtService::class);

        // Hierarchy debt summary: the single source of truth for "what
        // does my team owe me" and "what have I already forwarded to
        // organizers". Returns the manager's PERSONAL gross and the
        // subs' gross separately so the UI never has to conflate them.
        $debtSummary = $debt->promoterManagerDebt($manager);

        // The manager's own personal activity (sales, commission, order
        // and ticket counts he generated himself, NOT counting subs).
        $personal = $debt->personalManagerActivity($manager);

        // Per-sub-promoter debt summary used by the "my sub-promoters"
        // cards on the dashboard. Sorted by amount owed DESC so the
        // biggest debtors surface first.
        $subDebts = $debt->subDebtsForManager($manager)
            ->sortBy([
                ['amount_owed_to_manager', 'desc'],
                ['user.name', 'asc'],
            ])
            ->values();
        $subPromoters = $subDebts->pluck('user');

        // Aggregated team numbers for the "my team" glance card.
        $teamOwedToManager        = (float) $subDebts->sum('amount_owed_to_manager');
        $teamAlreadyPaidToManager = (float) $subDebts->sum('amount_already_paid');
        $teamCommissionTotal      = (float) $subDebts->sum('sub_commission');

        // Recent payment history shown at the bottom of the dashboard.
        $recentPaymentsFromSubs      = $debt->recentPaymentsReceivedByManager($manager, 8);
        $recentPaymentsToOrganizers  = $debt->recentPaymentsToOrganizersByManager($manager, 8);

        // Cash currently in the manager's hand (collected from subs but
        // not yet forwarded to organizers). Drives the second KPI card.
        $cashInHand = $debt->cashInHandByManager($manager);

        // Top sub-promoter leaderboard, ranked by gross revenue.
        $topSubs = $debt->topSubPromotersBySales($manager, 10);

        // Earnings breakdown: personal vs. share from sub sales. Drives
        // the "My earnings" detail section that the first KPI card
        // scrolls down to.
        $earningsBreakdown = $debt->managerEarningsBreakdown($manager);

        return view('pages.promoter_managers.dashboard', compact(
            'manager',
            'debtSummary',
            'personal',
            'subDebts',
            'subPromoters',
            'teamOwedToManager',
            'teamAlreadyPaidToManager',
            'teamCommissionTotal',
            'recentPaymentsFromSubs',
            'recentPaymentsToOrganizers',
            'cashInHand',
            'topSubs',
            'earningsBreakdown'
        ));
    }
}
