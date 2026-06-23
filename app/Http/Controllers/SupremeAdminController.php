<?php

namespace App\Http\Controllers;

use App\Models\TicketOrder;
use App\Models\TicketOrderCommission;
use App\Models\TicketOrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View as ViewResponse;

/**
 * Supreme-admin overview.
 *
 * Renders a single page that gives the supreme-admin a bird's-eye view of
 * the whole promoter hierarchy:
 *
 *   - every promoter_manager
 *       - the sub_promoters they created
 *           - per-user earnings, paid to organisers, owed to organisers,
 *             gross sales, ticket count
 *   - filterable on commission earned, paid amount, owed amount
 *   - sortable on the same columns
 *
 * Numbers come from the same source-of-truth tables the existing
 * promoter-manager pages use (ticket_orders, ticket_order_items,
 * ticket_order_commissions, users.paid) so the figures stay consistent
 * with what the rest of the admin UI shows.
 */
class SupremeAdminController extends Controller
{
    /** Order statuses that count as "money in" for the festival. */
    protected const SALE_STATUSES = ['completed', 'sent'];

    /**
     * Top-level overview: every promoter_manager with the full sub-promoter
     * breakdown attached, plus query-string driven filters.
     */
    public function overview(Request $request): ViewResponse
    {
        $filters = $this->parseFilters($request);

        // Fetch all promoter managers (with their sub-promoters eagerly
        // loaded) so we can attach financial data to every row in a single
        // pass. We deliberately do *not* push the filtering down to SQL
        // here because we need the per-manager totals to decide which
        // rows to keep after attaching sub-totals — much simpler to do
        // everything in PHP for a few hundred rows.
        $managers = User::where('role', 'promoter_manager')
            ->with(['subPromoters'])
            ->orderBy('name')
            ->get();

        // Pre-aggregate once, not per manager.
        $managerIds     = $managers->pluck('id')->all();
        $allSubIds      = $managers->flatMap(fn ($m) => $m->subPromoters->pluck('id'))->unique()->values();
        $relevantUserIds = collect($managerIds)->merge($allSubIds)->unique()->values();

        // ---- Sales sums grouped by requested_by (one row per user) ----
        // Excludes private (supreme-admin) sales — they belong to the
        // supreme-admin alone and must not appear in any team total.
        $salesByUser = TicketOrder::whereIn('requested_by', $relevantUserIds)
            ->publicOnly()
            ->whereIn('job_status', self::SALE_STATUSES)
            ->selectRaw('requested_by, COALESCE(SUM(total), 0) AS gross_sales, COUNT(*) AS orders_count')
            ->groupBy('requested_by')
            ->get()
            ->keyBy('requested_by');

        // ---- Ticket count by requested_by (sum of item quantities) ----
        $ticketsByUser = TicketOrderItem::whereHas('ticketOrder', function ($q) use ($relevantUserIds) {
            $q->whereIn('requested_by', $relevantUserIds)
              ->publicOnly()
              ->whereIn('job_status', self::SALE_STATUSES);
        })
            ->selectRaw('ticket_orders.requested_by AS requested_by, COALESCE(SUM(ticket_order_items.quantity), 0) AS tickets_sold')
            ->join('ticket_orders', 'ticket_order_items.ticket_order_id', '=', 'ticket_orders.id')
            ->groupBy('ticket_orders.requested_by')
            ->get()
            ->keyBy('requested_by');

        // ---- Commission sums by beneficiary ----
        $commissionByUser = TicketOrderCommission::whereIn('beneficiary_user_id', $relevantUserIds)
            ->selectRaw('beneficiary_user_id, COALESCE(SUM(commission_amount), 0) AS commission_earned')
            ->groupBy('beneficiary_user_id')
            ->get()
            ->keyBy('beneficiary_user_id');

        // ---- Paid-by-user (from users.paid) ----
        $paidByUser = User::whereIn('id', $relevantUserIds)
            ->select('id', 'paid')
            ->get()
            ->keyBy('id');

        // ---- Helpers ----
        $resolveRow = function (User $user) use ($salesByUser, $ticketsByUser, $commissionByUser, $paidByUser) {
            $uid        = $user->id;
            $sales      = $salesByUser[$uid]->gross_sales      ?? 0.0;
            $orders     = $salesByUser[$uid]->orders_count     ?? 0;
            $tickets    = $ticketsByUser[$uid]->tickets_sold   ?? 0;
            $commission = (float) ($commissionByUser[$uid]->commission_earned ?? 0.0);
            $paid       = (float) ($paidByUser[$uid]->paid ?? 0.0);
            $owed       = (float) $sales - $paid - $commission;

            return (object) [
                'user'              => $user,
                'gross_sales'       => (float) $sales,
                'orders_count'      => (int) $orders,
                'tickets_sold'      => (int) $tickets,
                'commission_earned' => $commission,
                'paid'              => $paid,
                'owed'              => $owed,
            ];
        };

        // Attach per-user rows to every sub-promoter and compute manager-level
        // totals (which include both direct sales and sub sales).
        foreach ($managers as $manager) {
            $subRows = $manager->subPromoters->map(fn ($sub) => $resolveRow($sub));

            $manager->subRows = $subRows;

            // Manager-level direct figures (their own orders only).
            $managerRow = $resolveRow($manager);

            // Manager-level team totals = manager's own + every sub.
            $manager->team_gross_sales = $managerRow->gross_sales
                + (float) $subRows->sum('gross_sales');
            $manager->team_orders = $managerRow->orders_count
                + (int) $subRows->sum('orders_count');
            $manager->team_tickets = $managerRow->tickets_sold
                + (int) $subRows->sum('tickets_sold');
            $manager->team_commission_earned = $managerRow->commission_earned
                + (float) $subRows->sum('commission_earned');
            $manager->team_paid = $managerRow->paid; // paid field is only on the manager
            $manager->team_owed = $manager->team_gross_sales
                - $manager->team_paid
                - $manager->team_commission_earned;

            $manager->self = $managerRow;
        }

        // Apply text search (on manager name/email) — always honored,
        // independent of the numeric filters, because admins almost always
        // start by typing a name.
        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $needle = mb_strtolower($search);
            $managers = $managers->filter(function ($manager) use ($needle) {
                if (str_contains(mb_strtolower($manager->name), $needle)) return true;
                if (str_contains(mb_strtolower($manager->email), $needle)) return true;
                return $manager->subPromoters->contains(function ($sub) use ($needle) {
                    return str_contains(mb_strtolower($sub->name), $needle)
                        || str_contains(mb_strtolower($sub->email), $needle);
                });
            })->values();
        }

