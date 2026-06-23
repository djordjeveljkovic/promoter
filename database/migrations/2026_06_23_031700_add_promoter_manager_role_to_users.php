<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Extends the users.role enum to add 'promoter_manager'.
     * A promoter-manager has the same commission as a regular promoter (calculated
     * from ticket_commissions tiers), but can additionally delegate part of his
     * commission to sub-promoters via promoter_commission_overrides.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('supreme','admin','promoter','promoter_manager','sub_promoter','buyer') NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('supreme','admin','promoter','promoter_manager','sub_promoter','buyer'))");
        }
        // SQLite doesn't support modifying column types/CHECK in place; the
        // application-level validation in User::$fillable + controllers handles it.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('supreme','admin','promoter','sub_promoter','buyer') NOT NULL");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('supreme','admin','promoter','sub_promoter','buyer'))");
        }
    }
};
