@props([
    'hover' => true,
    'striped' => false,
])

@php
    $baseTable = 'min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700';
    if ($striped) $baseTable .= ' [&_tbody_tr:nth-child(even)]:bg-zinc-50/70 dark:[&_tbody_tr:nth-child(even)]:bg-zinc-800/30';
@endphp

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => $baseTable]) }}>
        {{ $slot }}
    </table>
</div>