@props([
    'title' => null,
    'subtitle' => null,
])

<div class="flex flex-col gap-3 border-b border-zinc-200 px-5 py-4 dark:border-zinc-800 sm:flex-row sm:items-center sm:justify-between">
    <div class="min-w-0">
        @if($title)
            <h3 class="truncate text-base font-semibold text-zinc-900 dark:text-zinc-100">{{ $title }}</h3>
        @endif
        @if($subtitle)
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
