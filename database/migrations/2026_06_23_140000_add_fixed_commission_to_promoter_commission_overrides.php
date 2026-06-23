<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Extends promoter_commission_overrides so that a promoter-manager can
     * choose between two ways of delegating commission to a sub-promoter
     * (per ticket type):
     *
     *  - commission_type = 'percentage' (default, legacy behaviour):
     *        the sub-promoter earns commission_percentage % of the
     *        manager's tier-based commission for every ticket sold.
     *
     *  - commission_type = 'fixed':
     *        the sub-promoter earns fixed_commission_amount RSD per ticket
     *        regardless of the manager's tier. The remaining difference
     *        between the tier-based gross commission and the fixed amount
     *        is kept by the promoter-manager.
     *
     * Existing rows are migrated with commission_type = 'percentage' so the
     * current behaviour is preserved without any data changes.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            // Add commission_type as an enum with a safe default.
            DB::statement("ALTER TABLE promoter_commission_overrides ADD COLUMN commission_type ENUM('percentage','fixed') NOT NULL DEFAULT 'percentage' AFTER commission_percentage");
            // Add fixed_commission_amount as nullable decimal (per ticket).
            DB::statement("ALTER TABLE promoter_commission_overrides ADD COLUMN fixed_commission_amount DECIMAL(10, 2) NULL AFTER commission_type");
            // Keep existing commission_percentage usable in both modes (we
            // don't drop it).
        } else {
            // SQLite / fallback: use the schema builder so local tests work.
            Schema::table('promoter_commission_overrides', function (Blueprint $table) {
                $table->string('commission_type', 16)->default('percentage')->after('commission_percentage');
                $table->decimal('fixed_commission_amount', 10, 2)->nullable()->after('commission_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE promoter_commission_overrides DROP COLUMN fixed_commission_amount");
            DB::statement("ALTER TABLE promoter_commission_overrides DROP COLUMN commission_type");
        } else {
            Schema::table('promoter_commission_overrides', function (Blueprint $table) {
                $table->dropColumn(['commission_type', 'fixed_commission_amount']);
            });
        }
    }
};
