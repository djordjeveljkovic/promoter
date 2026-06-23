<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds an `is_private` flag to ticket_orders.
     *
     * When a `supreme` (or `superadmin`) user sells tickets through the
     * promoter flow, the resulting order is flagged as private. Private
     * orders are excluded from:
     *   - the admin orders list,
     *   - every dashboard / KPI calculation,
     *   - the promoter-manager overview and the supreme-admin overview,
     *   - any other aggregate statistic.
     *
     * Only the seller themselves (and only via their own promoter-facing
     * pages) can see their private orders. This is the "supremeadmin
     * private sales" feature.
     */
    public function up(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->boolean('is_private')->default(false)->after('job_failure_reason');
            $table->index('is_private');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_orders', function (Blueprint $table) {
            $table->dropIndex(['is_private']);
            $table->dropColumn('is_private');
        });
    }
};