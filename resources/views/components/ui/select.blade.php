@props([
    'size' => 'md',          // sm | md | lg
    'error' => null,
    'options' => null,        // [value => label] array OR null when using slot
    'placeholder' => null,
    'disabled' => false,
])

@php
    $base = 'block w-full appearance-none rounded-lg border bg-white text-zinc-900 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 disabled:cursor-not-allowed disabled:bg-zinc-50 disabled:text-zinc-500 dark:bg-zinc-900 dark:text-zinc-100 dark:disabled:bg-zinc-800/50';
    $sizes = [
        'sm' => 'pl-2.5 pr-8 py-1.5 text-xs',
        'md' => 'pl-3 pr-9 py-2 text-sm',
        'lg' => 'pl-4 pr-10 py-2.5 text-base',
    ];
    $stateClasses = $error
        ? ' border-rose-300 focus:border-rose-500 focus:ring-rose-500 dark:border-rose-500/50 dark:focus:border-rose-400 dark:focus:ring-rose-400'
        : ' border-zinc-300 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:focus:border-indigo-400 dark:focus:ring-indigo-400';

    // Chevron arrow as inline SVG data URI so the select always looks the same
    $arrowSvg = "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%2371717a' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3E%3C/svg%3E";
@endphp

<div class="relative">
    <select {{ $attributes->except(['class'])->merge(['class' => trim($base.' '.$sizes[$size].$stateClasses)]) }}
            @if($disabled) disabled @endif
            style="background-image: url('{{ $arrowSvg }}'); background-repeat: no-repeat; background-position: right 0.625rem center; background-size: 1.25em 1.25em;">
        @if($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @if($options)
            @foreach($options as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </select>
</div>
