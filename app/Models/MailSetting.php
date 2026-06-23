<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

/**
 * Persisted mail configuration. The table is a singleton — there should
 * only ever be one row. We expose convenience accessors that decrypt the
 * password on demand and a `settings()` helper that returns a plain array
 * suitable for `config()->set(...)`.
 *
 * NULL values intentionally fall back to whatever the .env (or compiled
 * config cache) supplies, so the migration is safe to run on existing
 * installations: nothing changes until the admin saves new values from
 * the UI.
 */
class MailSetting extends Model
{
    protected $table = 'mail_settings';

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'username',
        'password_encrypted',
        'encryption',
        'timeout',
        'from_address',
        'from_name',
        'test_recipient',
    ];

    protected $casts = [
        'port'    => 'integer',
        'timeout' => 'integer',
    ];

    /**
     * Get the (cached) singleton row, creating it on first use so the
     * controller form always has a target to update.
     */
    public static function current(): self
    {
        /** @var self|null $row */
        $row = static::query()->first();
        if (!$row) {
            $row = static::query()->create([]);
        }
        return $row;
    }

    /**
     * Encrypted password accessor. Returns null when nothing is stored.
     */
    public function getPasswordAttribute(): ?string
    {
        $cipher = (string) $this->password_encrypted;
        if ($cipher === '') {
            return null;
        }
        try {
            return Crypt::decryptString($cipher);
        } catch (\Throwable $e) {
            // Stored value was not encrypted (older row, or plain text)
            // — return as-is so the admin still sees what is on file.
            return $cipher;
        }
    }

    /**
     * Mutator for the password. Always encrypted at rest.
     */
    public function setPasswordAttribute(?string $value): void
    {
        if ($value === null || $value === '') {
            $this->attributes['password_encrypted'] = null;
            return;
        }
        $this->attributes['password_encrypted'] = Crypt::encryptString($value);
    }

    /**
     * Flat array representation suitable for `config()->set('mail.*', ...)`.
     * Only includes keys that have a non-null value so we don't clobber
     * .env defaults for fields the admin hasn't touched.
     *
     * @return array<string, mixed>
     */
    public function toConfigArray(): array
    {
        $cfg = [];

        if (!empty($this->mailer)) {
            $cfg['default'] = $this->mailer;
        }

        // For the smtp mailer (and any other mailer that supports these
        // keys), override the per-mailer settings.
        $smtp = array_filter([
            'host'     => $this->host,
            'port'     => $this->port,
            'username' => $this->username,
            'password' => $this->getPasswordAttribute(),
            'scheme'   => $this->encryption,
            'timeout'  => $this->timeout,
        ], fn ($v) => $v !== null && $v !== '');

        if (!empty($smtp)) {
            $cfg['mailers_smtp'] = array_merge(
                (array) config('mail.mailers.smtp', []),
                $smtp,
            );
        }

        $from = array_filter([
            'address' => $this->from_address,
            'name'    => $this->from_name,
        ], fn ($v) => !empty($v));

        if (!empty($from)) {
            $cfg['from'] = array_merge(
                (array) config('mail.from', []),
                $from,
            );
        }

        return $cfg;
    }

    /**
     * Apply this row's overrides to the global mail config so the next
     * `Mail::send()` (or whatever the framework does) uses the DB values.
     *
     * Special handling: `mailers_smtp` is a virtual key that means "merge
     * these values into the existing mail.mailers.smtp array". This way
     * we don't blow away the other transports (log, ses, sendmail, ...)
     * that the original config/mail.php defines.
     */
    public function applyToConfig(): void
    {
        foreach ($this->toConfigArray() as $key => $value) {
            if ($key === 'mailers_smtp') {
                $existing = (array) config('mail.mailers.smtp', []);
                config()->set('mail.mailers.smtp', array_merge($existing, $value));
                continue;
            }
            config()->set('mail.' . $key, $value);
        }
    }
}
