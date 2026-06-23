<?php

namespace Tests\Feature;

use App\Models\TicketOrder;
use App\Models\TicketOrderItem;
use App\Models\TicketType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression tests for the "supremeadmin private sales" feature.
 *
 *   - Orders placed by a user with role `supreme` (or `superadmin`) are
 *     marked `is_private = true`,
 *   - private orders are excluded from every dashboard / stats query
 *     (via the `publicOnly()` scope on TicketOrder),
 *   - private orders are not visible in the admin / promoter / promoter-
 *     manager listings — only the seller themselves sees them on their
 *     promoter-facing pages.
 */
class PrivateOrdersTest extends TestCase
{
    /** @var User */
    protected $supreme;

    /** @var User */
    protected $customer;

    /** @var TicketOrder */
    protected $privateOrder;

    protected function setUp(): void
    {
        parent::setUp();

        // Set up a tiny schema in memory because the project's phpunit.xml
        // runs against :memory: SQLite without migrations. We only need the
        // columns touched by the scope, the cast and the OrderController.
        if (!Schema::hasTable('ticket_orders')) {
            Schema::create('ticket_orders', function ($t) {
                $t->id();
                $t->string('order_number')->nullable();
                $t->unsignedBigInteger('ordered_by')->nullable();
                $t->unsignedBigInteger('requested_by')->nullable();
                $t->string('email')->nullable();
                $t->string('job_status')->nullable();
                $t->string('job_failure_reason')->nullable();
                $t->decimal('paid', 10, 2)->default(0);
                $t->decimal('total', 10, 2)->default(0);
                $t->boolean('is_private')->default(false);
                $t->timestamps();
            });
        }

        // We also need a users table because the User model relations
        // will touch it on insert/refresh.
        if (!Schema::hasTable('users')) {
            Schema::create('users', function ($t) {
                $t->id();
                $t->string('name');
                $t->string('email')->unique();
                $t->string('password')->nullable();
                $t->string('role')->default('buyer');
                $t->decimal('paid', 10, 2)->default(0);
                $t->unsignedBigInteger('parent_id')->nullable();
                $t->rememberToken();
                $t->timestamps();
            });
        }

        $this->supreme = User::create([
            'name'     => 'Test Supreme',
            'email'    => 'supreme_private_test@example.com',
            'password' => bcrypt('secret'),
            'role'     => 'supreme',
        ]);

        $this->customer = User::create([
            'name'     => 'Customer',
            'email'    => 'cust_private_test@example.com',
            'password' => bcrypt('secret'),
            'role'     => 'buyer',
        ]);

        $this->privateOrder = TicketOrder::create([
            'order_number' => 'PRIV-1',
            'ordered_by'   => $this->customer->id,
            'requested_by' => $this->supreme->id,
            'email'        => $this->customer->email,
            'job_status'   => 'completed',
            'paid'         => 250.00,
            'total'        => 250.00,
            'is_private'   => true,
        ]);
    }

    protected function tearDown(): void
    {
        $this->privateOrder?->delete();
        $this->customer?->delete();
        $this->supreme?->delete();
        parent::tearDown();
    }

    public function test_supreme_user_is_recognised_by_helper(): void
    {
        $this->assertTrue($this->supreme->isSupreme());
    }

    public function test_superadmin_user_is_also_supreme_for_sales_visibility(): void
    {
        $superadmin = User::create([
            'name'     => 'Test Superadmin',
            'email'    => 'supadm_private_test@example.com',
            'password' => bcrypt('secret'),
            'role'     => 'superadmin',
        ]);

        try {
            $this->assertTrue($superadmin->isSupreme());
        } finally {
            $superadmin->delete();
        }
    }

    public function test_regular_admin_is_not_supreme(): void
    {
        $admin = User::create([
            'name'     => 'Regular Admin',
            'email'    => 'admin_private_test@example.com',
            'password' => bcrypt('secret'),
            'role'     => 'admin',
        ]);
        try {
            $this->assertFalse($admin->isSupreme());
        } finally {
            $admin->delete();
        }
    }

    public function test_public_only_scope_excludes_private_orders(): void
    {
        // Add a public order to make sure we are not just seeing an empty
        // table.
        TicketOrder::create([
            'order_number' => 'PUB-1',
            'ordered_by'   => $this->customer->id,
            'requested_by' => $this->supreme->id,
            'email'        => $this->customer->email,
            'job_status'   => 'completed',
            'paid'         => 100.00,
            'total'        => 100.00,
            'is_private'   => false,
        ]);

        $public  = TicketOrder::publicOnly()->pluck('order_number')->all();
        $private = TicketOrder::privateOnly()->pluck('order_number')->all();
        $all     = TicketOrder::pluck('order_number')->all();

        $this->assertContains('PUB-1', $public);
        $this->assertNotContains('PRIV-1', $public);
        $this->assertContains('PRIV-1', $private);
        $this->assertNotContains('PUB-1', $private);
        $this->assertCount(2, $all);
    }

    public function test_revenue_sum_with_public_only_excludes_private(): void
    {
        TicketOrder::create([
            'order_number' => 'PUB-2',
            'ordered_by'   => $this->customer->id,
            'requested_by' => $this->supreme->id,
            'email'        => $this->customer->email,
            'job_status'   => 'completed',
            'paid'         => 100.00,
            'total'        => 100.00,
            'is_private'   => false,
        ]);

        $totalAll     = (float) TicketOrder::where('job_status', 'completed')->sum('total');
        $totalPublic  = (float) TicketOrder::where('job_status', 'completed')->publicOnly()->sum('total');
        $totalPrivate = (float) TicketOrder::where('job_status', 'completed')->privateOnly()->sum('total');

        $this->assertEquals(350.0, $totalAll, 'Total includes both public and private');
        $this->assertEquals(100.0, $totalPublic, 'Public only excludes the private order');
        $this->assertEquals(250.0, $totalPrivate);
    }
}