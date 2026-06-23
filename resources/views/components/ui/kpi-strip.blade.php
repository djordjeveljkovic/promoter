@props([
    'cols' => 4, // 2 | 3 | 4 | 5
])

@php
    $colsClass = match ((int) $cols) {
        2 => 'sm:grid-cols-2',
        3 => 'sm:grid-cols-2 lg:grid-cols-3',
        5 => 'sm:grid-cols-2 lg:grid-cols-5',
        default => 'sm:grid-cols-2 lg:grid-cols-4',
    };
@endphp

<div {{ $attributes->merge(['class' => 'grid grid-cols-1 gap-px overflow-hidden rounded-xl bg-zinc-200 ring-1 ring-zinc-200 dark:bg-zinc-800 dark:ring-zinc-800 '.$colsClass]) }}>
    {{ $slot }}
</div>
