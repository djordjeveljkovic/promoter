@props([
    'variant' => 'info',  // success | danger | warning | info | neutral
    'position' => 'inline', // inline | toast
    'dismissable' => true,
    'title' => null,
])

@php
    $variants = [
        'success' => [
            'container' => 'bg-emerald-50 text-emerald-800 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-200 dark:ring-emerald-500/30',
            'icon'      => 'text-emerald-500 dark:text-emerald-400',
        ],
        'danger'  => [
            'container' => 'bg-rose-50 text-rose-800 ring-rose-200 dark:bg-rose-500/10 dark:text-rose-200 dark:ring-rose-500/30',
            'icon'      => 'text-rose-500 dark:text-rose-400',
        ],
        'warning' => [
            'container' => 'bg-amber-50 text-amber-800 ring-amber-200 dark:bg-amber-500/10 dark:text-amber-200 dark:ring-amber-500/30',
            'icon'      => 'text-amber-500 dark:text-amber-400',
        ],
        'info'    => [
            'container' => 'bg-sky-50 text-sky-800 ring-sky-200 dark:bg-sky-500/10 dark:text-sky-200 dark:ring-sky-500/30',
            'icon'      => 'text-sky-500 dark:text-sky-400',
        ],
        'neutral' => [
            'container' => 'bg-zinc-50 text-zinc-800 ring-zinc-200 dark:bg-zinc-500/10 dark:text-zinc-200 dark:ring-zinc-500/30',
            'icon'      => 'text-zinc-500 dark:text-zinc-400',
        ],
    ];

    $iconName = match($variant) {
        'success' => 'check',
        'danger'  => 'x-mark',
        'warning' => 'arrow-trending-up',
        'info'    => 'cog',
        default   => 'check',
    };

    $containerClasses = $variants[$variant]['container'];

    $positionClasses = match($position) {
        'toast' => 'fixed top-4 right-4 z-[100] w-full max-w-sm shadow-lg',
        default => '',
    };
@endphp

@if($position === 'toast')
    <div role="alert"
         x-data="{ open: true }"
         x-show="open"
         x-transition.opacity
         {{ $attributes->merge(['class' => $positionClasses.' flex items-start gap-3 rounded-xl p-4 ring-1 '.$containerClasses]) }}>
        <x-ui.icon :name="$iconName" class="h-5 w-5 mt-0.5 shrink-0 {{ $variants[$variant]['icon'] }}" />
        <div class="flex-1 min-w-0 text-sm">
            @if($title)<p class="font-semibold">{{ $title }}</p>@endif
            <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
        </div>
        @if($dismissable)
            <button type="button" @click="open = false"
                    class="shrink-0 rounded p-1 -m-1 opacity-60 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-current">
                <x-ui.icon name="x-mark" class="h-4 w-4" />
                <span class="sr-only">{{ __('Dismiss') }}</span>
            </button>
        @endif
    </div>
@else
    <div role="alert"
         {{ $attributes->merge(['class' => 'flex items-start gap-3 rounded-lg p-4 ring-1 '.$containerClasses]) }}>
        <x-ui.icon :name="$iconName" class="h-5 w-5 mt-0.5 shrink-0 {{ $variants[$variant]['icon'] }}" />
        <div class="flex-1 min-w-0 text-sm">
            @if($title)<p class="font-semibold">{{ $title }}</p>@endif
            <div class="{{ $title ? 'mt-1' : '' }}">{{ $slot }}</div>
        </div>
        @isset($actions)
            <div class="shrink-0">{{ $actions }}</div>
        @endisset
    </div>
@endif
