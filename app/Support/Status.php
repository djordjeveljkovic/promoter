<?php

namespace App\Support;

/**
 * Single source of truth for job / order status visual treatment.
 *
 * Replaces the four duplicated `$jobStatusColors` arrays previously
 * defined in:
 *   - app/Http/Controllers/AdminOrderController.php
 *   - app/Http/Controllers/OrderController.php
 *   - app/Http/Controllers/SubPromoterController.php
 *   - app/Http/Controllers/OrderController1.php (dead code; see plan §3)
 *
 * Views can use <x-ui.status-pill :status="$order->job_status" />
 * and don't need any controller plumbing.
 */
class Status
{
    /** Map of raw status keys -> variant names. */
    public const VARIANTS = [
        'pending'          => 'warning',
        'processing'       => 'info',
        'failed'           => 'danger',
        'failed_clickable' => 'danger',
        'blocked'          => 'neutral',
        'completed'        => 'success',
        'sent'             => 'success',
        'unknown'          => 'neutral',
        'N/A'              => 'neutral',
    ];

    /** Tailwind class strings for each variant. Light + dark variants. */
    public const VARIANT_CLASSES = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30',
        'danger'  => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/30',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30',
        'info'    => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/30',
        'neutral' => 'bg-zinc-100 text-zinc-700 ring-zinc-500/20 dark:bg-zinc-500/10 dark:text-zinc-300 dark:ring-zinc-500/30',
        'indigo'  => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/30',
    ];

    /** Returns the variant name for a status key, defaulting to 'neutral'. */
    public static function variant(?string $status): string
    {
        $key = strtolower((string) $status);
        return self::VARIANTS[$key] ?? 'neutral';
    }

    /** Returns the full Tailwind class string for the status's variant. */
    public static function classes(?string $status): string
    {
        return self::VARIANT_CLASSES[self::variant($status)];
    }

    /** Returns every (status => variant) pair, useful for filters. */
    public static function all(): array
    {
        return self::VARIANTS;
    }
}
