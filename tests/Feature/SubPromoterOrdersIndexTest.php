<?php

namespace Tests\Feature;

use App\Models\TicketOrder;
use App\Models\TicketOrderCommission;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Verifies that a sub-promoter accesses ONLY their own orders on the
 * dedicated /sub-promoter/orders page and never sees orders placed by
 * other sub-promoters (even when they share the same promoter-manager).
 */
class SubPromoterOrdersIndexTest extends TestCase
{
    use RefreshDatabase;

    private function makeManagerWithSubs(): array
    {
        $manager = User::create([
            'name'     => 'Manager A',
            'email'    => 'mgr-a@example.com',
            'password' => Hash::make('secret123'),
            'role'     => 'promoter_manager',
        ]);

        $subA = User::create([
            'name'      => 'Sub A',
            'email'     => 'sub-a@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'sub_promoter',
            'parent_id' => $manager->id,
        ]);

        $subB = User::create([
            'name'      => 'Sub B',
            'email'     => 'sub-b@example.com',
            'password'  => Hash::make('secret123'),
            'role'      => 'sub_promoter',
            'parent_id' => $manager->id,
        ]);

        $type = TicketType::create([
            'name'           => 'Test',
            'price'          => 500.00,
            'qr_coordinates' => ['x' => 0, 'y' => 0, 'w' => 100, 'h' => 100],
        ]);

        return compact('manager', 'subA', 'subB', 'type');
    }

    private function makeOrder(User $seller, TicketType $type, int $qty): TicketOrder
    {
        $order = TicketOrder::create([
            'order_number'   => strtoupper(substr(md5(uniqid('', true)), 0, 6)),
            'ordered_by'     => $seller->id,
            'requested_by'   => $seller->id,
            'email'          => 'cust@example.com',
            'job_status'     => 'completed',
            'paid'           => $qty * $type->price,
            'total'          => $qty * $type->price,
        ]);

        TicketOrderItem::create([
            'ticket_order_id'    => $order->id,
            'ticket_type_id'     => $type->id,
            'quantity'           => $qty,
            'price_at_order'     => $type->price,
            'commission_earned'  => 0.0,
        ]);

        TicketOrderCommission::create([
            'ticket_order_id'      => $order->id,
            'ticket_order_item_id' => $order->items->first()->id,
            'beneficiary_user_id'  => $seller->id,
            'beneficiary_role'     => 'sub_promoter',
            'quantity'             => $qty,
            'commission_amount'    => 25.00,
        ]);

        return $order;
    }

    public function test_sub_promoter_sees_only_own_orders(): void
    {
        ['subA' => $subA, 'subB' => $subB, 'type' => $type] = $this->makeManagerWithSubs();

        $orderA1 = $this->makeOrder($subA, $type, 2);
        $orderA2 = $this->makeOrder($subA, $type, 3);
        $orderB1 = $this->makeOrder($subB, $type, 4);

        $response = $this->actingAs($subA)->get(route('sub_promoter.orders.index'));

        $response->assertOk();
        $response->assertSee('#' . $orderA1->order_number);
        $response->assertSee('#' . $orderA2->order_number);
        $response->assertDontSee('#' . $orderB1->order_number);
    }

    public function test_sub_promoter_orders_page_requires_sub_promoter_role(): void
    {
        ['manager' => $manager] = $this->makeManagerWithSubs();

        $response = $this->actingAs($manager)->get(route('sub_promoter.orders.index'));

        $response->assertStatus(403);
    }
}