        // Apply numeric filters. The "kind" determines whether we look at
        // team totals (commission earned, paid, owed) or individual rows
        // for the user-level filter (so sub-promoters can be targeted).
        $managers = $managers->filter(function ($manager) use ($filters) {
            $team = (object) [
                'commission_earned' => $manager->team_commission_earned,
                'paid'              => $manager->team_paid,
                'owed'              => $manager->team_owed,
                'gross_sales'       => $manager->team_gross_sales,
            ];

            foreach ($filters as $f) {
                $value = match ($f['scope']) {
                    'team'   => $team->{$f['field']}   ?? 0.0,
                    'self'   => $manager->self->{$f['field']} ?? 0.0,
                    default  => 0.0,
                };
                if (!$this->matchesFilter($value, $f['op'], $f['amount'])) {
                    return false;
                }
            }

            return true;
        })->values();

        // Sort.
        [$sortField, $sortDir] = $this->parseSort($request);
        $managers = $this->sortManagers($managers, $sortField, $sortDir);

        // Summary KPIs at the top of the page.
        $summary = $this->buildSummary($managers);

        return view('pages.supremeadmin.overview', [
            'managers'   => $managers,
            'filters'    => $filters,
            'sortField'  => $sortField,
            'sortDir'    => $sortDir,
            'search'     => $search,
            'summary'    => $summary,
            'scope'      => $request->query('scope', 'team'), // for current filter UI
        ]);
    }

    /**
     * Parse all ?filter[N][field]=...&filter[N][op]=...&filter[N][amount]=...
     * triplets from the request into a normalized array.
     *
     * @return array<int,array{field:string,op:string,amount:float,scope:string}>
     */
    protected function parseFilters(Request $request): array
    {
        $allowedFields = [
            'commission_earned' => ['team', 'self'],
            'paid'              => ['team', 'self'],
            'owed'              => ['team', 'self'],
            'gross_sales'       => ['team', 'self'],
        ];
        $allowedOps = ['>=', '<=', '=', '>', '<'];

        $raw = $request->query('filter', []);
        if (!is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $row) {
            if (!is_array($row)) continue;
            $field = (string) ($row['field'] ?? '');
            $op    = (string) ($row['op']    ?? '>=');
            $amt   = $row['amount'] ?? null;
            $scope = (string) ($row['scope'] ?? 'team');

            if (!isset($allowedFields[$field])) continue;
            if (!in_array($scope, $allowedFields[$field], true)) {
                $scope = $allowedFields[$field][0];
            }
            if (!in_array($op, $allowedOps, true)) $op = '>=';
            if ($amt === null || $amt === '' || !is_numeric($amt)) continue;

            $out[] = [
                'field'  => $field,
                'op'     => $op,
                'amount' => (float) $amt,
                'scope'  => $scope,
            ];
        }

        return $out;
    }

    /**
     * Parse the ?sort=field_dir query parameter into a field name + direction.
     *
     * @return array{0:string,1:string}
     */
    protected function parseSort(Request $request): array
    {
        $allowed = [
            'name', 'subs', 'gross_sales', 'commission_earned',
            'paid', 'owed', 'tickets', 'orders',
        ];
        $sort = (string) $request->query('sort', 'commission_earned_desc');
        [$field, $dir] = array_pad(explode('_', $sort, 2), 2, 'desc');

        if (!in_array($field, $allowed, true)) $field = 'commission_earned';
        if (!in_array(strtolower($dir), ['asc', 'desc'], true)) $dir = 'desc';

        return [$field, strtolower($dir)];
    }

    /**
     * Sort the manager collection by the chosen field. Sub-promoters within
     * each manager are sorted by the same field when applicable so the
     * nested table also lines up.
     */
    protected function sortManagers($managers, string $field, string $dir): \Illuminate\Support\Collection
    {
        $valueForManager = function ($manager) use ($field) {
            switch ($field) {
                case 'name':              return mb_strtolower($manager->name);
                case 'subs':              return $manager->subPromoters->count();
                case 'gross_sales':       return (float) $manager->team_gross_sales;
                case 'commission_earned': return (float) $manager->team_commission_earned;
                case 'paid':              return (float) $manager->team_paid;
                case 'owed':              return (float) $manager->team_owed;
                case 'tickets':           return (int) $manager->team_tickets;
                case 'orders':            return (int) $manager->team_orders;
                default:                  return 0;
            }
        };

        $sorted = $managers->sortBy($valueForManager, SORT_REGULAR, $dir === 'desc')->values();

        // Also sort the nested sub-rows by the same field when meaningful.
        $subSortable = ['gross_sales', 'commission_earned', 'paid', 'owed', 'tickets', 'orders', 'name'];
        if (in_array($field, $subSortable, true)) {
            foreach ($sorted as $manager) {
                $subField = $field;
                $manager->subRows = $manager->subRows->sortBy(function ($row) use ($subField) {
                    if ($subField === 'name') return mb_strtolower($row->user->name);
                    return $row->{$subField} ?? 0;
                }, SORT_REGULAR, $dir === 'desc')->values();
            }
        }

        return $sorted;
    }

    /**
     * Build the headline KPIs shown at the top of the overview.
     */
    protected function buildSummary($managers): array
    {
        return [
            'managers_count'       => $managers->count(),
            'subs_count'           => (int) $managers->sum(fn ($m) => $m->subPromoters->count()),
            'gross_sales_total'    => (float) $managers->sum('team_gross_sales'),
            'commission_total'     => (float) $managers->sum('team_commission_earned'),
            'paid_total'           => (float) $managers->sum('team_paid'),
            'owed_total'           => (float) $managers->sum('team_owed'),
            'tickets_total'        => (int)   $managers->sum('team_tickets'),
        ];
    }

    /**
     * Check whether a numeric value satisfies a filter predicate.
     */
    protected function matchesFilter(float $value, string $op, float $amount): bool
    {
        return match ($op) {
            '>=' => $value >= $amount,
            '<=' => $value <= $amount,
            '='  => abs($value - $amount) < 0.005,
            '>'  => $value >  $amount,
            '<'  => $value <  $amount,
            default => true,
        };
    }
}