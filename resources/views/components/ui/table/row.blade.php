@props([
    'hover' => true,
])

@php
    $hoverClass = $hover ? 'hover:bg-zinc-50/80 dark:hover:bg-zinc-900/30 transition-colors' : '';
@endphp

<tr {{ $attributes->merge(['class' => $hoverClass]) }}>
    {{ $slot }}
</tr>
