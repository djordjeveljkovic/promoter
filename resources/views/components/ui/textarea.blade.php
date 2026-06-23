@props([
    'size' => 'md', // sm | md | lg
    'error' => null,
    'rows' => 4,
])

@php
    $base = 'block w-full rounded-lg border bg-white text-zinc-900 placeholder:text-zinc-400 shadow-sm transition focus:outline-none focus:ring-2 focus:ring-offset-0 dark:bg-zinc-900 dark:text-zinc-100 dark:placeholder:text-zinc-500';

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-base',
    ];

    $stateClasses = $error
        ? ' border-rose-300 focus:border-rose-500 focus:ring-rose-500 dark:border-rose-500/50 dark:focus:border-rose-400 dark:focus:ring-rose-400'
        : ' border-zinc-300 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:focus:border-indigo-400 dark:focus:ring-indigo-400';
@endphp

<textarea rows="{{ $rows }}"
          {{ $attributes->except(['class','rows'])->merge(['class' => trim($base.' '.$sizes[$size].$stateClasses)]) }}>{{ $slot }}</textarea>
