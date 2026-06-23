@props([
    'hover' => true,
])

@php
    $hoverClass = $hover ? 'hover:bg-indigo-50/60 dark:hover:bg-indigo-500/10 transition-colors duration-100' : '';
@endphp

<tr {{ $attributes->merge(['class' => $hoverClass]) }}>
    {{ $slot }}
</tr>