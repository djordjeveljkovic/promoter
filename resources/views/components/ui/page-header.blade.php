@props([
    'title' => null,
    'subtitle' => null,
    'eyebrow' => null,
])

<header class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div class="min-w-0">
        @if($eyebrow)
            <p class="text-xs font-medium uppercase tracking-wider text-indigo-600 dark:text-indigo-400">
                {{ $eyebrow }}
            </p>
        @endif
        @if($title)
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50 sm:text-3xl">
                {{ $title }}
            </h1>
        @endif
        @if($subtitle)
            <p class="mt-1 max-w-2xl text-sm text-zinc-500 dark:text-zinc-400">
                {{ $subtitle }}
            </p>
        @endif

        {{ $slot }}
    </div>

    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</header>
