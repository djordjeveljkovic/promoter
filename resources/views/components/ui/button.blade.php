@props([
    'variant' => 'primary',   // primary | secondary | danger | success | warning | ghost | link
    'size'    => 'md',        // sm | md | lg
    'href'    => null,        // when set, renders an <a>
    'type'    => 'button',
    'icon'    => null,        // leading icon name
    'iconTrailing' => null,
    'loading' => null,        // wire:target for loading state
    'disabled'=> false,
    'fullWidth' => false,
])

@php
    $base = 'inline-flex items-center justify-center gap-2 font-semibold rounded-lg shadow-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap';

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-5 py-2.5 text-base',
    ];

    $variants = [
        'primary'   => 'bg-indigo-600 text-white hover:bg-indigo-700 focus-visible:ring-indigo-500',
        'secondary' => 'bg-white text-zinc-700 ring-1 ring-inset ring-zinc-300 hover:bg-zinc-50 focus-visible:ring-indigo-500 dark:bg-zinc-900 dark:text-zinc-200 dark:ring-zinc-700 dark:hover:bg-zinc-800',
        'danger'    => 'bg-rose-600 text-white hover:bg-rose-700 focus-visible:ring-rose-500',
        'success'   => 'bg-emerald-600 text-white hover:bg-emerald-700 focus-visible:ring-emerald-500',
        'warning'   => 'bg-amber-500 text-white hover:bg-amber-600 focus-visible:ring-amber-500',
        'ghost'     => 'bg-transparent text-zinc-700 hover:bg-zinc-100 focus-visible:ring-zinc-500 shadow-none dark:text-zinc-200 dark:hover:bg-zinc-800',
        'link'      => 'bg-transparent text-indigo-600 hover:text-indigo-700 shadow-none focus-visible:ring-indigo-500 px-0 py-0 dark:text-indigo-400 dark:hover:text-indigo-300',
    ];

    $iconSizes = [
        'sm' => 'h-3.5 w-3.5',
        'md' => 'h-4 w-4',
        'lg' => 'h-5 w-5',
    ];

    $classes = trim($base.' '.$sizes[$size].' '.$variants[$variant]);
    if ($fullWidth) $classes .= ' w-full';

    $iconClass = $iconSizes[$size];
    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if($tag === 'a')
        href="{{ $href }}"
        wire:navigate
    @else
        type="{{ $type }}"
    @endif
    {{ $attributes->except(['href','type','class'])->merge(['class' => $classes]) }}
    @if($disabled) disabled aria-disabled="true" @endif
    @if($loading)
        wire:loading.attr="disabled"
        wire:target="{{ $loading }}"
    @endif
>
    @if($icon)
        <x-ui.icon :name="$icon" class="{{ $iconClass }}" />
    @endif

    {{ $slot }}

    @if($iconTrailing)
        <x-ui.icon :name="$iconTrailing" class="{{ $iconClass }}" />
    @endif

    @if($loading)
        <svg wire:loading wire:target="{{ $loading }}"
             class="animate-spin {{ $iconClass }}"
             xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
    @endif
</{{ $tag }}>
