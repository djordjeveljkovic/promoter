@props([
    'label' => null,
    'for' => null,
    'error' => null,
    'hint' => null,
    'required' => false,
    'inline' => false,
])

@php
    $hasError = (bool) $error;
@endphp

<div @class([
    'flex',
    'flex-col' => ! $inline,
    'sm:flex-row sm:items-center sm:gap-4' => $inline,
    'gap-1.5' => ! $inline,
])>
    @if($label)
        <label for="{{ $for }}"
               class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 sm:flex-shrink-0">
            {{ $label }}
            @if($required)<span class="text-rose-500" aria-hidden="true">*</span>@endif
        </label>
    @endif

    <div class="flex-1 min-w-0 space-y-1">
        {{ $slot }}

        @if($hint && ! $hasError)
            <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ $hint }}</p>
        @endif

        @if($hasError)
            <p class="text-xs text-rose-600 dark:text-rose-400" role="alert">{{ $error }}</p>
        @endif
    </div>
</div>
