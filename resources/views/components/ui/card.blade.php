@props([
    'padding' => true,    // false for table-style cards that need no padding
    'overflow' => true,   // whether to clip overflow
])

@php
    $classes = 'rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60';
    if ($overflow) $classes .= ' overflow-hidden';
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
