<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Persisted mail configuration.
 *
 * Laravel's `config('mail.*')` is normally populated from .env at boot
 * time and frozen by `php artisan config:cache`. The admin UI needs to be
 * able to override these values without touching .env on the server, so
 * we store the overrides in a single-row table and re-apply them at
 * runtime via App\Providers\MailConfigServiceProvider.
 *
 * Schema mirrors the keys in config/mail.php so the controller can map
 * them 1:1. Defaults in PHP code fall back to whatever .env provides
 * when a column is NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mail_settings', function (Blueprint $table) {
            $table->id();

            // Which mailer to use: smtp, sendmail, log, array, ...
            $table->string('mailer', 32)->nullable();

            // SMTP-only fields.
            $table->string('host')->nullable();
            $table->unsignedInteger('port')->nullable();
            $table->string('username')->nullable();
            // Encrypted at rest by MailSetting model via Crypt.
            $table->text('password_encrypted')->nullable();
            // tls | ssl | null
            $table->string('encryption', 8)->nullable();
            $table->unsignedInteger('timeout')->nullable();

            // Global From envelope.
            $table->string('from_address')->nullable();
            $table->string('from_name')->nullable();

            // Where test emails get sent when the admin clicks "Send test".
            $table->string('test_recipient')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_settings');
    }
};
