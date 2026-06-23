<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Soft-delete (`is_active`) toggle for ticket types.
     *
     * Hard delete is not viable because `tickets`, `ticket_order_items` and
     * `ticket_commissions` all reference `ticket_types.id` with a RESTRICT
     * foreign key, so any ticket type that has ever been sold cannot be
     * removed without nuking historical sales data. Instead we mark the
     * ticket type as inactive so it disappears from the order creation flow
     * but its existing rows (and the FK relationships) stay intact.
     */
    public function up(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            // Default `true` so existing rows keep behaving as before.
            // Placed after `qr_coordinates` to avoid touching indexed columns.
            $table->boolean('is_active')->default(true)->after('qr_coordinates');

            // Helpful when filtering the admin list / order form by state.
            $table->index('is_active', 'idx_ticket_types_is_active');
        });
    }

    public function down(): void
    {
        Schema::table('ticket_types', function (Blueprint $table) {
            $table->dropIndex('idx_ticket_types_is_active');
            $table->dropColumn('is_active');
        });
    }
};