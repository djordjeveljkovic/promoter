<x-layouts.app.sidebar :title="$title ?? null">
    <div class="[grid-area:main] p-6 lg:p-8 [[data-flux-container]_&]:px-0" data-flux-main>
        <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

            <x-flash-messages/>

            <div class="max-w-full rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900/60 p-6">
                {{ $slot }}
            </div>
        </div>
    </div>

</x-layouts.app.sidebar>
</content>
</invoke>