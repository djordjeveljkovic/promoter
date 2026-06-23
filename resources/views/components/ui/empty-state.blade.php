@props([
    'icon' => null,
    'title' => null,
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center px-6 py-12 text-center']) }}>
    @if($icon)
        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
            <x-ui.icon :name="$icon" class="h-6 w-6" />
        </span>
    @endif

    @if($title)
        <h3 class="mt-4 text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
    @endif

    @if($description)
        <p class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ $description }}</p>
    @endif

    @isset($actions)
        <div class="mt-5 flex flex-wrap items-center justify-center gap-2">{{ $actions }}</div>
    @endisset

    {{ $slot }}
</div>
