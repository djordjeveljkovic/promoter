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
 * Verifies that the promoter-manager dashboard renders the new 4-KPI
 * layout, leaderboard, debts overview, and payment evidence sections
 * without throwing a view exception. The dashboard was refactored to:
 *   - show 4 top-of-page KPI cards (earnings, cash in hand, money with
 *     subs, debt to organizers);
 *   - include a "My earnings" breakdown anchored at #earnings;
 *   - include a top sub-promoters leaderboard anchored at /links to the
 *     sub-promoter edit page;
 *   - include a debts overview + per-sub list anchored at #debts;
 *   - include payment-evidence ledgers (from subs + to organizers).
 */
class PromoterManagerDashboardRenderTest extends TestCase
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

    public function test_dashboard_renders_with_4_kpis_earnings_and_debts(): void
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

        $this->completedOrder($manager, $type, 2, 1);
        $this->runJob(TicketOrder::find(1));
        $this->completedOrder($sub, $type, 3, 2);
        $this->runJob(TicketOrder::find(2));

        $response = $this->actingAs($manager)->get(route('promoter_manager.dashboard'));
        $response->assertOk();

        // 4 KPI card labels.
        $response->assertSee(__('promoter_managers.dashboard.kpi.my_earnings.label'));
        $response->assertSee(__('promoter_managers.dashboard.kpi.cash_in_hand.label'));
        $response->assertSee(__('promoter_managers.dashboard.kpi.money_with_promoters.label'));
        $response->assertSee(__('promoter_managers.dashboard.kpi.debt_to_organizers.label'));

        // Earnings breakdown + total.
        $response->assertSee(__('promoter_managers.dashboard.earnings_section.heading'));
        $response->assertSee(__('promoter_managers.dashboard.earnings_section.my_commission'));
        $response->assertSee(__('promoter_managers.dashboard.earnings_section.sub_commission_share'));
        $response->assertSee(__('promoter_managers.dashboard.earnings_section.total_earned'));

        // Leaderboard.
        $response->assertSee(__('promoter_managers.dashboard.leaderboard.heading'));
        $response->assertSee(__('promoter_managers.dashboard.leaderboard.header_gross'));
        $response->assertSee(__('promoter_managers.dashboard.leaderboard.header_manager_commission'));

        // Debts section.
        $response->assertSee(__('promoter_managers.dashboard.debts_section.heading'));
        $response->assertSee(__('promoter_managers.dashboard.debts_section.overview_subs_owe'));
        $response->assertSee(__('promoter_managers.dashboard.debts_section.overview_orgs_owe'));

        // Evidence ledgers.
        $response->assertSee(__('promoter_managers.dashboard.evidence.heading'));
        $response->assertSee(__('promoter_managers.dashboard.evidence.from_subs_heading'));
        $response->assertSee(__('promoter_managers.dashboard.evidence.to_orgs_heading'));
    }

    public function test_dashboard_handles_zero_state_without_crashing(): void
    {
        $manager = User::create([
            'name'     => 'Lonely Manager',
            'email'    => 'lonely@example.com',
            'password' => Hash::make('secret123'),
            'role'     => 'promoter_manager',
        ]);

        $response = $this->actingAs($manager)->get(route('promoter_manager.dashboard'));
        $response->assertOk();

        // Even with no subs and no orders the page renders and shows
        // the up-to-date labels.
        $response->assertSee(__('promoter_managers.dashboard.kpi.my_earnings.label'));
        $response->assertSee(__('promoter_managers.dashboard.kpi.debt_to_organizers.zero_label'));
    }

    public function test_anchor_sections_exist_for_earnings_and_debts(): void
    {
        $manager = User::create([
            'name'     => 'Manager Anchor',
            'email'    => 'anchor@example.com',
            'password' => Hash::make('secret123'),
            'role'     => 'promoter_manager',
        ]);

        $response = $this->actingAs($manager)->get(route('promoter_manager.dashboard'));
        $response->assertOk();

        // The earnings and debts sections must be anchorable so the
        // top-of-page KPI cards can smooth-scroll to them.
        $response->assertSee('id="earnings"', false);
        $response->assertSee('id="debts"', false);
    }
}
