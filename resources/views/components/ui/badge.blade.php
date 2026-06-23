@props([
    'variant' => 'neutral', // neutral | success | danger | warning | info | indigo
    'size' => 'md',         // sm | md | lg
    'icon' => null,
])

@php
    $base = 'inline-flex items-center gap-1 font-medium rounded-full ring-1 ring-inset whitespace-nowrap';

    $sizes = [
        'sm' => 'px-2 py-0.5 text-[11px]',
        'md' => 'px-2.5 py-0.5 text-xs',
        'lg' => 'px-3 py-1 text-sm',
    ];

    $variants = [
        'success' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30',
        'danger'  => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-500/10 dark:text-rose-400 dark:ring-rose-500/30',
        'warning' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/10 dark:text-amber-400 dark:ring-amber-500/30',
        'info'    => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-500/10 dark:text-sky-400 dark:ring-sky-500/30',
        'neutral' => 'bg-zinc-100 text-zinc-700 ring-zinc-500/20 dark:bg-zinc-500/10 dark:text-zinc-300 dark:ring-zinc-500/30',
        'indigo'  => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-500/10 dark:text-indigo-400 dark:ring-indigo-500/30',
    ];

    $iconSize = match($size) {
        'sm' => 'h-3 w-3',
        'md' => 'h-3.5 w-3.5',
        'lg' => 'h-4 w-4',
    };
@endphp

<span {{ $attributes->merge(['class' => $base.' '.$sizes[$size].' '.$variants[$variant]]) }}>
    @if($icon)
        <x-ui.icon :name="$icon" class="{{ $iconSize }}" />
    @endif
    {{ $slot }}
</span>
