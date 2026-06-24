<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <div x-data="{ theme: localStorage.getItem('appearance') || 'system' }"
             class="inline-flex rounded-lg bg-zinc-100 p-1 dark:bg-zinc-800">
            <button type="button"
                    @click="theme = 'light'; localStorage.setItem('appearance','light'); document.documentElement.classList.remove('dark')"
                    :class="theme === 'light' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition">
                <x-ui.icon name="arrow-trending-up" class="h-4 w-4" />
                {{ __('Light') }}
            </button>
            <button type="button"
                    @click="theme = 'dark'; localStorage.setItem('appearance','dark'); document.documentElement.classList.add('dark')"
                    :class="theme === 'dark' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition">
                <x-ui.icon name="cog" class="h-4 w-4" />
                {{ __('Dark') }}
            </button>
            <button type="button"
                    @click="theme = 'system'; localStorage.removeItem('appearance'); document.documentElement.classList.toggle('dark', window.matchMedia('(prefers-color-scheme: dark)').matches)"
                    :class="theme === 'system' ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-100' : 'text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200'"
                    class="inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition">
                <x-ui.icon name="home" class="h-4 w-4" />
                {{ __('System') }}
            </button>
        </div>
    </x-settings.layout>
</section>
</content>
</invoke>