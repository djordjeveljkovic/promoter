@props([
    'label' => null,
    'error' => null,
])

<label class="inline-flex items-start gap-2 cursor-pointer">
    <input type="checkbox"
           {{ $attributes->except(['class'])->merge(['class' => 'mt-0.5 h-4 w-4 rounded border-zinc-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-600 dark:bg-zinc-900 dark:focus:ring-indigo-400 dark:focus:ring-offset-zinc-900']) }} />
    @if($label)
        <span class="text-sm text-zinc-700 dark:text-zinc-300 select-none">
            {{ $label }}
            @if($attributes->get('required'))<span class="text-rose-500">*</span>@endif
        </span>
    @endif
</label>

@if($error)
    <p class="mt-1 text-xs text-rose-600 dark:text-rose-400" role="alert">{{ $error }}</p>
@endif
