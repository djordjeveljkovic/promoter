<section class="mt-10 space-y-6"
         x-data="{ open: false }">
    <div class="relative mb-5">
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('profile.delete_account_section_heading') }}</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('profile.delete_account_section_subheading') }}</p>
    </div>

    <x-ui.button variant="danger" @click="open = true">
        {{ __('profile.delete_account_button_open_modal') }}
    </x-ui.button>

    <div x-show="open"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/50 px-4"
         @keydown.escape.window="open = false">
        <div class="w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800"
             @click.outside="open = false">
            <form wire:submit="deleteUser" class="space-y-6 p-6">
                <div>
                    <h3 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ __('profile.delete_account_modal_heading') }}</h3>
                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        {{ __('profile.delete_account_modal_warning') }}
                    </p>
                </div>

                <x-ui.field :label="__('profile.delete_account_password_label')" for="delete_password" required>
                    <x-ui.input id="delete_password" wire:model="password" type="password" required />
                </x-ui.field>

                <div class="flex justify-end gap-2">
                    <x-ui.button variant="secondary" type="button" @click="open = false">
                        {{ __('profile.delete_account_cancel_button') }}
                    </x-ui.button>
                    <x-ui.button variant="danger" type="submit">
                        {{ __('profile.delete_account_confirm_button') }}
                    </x-ui.button>
                </div>
            </form>
        </div>
    </div>

    <style>[x-cloak] { display: none !important; }</style>
</section>
</content>
</invoke>