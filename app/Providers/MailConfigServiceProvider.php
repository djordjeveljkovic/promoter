<?php

namespace App\Providers;

use App\Models\MailSetting;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

/**
 * Applies DB-persisted mail overrides to the runtime config so the admin
 * UI can change SMTP credentials without touching .env.
 *
 * Loaded on every request (web + console + queue worker). Safe to run
 * before the migrations exist — the `Schema::hasTable` guard makes the
 * boot a no-op when the table hasn't been created yet, which matters
 * for `php artisan migrate` itself.
 */
class MailConfigServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            if (!Schema::hasTable('mail_settings')) {
                return;
            }
            MailSetting::current()->applyToConfig();
        } catch (\Throwable $e) {
            // Never break the boot because of a bad mail override row.
            // The default .env-based config will still be used.
            report($e);
        }
    }
}
