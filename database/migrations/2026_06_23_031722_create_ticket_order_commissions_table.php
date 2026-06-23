<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Per-beneficiary commission record for each ticket_order_item.
     * When an order is placed by a promoter / promoter_manager / sub_promoter,
     * we create one row per beneficiary that earns a share of the item's
     * commission. For simple promoter / promoter_manager orders this is a
     * single row. For sub-promoter orders it's typically two rows
     * (sub-promoter share + manager share).
     */
    public function up(): void
    {
        Schema::create('ticket_order_commissions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ticket_order_id')
                  ->constrained('ticket_orders')
                  ->cascadeOnDelete();
            $table->foreignId('ticket_order_item_id')
                  ->constrained('ticket_order_items')
                  ->cascadeOnDelete();

            $table->foreignId('beneficiary_user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Role of the beneficiary at the time the order was placed - kept
            // here for fast reporting even if the user's role is changed later.
            $table->enum('beneficiary_role', [
                'promoter',
                'promoter_manager',
                'sub_promoter',
            ]);

            // Number of tickets this commission record covers (mirrors the
            // parent item quantity, but is stored per row for clarity when
            // partial rows are present).
            $table->unsignedInteger('quantity');

            // Total commission amount earned by this beneficiary from this item.
            $table->decimal('commission_amount', 12, 2)->default(0);

            $table->timestamps();

            $table->index(['beneficiary_user_id']);
            $table->index(['ticket_order_id']);
            $table->index(['ticket_order_item_id', 'beneficiary_user_id'], 'idx_toc_item_beneficiary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticket_order_commissions');
    }
};
