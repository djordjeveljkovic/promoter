<?php

namespace App\Http\Controllers;

use App\Models\SubPromoterPayment;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View as ViewResponse;

/**
 * Controller for the payment-recording flows in the promoter hierarchy.
 *
 * Permission matrix enforced here (single source of truth):
 *
 *   • sub_promoter  → NO recording actions at all. Only views payments
 *                     that his manager recorded for him. (No endpoint
 *                     is registered for sub_promoters in routes/web.php.)
 *
 *   • promoter_mgr  → Records payments FROM his sub-promoters
 *                     (recordFromSub). Cannot record his own payment;
 *                     that action is admin-only.
 *
 *   • admin         → Records the promoter-manager's own payment to the
 *                     organizers (adminRecordFromManager). Can also
 *                     record sub-to-manager payments on behalf of the
 *                     manager (adminRecordFromSub).
 *
 *   • superadmin    → Same as admin (the admin middleware permits both
 *                     roles) — they can also see and do everything.
 *
 * Two payment types are persisted (see SubPromoterPayment):
 *
 *   - 'sub_to_manager'         — sub → manager (the cash the manager
 *                                collected for orders the sub placed).
 *   - 'manager_to_organizers'  — manager → organizers (the net revenue
 *                                the manager has forwarded after the
 *                                team commission pool has been kept).
 */
class PaymentController extends Controller
{
    public function __construct(private DebtService $debt) {}

    /* ============================================================== */
    /*  PROMOTER-MANAGER ACTIONS                                       */
    /* ============================================================== */

    /**
     * Promoter-manager records a payment received from a sub-promoter.
     *
     * This is the ONLY payment-recording action a manager can perform.
     */
    public function recordFromSub(Request $request, int $subId)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $sub = User::where('role', 'sub_promoter')
            ->where('parent_id', $manager->id)
            ->findOrFail($subId);

        $validated = $request->validate([
            'amount'   => 'required|numeric|min:0.01|max:9999999.99',
            'note'     => 'nullable|string|max:500',
            'paid_at'  => 'nullable|date',
        ]);

        $this->debt->recordPayment(
            type:     SubPromoterPayment::TYPE_SUB_TO_MANAGER,
            payer:    $sub,
            receiver: $manager,
            amount:   (float) $validated['amount'],
            recorder: $manager,
            note:     $validated['note'] ?? null,
            paidAt:   isset($validated['paid_at']) ? \Carbon\Carbon::parse($validated['paid_at']) : null,
        );

        $back = $request->input('redirect_to');
        if ($back === 'sub_promoters_index') {
            return redirect()->route('promoter_manager.sub_promoters.index')
                ->with('success', __('alert.payment_recorded_success', [
                    'name'   => $sub->name,
                    'amount' => number_format((float) $validated['amount'], 2),
                ]));
        }

