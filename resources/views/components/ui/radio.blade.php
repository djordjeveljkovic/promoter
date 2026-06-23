@props([
    'label' => null,
    'value' => null,
])

<label class="inline-flex items-start gap-2 cursor-pointer">
    <input type="radio"
           value="{{ $value }}"
           {{ $attributes->except(['class','value'])->merge(['class' => 'mt-0.5 h-4 w-4 border-zinc-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-offset-0 dark:border-zinc-600 dark:bg-zinc-900 dark:focus:ring-indigo-400 dark:focus:ring-offset-zinc-900']) }} />
    @if($label)
        <span class="text-sm text-zinc-700 dark:text-zinc-300 select-none">
            {{ $label }}
        </span>
    @endif
</label>
