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
 * Verifies the per-order commission display on the promoter-manager
 * orders index page:
 *   - "My Earnings" column shows the VIEWER's personal commission per
 *     order, not the total commission pool.
 *   - For orders placed by sub-promoters, the manager's commission is the
 *     team-tier gross MINUS the sub-promoter's fixed / percentage share.
 *   - For the manager's own orders, the manager's commission equals the
 *     full tier gross (no sub involved).
 *   - The "View" button on each row links to the order detail page which
 *     exposes all tickets / QR codes for that sale.
 */
class PromoterManagerOrdersCommissionTest extends TestCase
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

        // Single tier of 100 RSD per ticket so the math is easy to follow.
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

        // Seed two tickets for the QR-code display.
        for ($i = 0; $i < $qty; $i++) {
            \App\Models\Ticket::create([
                'code'            => \Illuminate\Support\Str::uuid()->toString(),
                'ticket_type_id'  => $type->id,
                'ticket_order_id' => $order->id,
                'is_active'       => true,
            ]);
        }

        return $order;
    }

    private function runJob(TicketOrder $order): void
    {
        (new \App\Jobs\OrderCompleted($order))->handle();
    }

    public function test_manager_sees_only_his_share_for_sub_promoter_orders(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        // Sub-promoter has a fixed 30 RSD per ticket override.
        PromoterCommissionOverride::create([
            'promoter_manager_id'     => $manager->id,
            'sub_promoter_id'         => $sub->id,
            'ticket_type_id'          => $type->id,
            'commission_type'         => PromoterCommissionOverride::TYPE_FIXED,
            'commission_percentage'   => 0,
            'fixed_commission_amount' => 30.00,
        ]);

        // Manager's own order: 2 tickets. Tier = 100/ticket -> gross 200.
        $ownOrder = $this->completedOrder($manager, $type, 2, 1);
        $this->runJob($ownOrder);

        // Sub's order: 3 tickets. Tier = 100/ticket -> gross 300.
        // Sub gets 30/ticket * 3 = 90. Manager gets 300 - 90 = 210.
        $subOrder = $this->completedOrder($sub, $type, 3, 2);
        $this->runJob($subOrder);

        $response = $this->actingAs($manager)->get(route('promoter.orders.index'));
        $response->assertOk();

        // Both orders should be visible.
        $response->assertSee('#' . $ownOrder->order_number);
        $response->assertSee('#' . $subOrder->order_number);

        // The viewer's commission per order should be:
        //   - own order: 200 (full tier gross, no sub involved)
        //   - sub order: 210 (300 - 90)
        // NOT the total commission pool (which would be 200 and 300).
        $response->assertViewHas('viewerCommissionByOrder', function ($map) use ($ownOrder, $subOrder) {
            return (float) ($map[$ownOrder->id] ?? 0) === 200.0
                && (float) ($map[$subOrder->id] ?? 0) === 210.0;
        });

        // The dashboard should NOT show 300 (the total pool) for the sub
        // order. We assert the rendered HTML doesn't contain "300.00 RSD"
        // as a commission value (it would only appear if we used the
        // total_commission_earned accessor).
        $response->assertDontSee('300.00 RSD', false);
    }

    public function test_view_button_links_to_order_show(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        $order = $this->completedOrder($manager, $type, 1, 1);
        $this->runJob($order);

        $response = $this->actingAs($manager)->get(route('promoter.orders.index'));
        $response->assertOk();

        // The "View" button must link to the show route.
        $expectedHref = route('promoter.orders.show', $order->id);
        $response->assertSee($expectedHref, false);
    }

    public function test_show_page_renders_with_tickets(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        $order = $this->completedOrder($manager, $type, 2, 1);
        $this->runJob($order);

        $response = $this->actingAs($manager)->get(route('promoter.orders.show', $order->id));
        $response->assertOk();
        $response->assertSee($order->email);
        // 2 tickets generated -> show page should mention the count.
        $response->assertSee('2', false);
    }

    public function test_sub_promoter_can_view_their_own_order_but_not_others(): void
    {
        ['manager' => $manager, 'sub' => $sub, 'type' => $type] = $this->setupTeam();

        // Create a second sub-promoter under the same manager.
        $sub2 = User::create([
            'name'      => 'Sub Two',
            'email'     => 'sub2@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'sub_promoter',
            'parent_id' => $manager->id,
        ]);

        $subOrder  = $this->completedOrder($sub,  $type, 1, 1);
        $otherOrder = $this->completedOrder($sub2, $type, 1, 2);
        $this->runJob($subOrder);
        $this->runJob($otherOrder);

        // Sub can view their own order.
        $response = $this->actingAs($sub)->get(route('promoter.orders.show', $subOrder->id));
        $response->assertOk();

        // Sub CANNOT view another sub's order.
        $response = $this->actingAs($sub)->get(route('promoter.orders.show', $otherOrder->id));
        $response->assertStatus(403);

        // But the manager (promoter_manager) can view both.
        $mgrResp1 = $this->actingAs($manager)->get(route('promoter.orders.show', $subOrder->id));
        $mgrResp1->assertOk();
        $mgrResp2 = $this->actingAs($manager)->get(route('promoter.orders.show', $otherOrder->id));
        $mgrResp2->assertOk();
    }
}
