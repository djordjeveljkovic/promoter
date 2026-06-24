@props([
    'label' => null,
    'value' => null,
    'subtext' => null,
    'icon' => null,        // icon name -> renders in tinted chip
    'tone' => 'neutral',   // neutral | success | danger | warning | info | indigo
    'trend' => null,       // optional: '+12% MoM' string
    'trendUp' => null,     // bool
])

@php
    $toneClasses = [
        'neutral' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300',
        'success' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400',
        'danger'  => 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400',
        'warning' => 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-400',
        'info'    => 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400',
        'indigo'  => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-500/10 dark:text-indigo-400',
    ];
    $chipClasses = 'inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg '.$toneClasses[$tone];
@endphp

<div {{ $attributes->merge(['class' => 'flex flex-col gap-2 bg-white p-5 dark:bg-zinc-900 sm:p-6']) }}>
    <div class="flex items-start justify-between gap-3">
        @if($label)
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">
                {{ $label }}
            </p>
        @endif
        @if($icon)
            <span class="{{ $chipClasses }}">
                <x-ui.icon :name="$icon" class="h-5 w-5" />
            </span>
        @endif
    </div>

    @if($value || $slot->isNotEmpty())
        <div class="flex items-baseline gap-2">
            @if($value)
                <span class="text-lg font-bold tracking-tight tabular-nums text-zinc-900 dark:text-zinc-50 sm:text-xl">
                    {!! $value !!}
                </span>
                @if($slot->isNotEmpty())
                    {{ $slot }}
                @endif
            @else
                <span class="text-lg font-bold tracking-tight tabular-nums text-zinc-900 dark:text-zinc-50 sm:text-xl">
                    {{ $slot }}
                </span>
            @endif
        </div>
    @endif

    <div class="flex items-center justify-between gap-2">
        @if($subtext)
            <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ $subtext }}</p>
        @endif

        @if($trend)
            @php
                $trendTone = $trendUp === true
                    ? 'text-emerald-700 dark:text-emerald-400'
                    : ($trendUp === false ? 'text-rose-700 dark:text-rose-400' : 'text-zinc-500 dark:text-zinc-400');
            @endphp
            <span class="inline-flex items-center gap-1 text-xs font-medium {{ $trendTone }}">
                @if($trendUp === true)<x-ui.icon name="arrow-up" class="h-3 w-3" />
                @elseif($trendUp === false)<x-ui.icon name="arrow-down" class="h-3 w-3" />
                @endif
                {{ $trend }}
            </span>
        @endif
    </div>
</div>
