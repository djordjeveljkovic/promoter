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
 * Verifies that a promoter-manager can set a FIXED RSD commission per
 * ticket for a sub-promoter and that the OrderCompleted job correctly
 * splits the order between the sub-promoter (flat amount, independent of
 * the tier) and the promoter-manager (the team-tier remainder).
 *
 * The team-tier is based on the MANAGER's total sales (manager + every
 * sub-promoter), so the manager's per-ticket share automatically rises as
 * the team sells more, while the sub-promoter keeps the flat rate their
 * manager set.
 */
class FixedSubPromoterCommissionTest extends TestCase
{
    use RefreshDatabase;

    private function setupManagerAndSub(): array
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

        // Two tiers so the gross commission depends on previous sales.
        TicketCommission::create([
            'ticket_type_id'     => $type->id,
            'min_sold'           => 0,
            'max_sold'           => 10,
            'commission_amount'  => 50.0,
        ]);
        TicketCommission::create([
            'ticket_type_id'     => $type->id,
            'min_sold'           => 11,
            'max_sold'           => null,
            'commission_amount'  => 150.0,
        ]);

        return compact('manager', 'sub', 'type');
    }

    private function placeCompletedOrder(User $seller, TicketType $type, int $qty, ?int $orderId = null): TicketOrder
    {
        $order = TicketOrder::create([
            'id'                   => $orderId,
            'order_number'         => strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'ordered_by'           => $seller->id,
            'requested_by'         => $seller->id,
            'email'                => 'buyer@example.com',
            'job_status'           => 'completed',
            'paid'                 => $qty * $type->price,
            'total'                => $qty * $type->price,
            'total_commission_earned' => 0.0,
        ]);

        TicketOrderItem::create([
            'ticket_order_id'    => $order->id,
            'ticket_type_id'     => $type->id,
            'quantity'           => $qty,
            'price_at_order'     => $type->price,
            'commission_earned'  => 0.0,
        ]);

        return $order;
    }

    public function test_fixed_override_sub_gets_flat_manager_gets_team_tier_minus_flat(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupManagerAndSub();

        // Sub-promoter gets a fixed 30 RSD per ticket. The manager's team
        // tier at 0 prior sales is 50 RSD/ticket, so the manager should
        // receive (50 - 30) * quantity.
        PromoterCommissionOverride::create([
            'promoter_manager_id'     => $manager->id,
            'sub_promoter_id'         => $sub->id,
            'ticket_type_id'          => $type->id,
            'commission_type'         => PromoterCommissionOverride::TYPE_FIXED,
            'commission_percentage'   => 0,
            'fixed_commission_amount' => 30.00,
        ]);

        $order = $this->placeCompletedOrder($sub, $type, 4);
        // Team tier at qty 4 (baseline 0) is 50 RSD/ticket -> gross 200.
        $this->runJob($order);

        $rows = TicketOrderCommission::where('ticket_order_id', $order->id)->get();
        $this->assertCount(2, $rows, 'expected sub-promoter and manager beneficiaries');

        $subRow = $rows->firstWhere('beneficiary_user_id', $sub->id);
        $mgrRow = $rows->firstWhere('beneficiary_user_id', $manager->id);

        // Fixed: 30 RSD * 4 = 120.00 for the sub-promoter.
        $this->assertEquals(120.0, (float) $subRow->commission_amount);
        $this->assertEquals('sub_promoter', $subRow->beneficiary_role);

        // Manager: tier gross (200) - sub share (120) = 80.00.
        $this->assertEquals(80.0, (float) $mgrRow->commission_amount);
        $this->assertEquals('promoter_manager', $mgrRow->beneficiary_role);
    }

    public function test_fixed_override_uses_team_volume_not_just_subs_volume(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupManagerAndSub();

        // Seed some prior sales so the team's tier rises. We need > 10
        // tickets sold BEFORE the new order to push the team tier from
        // 50 RSD to 150 RSD per ticket.
        // Place 12 small orders by alternating sellers (manager + sub) so
        // each becomes "completed" before the new order.
        $prior = 12;
        for ($i = 0; $i < $prior; $i++) {
            $seller = ($i % 2 === 0) ? $manager : $sub;
            // Force id ordering: each prior order must have a smaller id.
            $priorOrder = $this->placeCompletedOrder($seller, $type, 1, $i + 1);
        }

        PromoterCommissionOverride::create([
            'promoter_manager_id'     => $manager->id,
            'sub_promoter_id'         => $sub->id,
            'ticket_type_id'          => $type->id,
            'commission_type'         => PromoterCommissionOverride::TYPE_FIXED,
            'commission_percentage'   => 0,
            'fixed_commission_amount' => 30.00,
        ]);

        // Now place the order whose commission we will compute. Give it
        // an id past the seed orders so calculateTierCommissionForTeam
        // counts only the prior 12.
        $order = $this->placeCompletedOrder($sub, $type, 1, 100);
        $this->runJob($order);

        $rows = TicketOrderCommission::where('ticket_order_id', $order->id)->get();
        $subRow = $rows->firstWhere('beneficiary_user_id', $sub->id);
        $mgrRow = $rows->firstWhere('beneficiary_user_id', $manager->id);

        // Team tier after 12 prior sales is 150 RSD/ticket. The new ticket
        // falls into that tier, so gross = 150.
        // Sub gets fixed 30 * 1 = 30. Manager gets 150 - 30 = 120.
        $this->assertEquals(30.0, (float) $subRow->commission_amount);
        $this->assertEquals(120.0, (float) $mgrRow->commission_amount);
    }

    public function test_fixed_override_is_capped_at_tier_gross(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupManagerAndSub();

        // Fixed 9999/ticket - clearly bigger than the tier.
        PromoterCommissionOverride::create([
            'promoter_manager_id'     => $manager->id,
            'sub_promoter_id'         => $sub->id,
            'ticket_type_id'          => $type->id,
            'commission_type'         => PromoterCommissionOverride::TYPE_FIXED,
            'commission_percentage'   => 0,
            'fixed_commission_amount' => 9999.00,
        ]);

        $order = $this->placeCompletedOrder($sub, $type, 3);
        // Tier gross: 50 * 3 = 150. Sub share must be capped at 150.
        $this->runJob($order);

        $rows = TicketOrderCommission::where('ticket_order_id', $order->id)->get();
        $subRow = $rows->firstWhere('beneficiary_user_id', $sub->id);
        $mgrRow = $rows->firstWhere('beneficiary_user_id', $manager->id);

        $this->assertEquals(150.0, (float) $subRow->commission_amount);
        // Manager share is 0 -> no row is created for the manager.
        $this->assertNull($mgrRow, 'manager should not get a row when their share rounds to zero');
    }

    public function test_percentage_override_still_uses_legacy_logic(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupManagerAndSub();

        // 40% to the sub-promoter (legacy percentage behaviour).
        PromoterCommissionOverride::create([
            'promoter_manager_id'     => $manager->id,
            'sub_promoter_id'         => $sub->id,
            'ticket_type_id'          => $type->id,
            'commission_type'         => PromoterCommissionOverride::TYPE_PERCENTAGE,
            'commission_percentage'   => 40.00,
            'fixed_commission_amount' => null,
        ]);

        $order = $this->placeCompletedOrder($sub, $type, 2);
        // Tier gross: 50 * 2 = 100. Sub gets 40%, manager gets 60%.
        $this->runJob($order);

        $rows = TicketOrderCommission::where('ticket_order_id', $order->id)->get();
        $subRow = $rows->firstWhere('beneficiary_user_id', $sub->id);
        $mgrRow = $rows->firstWhere('beneficiary_user_id', $manager->id);

        $this->assertEquals(40.0, (float) $subRow->commission_amount);
        $this->assertEquals(60.0, (float) $mgrRow->commission_amount);
    }

    public function test_no_override_sub_promoter_keeps_full_commission(): void
    {
        ['sub' => $sub, 'type' => $type] = $this->setupManagerAndSub();

        // No override row at all - sub-promoter keeps 100%.
        $order = $this->placeCompletedOrder($sub, $type, 2);
        $this->runJob($order);

        $rows = TicketOrderCommission::where('ticket_order_id', $order->id)->get();
        $this->assertCount(1, $rows);
        $this->assertEquals(100.0, (float) $rows->first()->commission_amount);
        $this->assertEquals('sub_promoter', $rows->first()->beneficiary_role);
    }

    /**
     * Run the OrderCompleted job synchronously against the test order. The
     * job is normally queued, so we resolve it through the container to
     * call handle() directly.
     */
    private function runJob(TicketOrder $order): void
    {
        $job = new \App\Jobs\OrderCompleted($order);
        $job->handle();
    }
}
