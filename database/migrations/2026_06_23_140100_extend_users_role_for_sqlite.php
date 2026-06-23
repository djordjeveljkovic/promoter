<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * SQLite-only follow-up to the MySQL/pgsql migration that added the
     * 'promoter_manager' role to the users table.
     *
     * The original migration cannot rewrite the CHECK constraint on
     * SQLite in place (SQLite stores the enum values as a CHECK, not a
     * native enum type). Without this migration the in-memory test
     * database refuses to insert promoter_manager / sub_promoter users,
     * making the relevant feature tests fail with:
     *   "CHECK constraint failed: role"
     *
     * The migration is a no-op on MySQL and pgsql, where the role was
     * already extended by the original migration. Production MySQL
     * databases are unaffected.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            return;
        }

        // Rebuild the users table with the extended role set so the CHECK
        // constraint includes 'promoter_manager'.
        DB::beginTransaction();
        try {
            DB::statement('PRAGMA foreign_keys = OFF');

            DB::statement(<<<'SQL'
                CREATE TABLE users_new (
                    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    email_verified_at TIMESTAMP NULL,
                    password VARCHAR(255) NOT NULL,
                    remember_token VARCHAR(100) NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    role VARCHAR(32) NOT NULL
                        CHECK (role IN ('supreme','admin','promoter','promoter_manager','sub_promoter','buyer')),
                    paid DECIMAL(12,2) NULL DEFAULT 0,
                    parent_id INTEGER NULL
                )
            SQL);

            DB::statement('INSERT INTO users_new (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role, paid, parent_id) SELECT id, name, email, email_verified_at, password, remember_token, created_at, updated_at, role, paid, parent_id FROM users');
            DB::statement('DROP TABLE users');
            DB::statement('ALTER TABLE users_new RENAME TO users');
            // Recreate the unique index on email.
            DB::statement('CREATE UNIQUE INDEX users_email_unique ON users(email)');
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
            DB::commit();
        }
    }

    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'sqlite') {
            return;
        }

        DB::beginTransaction();
        try {
            DB::statement('PRAGMA foreign_keys = OFF');
            DB::statement(<<<'SQL'
                CREATE TABLE users_old (
                    id INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL,
                    email_verified_at TIMESTAMP NULL,
                    password VARCHAR(255) NOT NULL,
                    remember_token VARCHAR(100) NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL,
                    role VARCHAR(32) NOT NULL
                        CHECK (role IN ('supreme','admin','promoter','sub_promoter','buyer')),
                    paid DECIMAL(12,2) NULL DEFAULT 0,
                    parent_id INTEGER NULL
                )
            SQL);
            DB::statement('INSERT INTO users_old SELECT * FROM users');
            DB::statement('DROP TABLE users');
            DB::statement('ALTER TABLE users_old RENAME TO users');
            DB::statement('CREATE UNIQUE INDEX users_email_unique ON users(email)');
        } finally {
            DB::statement('PRAGMA foreign_keys = ON');
            DB::commit();
        }
    }
};