        return redirect()->route('promoter_manager.dashboard')
            ->with('success', __('alert.payment_recorded_success', [
                'name'   => $sub->name,
                'amount' => number_format((float) $validated['amount'], 2),
            ]));
    }

    /* ============================================================== */
    /*  PROMOTER-MANAGER ACTIONS — edit / delete existing payments    */
    /* ============================================================== */

    /**
     * Show the edit form for a previously-recorded sub-to-manager payment.
     *
     * Authorization: the caller must be a promoter-manager, and the payment
     * must be of type 'sub_to_manager' where the payer's parent is the caller.
     */
    public function managerEditFromSub(Request $request, int $paymentId): ViewResponse
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $payment = $this->findManagerSubPayment($manager, $paymentId);

        $sub = User::findOrFail($payment->payer_id);

        return view('pages.promoter_managers.sub_promoters.edit_payment', [
            'payment' => $payment,
            'sub'     => $sub,
        ]);
    }

    /**
     * Update an existing sub-to-manager payment (mistake correction by the
     * promoter-manager). The debt figures are computed live from the ledger
     * so adjusting amount/date/note here is reflected everywhere immediately.
     */
    public function managerUpdateFromSub(Request $request, int $paymentId)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $payment = $this->findManagerSubPayment($manager, $paymentId);

        $validated = $request->validate([
            'amount'  => 'required|numeric|min:0.01|max:9999999.99',
            'note'    => 'nullable|string|max:500',
            'paid_at' => 'nullable|date',
        ]);

        $payment->amount  = (float) $validated['amount'];
        $payment->note    = $validated['note'] ?? null;
        $payment->paid_at = isset($validated['paid_at'])
            ? \Carbon\Carbon::parse($validated['paid_at'])
            : $payment->paid_at;
        $payment->save();

        return redirect()
            ->route('promoter_manager.sub_promoters.edit', $payment->payer_id)
            ->with('success', __('alert.payment_updated_success', [
                'name'   => $payment->payer?->name ?? '',
                'amount' => number_format((float) $payment->amount, 2),
            ]));
    }

    /**
     * Delete a previously-recorded sub-to-manager payment.
     *
     * No cached column needs to be reverted: sub_to_manager flows don't
     * update users.paid. Deletion is wrapped in a transaction so we never
     * half-delete a row.
     */
    public function managerDestroyFromSub(Request $request, int $paymentId)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $payment = $this->findManagerSubPayment($manager, $paymentId);
        $subId   = $payment->payer_id;
        $amount  = (float) $payment->amount;

        DB::transaction(function () use ($payment) {
            $payment->delete();
        });

        return redirect()
            ->route('promoter_manager.sub_promoters.edit', $subId)
            ->with('success', __('alert.payment_deleted_success', [
                'amount' => number_format($amount, 2),
            ]));
    }

    /**
     * Helper: locate a sub-to-manager payment that the given manager is
     * authorized to mutate. Centralizes the three checks every manager
     * action performs (role, payment type, ownership via parent_id).
     */
    private function findManagerSubPayment(User $manager, int $paymentId): SubPromoterPayment
    {
        return SubPromoterPayment::where('payment_type', SubPromoterPayment::TYPE_SUB_TO_MANAGER)
            ->whereHas('payer', function ($q) use ($manager) {
                // payer's parent must be the current manager
                $q->where('role', 'sub_promoter')->where('parent_id', $manager->id);
            })
            ->findOrFail($paymentId);
    }

    /* ============================================================== */
    /*  ADMIN ACTIONS                                                  */
    /* ============================================================== */

    /**
     * Admin (or superadmin) records a payment that a sub-promoter made
     * to his promoter-manager. This is the admin-side equivalent of
     * recordFromSub — used when the manager has not logged the cash
     * themselves but the admin needs to keep the team's debt figures
     * up to date (e.g. after a bank reconciliation).
     *
     * Authorization: only roles 'admin', 'superadmin', 'supreme' may
     * invoke this action.
     */
    public function adminRecordFromSub(Request $request, int $managerId, int $subId)
    {
        $admin = Auth::user();
        abort_unless($admin && $admin->isAdmin(), 403);

        $manager = User::where('role', 'promoter_manager')->findOrFail($managerId);

        $sub = User::where('role', 'sub_promoter')
            ->where('parent_id', $manager->id)
            ->findOrFail($subId);

        $validated = $request->validate([
            'amount'   => 'required|numeric|min:0.01|max:9999999.99',
            'note'     => 'nullable|string|max:500',
            'paid_at'  => 'nullable|date',
        ]);

        $this->debt->recordPayment(
            type:     SubPromoterPayment::TYPE_SUB_TO_MANAGER,
            payer:    $sub,
            receiver: $manager,
            amount:   (float) $validated['amount'],
            recorder: $admin,
            note:     $validated['note'] ?? null,
            paidAt:   isset($validated['paid_at']) ? \Carbon\Carbon::parse($validated['paid_at']) : null,
        );

        $back = $request->input('redirect_to', 'manager_edit');

        $redirect = match ($back) {
            'supremeadmin_overview' => route('supremeadmin.overview'),
            'manager_index'         => route('admin.promoter_managers.index'),
            default                 => route('admin.promoter_managers.edit', $manager->id),
        };

        return redirect($redirect)
            ->with('success', __('alert.admin_payment_from_sub_recorded_success', [
                'name'   => $sub->name,
                'amount' => number_format((float) $validated['amount'], 2),
                'manager'=> $manager->name,
            ]));
    }

    /**
     * Admin (or superadmin) records a payment that a promoter-manager
     * made to the event organizers.
     *
     * Per the new business rules the manager is NOT allowed to record
     * this kind of payment themselves — only an admin can. This method
     * also updates the manager's `paid` cached column so the
     * supreme-admin overview (which reads `users.paid` directly) stays
     * in sync with the SubPromoterPayment ledger.
     *
     * Authorization: only roles 'admin', 'superadmin', 'supreme' may
     * invoke this action.
     */
    public function adminRecordFromManager(Request $request, int $managerId)
    {
        $admin = Auth::user();
        abort_unless($admin && $admin->isAdmin(), 403);

        $manager = User::where('role', 'promoter_manager')->findOrFail($managerId);

        $validated = $request->validate([
            'amount'   => 'required|numeric|min:0.01|max:9999999.99',
            'note'     => 'nullable|string|max:500',
            'paid_at'  => 'nullable|date',
        ]);

        $this->debt->recordPayment(
            type:     SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS,
            payer:    $manager,
            receiver: $manager,
            amount:   (float) $validated['amount'],
            recorder: $admin,
            note:     $validated['note'] ?? null,
            paidAt:   isset($validated['paid_at']) ? \Carbon\Carbon::parse($validated['paid_at']) : null,
        );

        // Keep `users.paid` in sync with the ledger so the
        // supreme-admin overview (and any other view that reads
        // `users.paid` directly) reflects the new total. We add the
        // freshly recorded amount to whatever the cached column holds
        // so manual edits via the manager edit form are preserved.
        $manager->paid = (float) ($manager->paid ?? 0) + (float) $validated['amount'];
        $manager->save();

        $back = $request->input('redirect_to', 'manager_edit');

        $redirect = match ($back) {
            'supremeadmin_overview' => route('supremeadmin.overview'),
            'manager_index'         => route('admin.promoter_managers.index'),
            default                 => route('admin.promoter_managers.edit', $manager->id),
        };

        return redirect($redirect)
            ->with('success', __('alert.admin_payment_from_manager_recorded_success', [
                'name'   => $manager->name,
                'amount' => number_format((float) $validated['amount'], 2),
            ]));
    }

    /* ============================================================== */
    /*  ADMIN ACTIONS — DELETE recorded payments                       */
    /* ============================================================== */

    /**
     * Delete a 'manager_to_organizers' payment that was recorded by
     * mistake. Reverses the cached `users.paid` adjustment that
     * adminRecordFromManager performed when the row was first created.
     *
     * Only the admin-tier roles may invoke this action. The deletion is
     * wrapped in a transaction so the ledger row and the `users.paid`
     * cache can never disagree.
     */
    public function adminDestroyFromManager(Request $request, int $paymentId)
    {
        $admin = Auth::user();
        abort_unless($admin && $admin->isAdmin(), 403);

        $payment = SubPromoterPayment::where('payment_type', SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS)
            ->findOrFail($paymentId);

        DB::transaction(function () use ($payment) {
            $manager = User::find($payment->payer_id);
            if ($manager) {
                $manager->paid = max(0.0, (float) ($manager->paid ?? 0) - (float) $payment->amount);
                $manager->save();
            }
            $payment->delete();
        });

        return back()->with('success', __('alert.admin_payment_deleted_success', [
            'amount' => number_format((float) $payment->amount, 2),
        ]));
    }

    /**
     * Delete a 'sub_to_manager' payment that was recorded by mistake.
     * Does not touch any cached columns (sub_to_manager flows do not
     * update users.paid — the cached column tracks only
     * manager_to_organizers payments).
     */
    public function adminDestroyFromSub(Request $request, int $paymentId)
    {
        $admin = Auth::user();
        abort_unless($admin && $admin->isAdmin(), 403);

        $payment = SubPromoterPayment::where('payment_type', SubPromoterPayment::TYPE_SUB_TO_MANAGER)
            ->findOrFail($paymentId);

        DB::transaction(function () use ($payment) {
            $payment->delete();
        });

        return back()->with('success', __('alert.admin_payment_deleted_success', [
            'amount' => number_format((float) $payment->amount, 2),
        ]));
    }
}
