@props([
    'status' => null,
    'label' => null,    // override label; defaults to ucfirst($status)
    'clickable' => false,
    'size' => 'md',
])

@php
    use App\Support\Status;
    $variant = Status::variant($status);
    $classes = Status::classes($status);
    if ($clickable && $variant === 'danger') {
        $classes .= ' hover:bg-rose-100 dark:hover:bg-rose-500/20 cursor-pointer';
    }
    $displayLabel = $label ?? ucfirst((string) $status);
    $sizeClass = $size === 'sm' ? 'px-2 py-0.5 text-[11px]' : 'px-2.5 py-0.5 text-xs';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 font-medium rounded-full ring-1 ring-inset lowercase '.$classes.' '.$sizeClass]) }}>
    {{ $displayLabel }}
    @if($clickable && $variant === 'danger')
        <x-ui.icon name="chevron-down" class="h-3 w-3" />
    @endif
</span>
