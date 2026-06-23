@props([
    'hover' => true,
])

@php
    $divider = 'divide-y divide-zinc-200 dark:divide-zinc-700';
@endphp

<tbody {{ $attributes->merge(['class' => $divider.' bg-white dark:bg-zinc-900/40']) }}>
    {{ $slot }}
</tbody>