@props([
    'header' => false,    // renders as <th> instead of <td>
    'align' => 'left',    // left | right | center
    'numeric' => false,
    'nowrap' => false,
    'colspan' => null,
    'width' => null,
])

@php
    $base = $header
        ? 'px-5 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400'
        : 'px-5 py-3.5 text-sm text-zinc-700 dark:text-zinc-300';

    $alignClass = match($align) {
        'right'  => 'text-right',
        'center' => 'text-center',
        default  => 'text-left',
    };

    if ($numeric) $base .= ' tabular-nums';
    if ($nowrap) $base .= ' whitespace-nowrap';

    $tag = $header ? 'th' : 'td';
    $scope = $header ? 'col' : null;
@endphp

<{{ $tag }}
    @if($header) scope="{{ $scope }}" @endif
    @if($colspan) colspan="{{ $colspan }}" @endif
    @if($width) style="width: {{ $width }}" @endif
    {{ $attributes->merge(['class' => $base.' '.$alignClass]) }}>
    {{ $slot }}
</{{ $tag }}>
