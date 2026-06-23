@props([
    'variant' => 'primary', // primary | secondary | danger | muted | success | warning
    'size'    => 'md',      // sm | md | lg
    'href'    => '#',
    'icon'    => null,
    'iconTrailing' => null,
    'wire'    => null,      // wire:navigate by default; pass false to disable
])

@php
    $base = 'inline-flex items-center justify-center gap-1.5 font-medium rounded-md transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 whitespace-nowrap';

    $sizes = [
        'sm' => 'px-2 py-1 text-xs',
        'md' => 'px-2.5 py-1.5 text-sm',
        'lg' => 'px-3 py-2 text-sm',
    ];

    $variants = [
        'primary'   => 'text-indigo-600 hover:text-indigo-700 hover:bg-indigo-50 focus-visible:ring-indigo-500 dark:text-indigo-400 dark:hover:text-indigo-300 dark:hover:bg-indigo-500/10',
        'secondary' => 'text-zinc-700 hover:text-zinc-900 hover:bg-zinc-100 focus-visible:ring-zinc-500 dark:text-zinc-300 dark:hover:text-white dark:hover:bg-zinc-800',
        'danger'    => 'text-rose-600 hover:text-rose-700 hover:bg-rose-50 focus-visible:ring-rose-500 dark:text-rose-400 dark:hover:text-rose-300 dark:hover:bg-rose-500/10',
        'success'   => 'text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50 focus-visible:ring-emerald-500 dark:text-emerald-400 dark:hover:text-emerald-300 dark:hover:bg-emerald-500/10',
        'warning'   => 'text-amber-600 hover:text-amber-700 hover:bg-amber-50 focus-visible:ring-amber-500 dark:text-amber-400 dark:hover:text-amber-300 dark:hover:bg-amber-500/10',
        'muted'     => 'text-zinc-500 hover:text-zinc-700 focus-visible:ring-zinc-500 dark:text-zinc-400 dark:hover:text-zinc-200',
    ];

    $iconSizes = [
        'sm' => 'h-3.5 w-3.5',
        'md' => 'h-4 w-4',
        'lg' => 'h-5 w-5',
    ];

    $classes = trim($base.' '.$sizes[$size].' '.$variants[$variant]);
    $iconClass = $iconSizes[$size];
@endphp

<a href="{{ $href }}"
   @if($wire !== false) wire:navigate @endif
   {{ $attributes->except(['href','class','wire'])->merge(['class' => $classes]) }}>
    @if($icon)
        <x-ui.icon :name="$icon" class="{{ $iconClass }}" />
    @endif
    {{ $slot }}
    @if($iconTrailing)
        <x-ui.icon :name="$iconTrailing" class="{{ $iconClass }}" />
    @endif
</a>
