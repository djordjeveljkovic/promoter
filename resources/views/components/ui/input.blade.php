@props([
    'type' => 'text',
    'size' => 'md', // sm | md | lg
    'error' => null,
    'leadingIcon' => null,
    'trailingIcon' => null,
    'disabled' => false,
])

@php
    $base = 'block w-full rounded-lg border bg-white text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-zinc-50 disabled:text-zinc-500 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-500 dark:disabled:bg-zinc-800/50';

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-base',
    ];

    $stateClasses = $error
        ? ' border-rose-300 focus:border-rose-500 focus:ring-rose-500 dark:border-rose-500/50 dark:focus:border-rose-400 dark:focus:ring-rose-400'
        : ' border-zinc-300 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:focus:border-indigo-400 dark:focus:ring-indigo-400';

    $padding = ($leadingIcon ? ' pl-9' : '') . ($trailingIcon ? ' pr-9' : '');
@endphp

@if($leadingIcon || $trailingIcon)
    <div class="relative">
        @if($leadingIcon)
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5 text-zinc-400 dark:text-zinc-500">
                <x-ui.icon :name="$leadingIcon" class="h-4 w-4" />
            </span>
        @endif

        <input type="{{ $type }}"
               {{ $attributes->except(['class'])->merge(['class' => trim($base.' '.$sizes[$size].$stateClasses.$padding)]) }}
               @if($disabled) disabled @endif />

        @if($trailingIcon)
            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2.5 text-zinc-400 dark:text-zinc-500">
                <x-ui.icon :name="$trailingIcon" class="h-4 w-4" />
            </span>
        @endif
    </div>
@else
    <input type="{{ $type }}"
           {{ $attributes->except(['class'])->merge(['class' => trim($base.' '.$sizes[$size].$stateClasses)]) }}
           @if($disabled) disabled @endif />
@endif
