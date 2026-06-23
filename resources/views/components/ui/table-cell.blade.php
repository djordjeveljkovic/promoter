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
        ? 'px-5 py-3 text-xs font-bold uppercase tracking-wider text-zinc-700 dark:text-zinc-200'
        : 'px-5 py-4 text-sm font-medium text-zinc-800 dark:text-zinc-100';

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