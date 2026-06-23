<?php

namespace Tests\Feature;

use App\Models\PromoterCommissionOverride;
use App\Models\TicketCommission;
use App\Models\TicketOrder;
use App\Models\TicketOrderCommission;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Verifies that the promoter-manager dashboard's KPIs correctly account
 * for sub-promoter sales and commissions:
 *   - Gross sales include orders placed by the manager AND his sub-promoters.
 *   - "My Commission" KPI sums ONLY commission rows where the manager is
 *     the beneficiary (i.e. his personal share, not the team's total).
 *   - "Amount Owed to Organizers" subtracts BOTH the manager's commission
 *     and every sub-promoter's commission from team gross sales.
 */
class PromoterManagerDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function setupTeam(): array
    {
        $manager = User::create([
            'name'     => 'Manager One',
            'email'    => 'mgr@example.com',
            'password' => Hash::make('secret123'),
            'role'     => 'promoter_manager',
        ]);

        $sub = User::create([
            'name'      => 'Sub One',
            'email'     => 'sub@example.com',
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
            'ticket_type_id'     => $type->id,
            'min_sold'           => 0,
            'max_sold'           => null,
            'commission_amount'  => 100.0,
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
            'ticket_order_id'    => $order->id,
            'ticket_type_id'     => $type->id,
            'quantity'           => $qty,
            'price_at_order'     => $type->price,
        ]);

        return $order;
    }

    private function runJob(TicketOrder $order): void
    {
        (new \App\Jobs\OrderCompleted($order))->handle();
    }

    public function test_team_gross_sales_includes_sub_orders(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        // Manager places order: 3 tickets at 1000 RSD = 3000 RSD gross
        $mgrOrder = $this->completedOrder($manager, $type, 3, 1);
        $this->runJob($mgrOrder);

        // Sub places order: 2 tickets at 1000 RSD = 2000 RSD gross
        $subOrder = $this->completedOrder($sub, $type, 2, 2);
        $this->runJob($subOrder);

        $response = $this->actingAs($manager)->get(route('promoter_manager.dashboard'));
        $response->assertOk();

        // Team gross sales should include both: 3000 + 2000 = 5000 RSD.
        $response->assertViewHas('managerGrossSalesAllTime', 5000.0);
        $response->assertViewHas('managerDirectSalesAllTime', 3000.0);
    }

    public function test_amount_owed_subtracts_manager_and_sub_commission(): void
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

        // Manager places 2 tickets: gross 2000, commission 200 (100/ticket * 2)
        $mgrOrder = $this->completedOrder($manager, $type, 2, 1);
        $this->runJob($mgrOrder);

        // Sub places 3 tickets: gross 3000, sub gets 90 (30*3), manager gets 210 (210)
        $subOrder = $this->completedOrder($sub, $type, 3, 2);
        $this->runJob($subOrder);

        // Team gross sales = 5000
        // Manager commission = 200 (own) + 210 (from sub) = 410
        // Sub commission = 90
        // Amount owed = 5000 - 0 (paid) - 410 - 90 = 4500

        $response = $this->actingAs($manager)->get(route('promoter_manager.dashboard'));
        $response->assertOk();

        $response->assertViewHas('managerCommissionAllTime', 410.0);
        $response->assertViewHas('subCommissionsAllTime', 90.0);
        $response->assertViewHas('amountOwed', 4500.0);
    }

    public function test_managers_personal_commission_excludes_sub_commission(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        PromoterCommissionOverride::create([
            'promoter_manager_id'     => $manager->id,
            'sub_promoter_id'         => $sub->id,
            'ticket_type_id'          => $type->id,
            'commission_type'         => PromoterCommissionOverride::TYPE_FIXED,
            'commission_percentage'   => 0,
            'fixed_commission_amount' => 30.00,
        ]);

        // Sub places 1 ticket: gross 100, sub gets 30, manager gets 70
        $subOrder = $this->completedOrder($sub, $type, 1, 1);
        $this->runJob($subOrder);

        $response = $this->actingAs($manager)->get(route('promoter_manager.dashboard'));

        // "My Commission" KPI should show ONLY the manager's share (70),
        // not the combined manager + sub total (100).
        $response->assertViewHas('managerCommissionAllTime', 70.0);
    }
}
