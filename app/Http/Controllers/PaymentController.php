<?php

namespace App\Http\Controllers;

use App\Models\SubPromoterPayment;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Controller for the payment-recording flows in the promoter hierarchy.
 *
 * Two actions are exposed:
 *
 *  - recordFromSub:  the promoter-manager records that a sub-promoter has
 *                    handed over the amount owed. Payer = sub, receiver =
 *                    manager. Only the manager can submit this; the
 *                    manager_id is taken from the URL, the sub from the
 *                    form (or vice versa, we go from URL).
 *
 *  - recordToOrganizers: the promoter-manager records that he has paid
 *                    the organizers. Payer = manager, receiver = manager
 *                    himself (organizer has no user row in this app; the
 *                    row is informational and is scoped to the manager).
 *
 *  - recordSubToManager: a sub-promoter self-logs a payment he made to
 *                    his manager. Optional — the manager's record is the
 *                    source of truth, but letting the sub keep a journal
 *                    is useful so he can show "I paid X on Y date".
 */
class PaymentController extends Controller
{
    public function __construct(private DebtService $debt) {}

    /**
     * Promoter-manager records a payment received from a sub-promoter.
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

    /**
     * Promoter-manager records a payment made to the event organizers.
     */
    public function recordToOrganizers(Request $request)
    {
        $manager = Auth::user();
        abort_unless($manager && $manager->isPromoterManager(), 403);

        $validated = $request->validate([
            'amount'  => 'required|numeric|min:0.01|max:9999999.99',
            'note'    => 'nullable|string|max:500',
            'paid_at' => 'nullable|date',
        ]);

        $this->debt->recordPayment(
            type:     SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS,
            payer:    $manager,
            receiver: $manager, // organizer has no user row; self-loop
            amount:   (float) $validated['amount'],
            recorder: $manager,
            note:     $validated['note'] ?? null,
            paidAt:   isset($validated['paid_at']) ? \Carbon\Carbon::parse($validated['paid_at']) : null,
        );

        return redirect()->route('promoter_manager.dashboard')
            ->with('success', __('alert.payment_to_organizers_recorded_success', [
                'amount' => number_format((float) $validated['amount'], 2),
            ]));
    }

    /**
     * Sub-promoter self-logs a payment he made to his promoter-manager.
     * This is a courtesy log; the manager's own record is the source of
     * truth. The sub can use it to keep a personal journal of what he
     * has paid.
     */
    public function recordSubToManager(Request $request)
    {
        $sub = Auth::user();
        abort_unless($sub && $sub->isSubPromoter(), 403);

        $manager = $sub->promoterManager();
        abort_unless($manager, 422, 'You are not assigned to a promoter-manager.');

        $validated = $request->validate([
            'amount'  => 'required|numeric|min:0.01|max:9999999.99',
            'note'    => 'nullable|string|max:500',
            'paid_at' => 'nullable|date',
        ]);

        $this->debt->recordPayment(
            type:     SubPromoterPayment::TYPE_SUB_TO_MANAGER,
            payer:    $sub,
            receiver: $manager,
            amount:   (float) $validated['amount'],
            recorder: $sub,
            note:     $validated['note'] ?? null,
            paidAt:   isset($validated['paid_at']) ? \Carbon\Carbon::parse($validated['paid_at']) : null,
        );

        return redirect()->route('sub_promoter.dashboard')
            ->with('success', __('alert.payment_recorded_success', [
                'name'   => $manager->name,
                'amount' => number_format((float) $validated['amount'], 2),
            ]));
    }
}
