<?php

namespace Tests\Feature;

use App\Models\PromoterCommissionOverride;
use App\Models\SubPromoterPayment;
use App\Models\TicketCommission;
use App\Models\TicketOrder;
use App\Models\TicketOrderCommission;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use App\Services\DebtService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Integration tests for the debt / payment hierarchy.
 *
 * These cover the rules from the requirements:
 *  - Sub-promoter's debt to manager = ticket price MINUS sub's commission
 *  - Manager's debt to organizers = gross - manager commission - sub commissions
 *  - Every payment event flows correctly through the new SubPromoterPayment
 *    table and updates the live balances.
 */
class DebtServiceTest extends TestCase
{
    use RefreshDatabase;

    private function setupTeam(): array
    {
        $manager = User::create([
            'name'     => 'Manager One',
            'email'    => 'mgr-debt@example.com',
            'password' => Hash::make('secret123'),
            'role'     => 'promoter_manager',
        ]);

        $sub = User::create([
            'name'      => 'Sub One',
            'email'     => 'sub-debt@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'sub_promoter',
            'parent_id' => $manager->id,
        ]);

        $type = TicketType::create([
            'name'           => 'Standard',
            'price'          => 1000.00,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'w' => 100, 'h' => 100],
        ]);

        TicketCommission::create([
            'ticket_type_id'    => $type->id,
            'min_sold'          => 0,
            'max_sold'          => null,
            'commission_amount' => 100.0,
        ]);

        return compact('manager', 'sub', 'type');
    }

    private function completedOrder(User $seller, TicketType $type, int $qty, ?int $orderId = null): TicketOrder
    {
        $order = TicketOrder::create([
            'id'                   => $orderId,
            'order_number'         => strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'ordered_by'           => $seller->id,
            'requested_by'         => $seller->id,
            'email'                => 'cust@example.com',
            'job_status'           => 'completed',
            'paid'                 => $qty * $type->price,
            'total'                => $qty * $type->price,
        ]);

        TicketOrderItem::create([
            'ticket_order_id' => $order->id,
            'ticket_type_id'  => $type->id,
            'quantity'        => $qty,
            'price_at_order'  => $type->price,
        ]);

        return $order;
    }

    private function runJob(TicketOrder $order): void
    {
        (new \App\Jobs\OrderCompleted($order))->handle();
    }

    public function test_sub_debt_is_gross_minus_sub_commission(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        // Sub sells 2 tickets at 1000 RSD each. Tier commission is 100/ticket
        // (200 total). No override -> sub keeps 100% -> sub commission = 200.
        // Sub's debt to manager = 2000 - 200 = 1800.
        $subOrder = $this->completedOrder($sub, $type, 2, 1);
        $this->runJob($subOrder);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);
        $summary = $debt->subPromoterDebt($sub);

        $this->assertSame(2000.0, $summary['gross_sales']);
        $this->assertSame(200.0, $summary['sub_commission']);
        $this->assertSame(1800.0, $summary['amount_owed_to_manager']);
        $this->assertSame(0.0, $summary['amount_already_paid']);
        $this->assertSame($manager->id, $summary['manager_id']);
    }

    public function test_sub_debt_drops_after_payment_recorded(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        $subOrder = $this->completedOrder($sub, $type, 2, 1);
        $this->runJob($subOrder);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);

        // Manager records a 500 RSD payment received from the sub.
        $debt->recordPayment(
            SubPromoterPayment::TYPE_SUB_TO_MANAGER,
            payer: $sub,
            receiver: $manager,
            amount: 500.0,
            recorder: $manager,
            note: 'Cash',
        );

        $summary = $debt->subPromoterDebt($sub);
        $this->assertSame(500.0, $summary['amount_already_paid']);
        $this->assertSame(1300.0, $summary['amount_owed_to_manager']);
    }

    public function test_manager_debt_subtracts_full_commission_pool(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        // Sub has a fixed 30 RSD per ticket override.
        PromoterCommissionOverride::create([
            'promoter_manager_id'     => $manager->id,
            'sub_promoter_id'         => $sub->id,
            'ticket_type_id'          => $type->id,
            'commission_type'         => PromoterCommissionOverride::TYPE_FIXED,
            'commission_percentage'   => 0,
            'fixed_commission_amount' => 30.00,
        ]);

        $mgrOrder = $this->completedOrder($manager, $type, 2, 1);
        $this->runJob($mgrOrder);
        $subOrder = $this->completedOrder($sub, $type, 3, 2);
        $this->runJob($subOrder);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);
        $summary = $debt->promoterManagerDebt($manager);

        // Team gross = 5000
        // Manager commission = 200 (own) + 210 (from sub) = 410
        // Sub commission = 90
        // Manager debt to organizers = 5000 - 0 - 410 - 90 = 4500
        $this->assertSame(5000.0, $summary['gross_sales']);
        $this->assertSame(410.0, $summary['manager_commission']);
        $this->assertSame(90.0, $summary['sub_commissions']);
        $this->assertSame(4500.0, $summary['amount_owed_to_organizers']);
    }

    public function test_manager_to_organizers_payment_reduces_debt(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        $subOrder = $this->completedOrder($sub, $type, 1, 1);
        $this->runJob($subOrder);

        /** @var DebtService $debt */
        $debt = app(DebtService::class);

        // gross=1000, manager_commission=100, sub_commission=0
        // debt = 1000 - 100 - 0 = 900

        $debt->recordPayment(
            SubPromoterPayment::TYPE_MANAGER_TO_ORGANIZERS,
            payer: $manager,
            receiver: $manager,
            amount: 200.0,
            recorder: $manager,
        );

        $summary = $debt->promoterManagerDebt($manager);
        $this->assertSame(200.0, $summary['amount_already_paid_to_organizers']);
        $this->assertSame(700.0, $summary['amount_owed_to_organizers']);
    }

    public function test_sub_promoter_payments_endpoint_records_payment(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        $subOrder = $this->completedOrder($sub, $type, 1, 1);
        $this->runJob($subOrder);

        $response = $this->actingAs($manager)->post(
            route('promoter_manager.payments.from_sub.store', $sub->id),
            ['amount' => '300.00', 'note' => 'Cash payment'],
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('sub_promoter_payments', [
            'payment_type' => SubPromoterPayment::TYPE_SUB_TO_MANAGER,
            'payer_id'     => $sub->id,
            'receiver_id'  => $manager->id,
            'amount'       => '300.00',
            'recorded_by'  => $manager->id,
            'note'         => 'Cash payment',
        ]);
    }

    public function test_sub_promoter_self_log_endpoint_works(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        $subOrder = $this->completedOrder($sub, $type, 1, 1);
        $this->runJob($subOrder);

        $response = $this->actingAs($sub)->post(
            route('sub_promoter.payments.to_manager.store'),
            ['amount' => '250.00', 'note' => 'Bank transfer'],
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('sub_promoter_payments', [
            'payment_type' => SubPromoterPayment::TYPE_SUB_TO_MANAGER,
            'payer_id'     => $sub->id,
            'receiver_id'  => $manager->id,
            'amount'       => '250.00',
            'recorded_by'  => $sub->id, // sub records his own payment
        ]);
    }
}
