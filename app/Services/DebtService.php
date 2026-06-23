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
