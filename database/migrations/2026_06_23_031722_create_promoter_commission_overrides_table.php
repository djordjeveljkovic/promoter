<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores the commission portion a promoter-manager delegates to one of his
     * sub-promoters, per ticket type. The percentage represents the share of
     * the manager's tier-based commission (0-100) that the sub-promoter earns
     * for every ticket of that type sold by that sub-promoter.
     *
     * Example: manager M has tier commission 100 RSD per ticket of type T.
     *   override(sub: S, type: T, pct: 30)  => S gets 30 RSD, M gets 70 RSD.
     */
    public function up(): void
    {
        Schema::create('promoter_commission_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_manager_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('sub_promoter_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->foreignId('ticket_type_id')
                  ->constrained('ticket_types')
                  ->cascadeOnDelete();

            // 0.00 .. 100.00 - percentage of the manager's commission that the
            // sub-promoter earns. The remaining (100 - percentage) is kept by
            // the manager.
            $table->decimal('commission_percentage', 5, 2);

            $table->timestamps();

            // One override per (manager, sub-promoter, ticket_type)
            $table->unique(
                ['promoter_manager_id', 'sub_promoter_id', 'ticket_type_id'],
                'uniq_pco_mgr_sub_type'
            );

            $table->index(['promoter_manager_id', 'ticket_type_id'], 'idx_pco_mgr_type');
            $table->index(['sub_promoter_id'], 'idx_pco_sub');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoter_commission_overrides');
    }
};
