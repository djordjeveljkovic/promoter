<section class="mt-10 space-y-6"
         x-data="{ open: false }"
         x-init="open = false; if ($wire && typeof $wire.on === 'function') { $wire.on('close-delete-account-modal', () => { open = false }) }">
    <div class="relative mb-5">
        <h3 class="text-base font-semibold text-zinc-900 dark:text-white">{{ __('profile.delete_account_section_heading') }}</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('profile.delete_account_section_subheading') }}</p>
    </div>

    <x-ui.button variant="danger" @click="open = true">
        {{ __('profile.delete_account_button_open_modal') }}
    </x-ui.button>

    {{-- Use <template x-if> instead of x-show so the modal is fully removed from
         the DOM when closed. This guarantees it can never "leak" open via
         wire:navigate morphdom or any stale Alpine state. --}}
    <template x-if="open">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-zinc-900/50 px-4"
             @keydown.escape.window="open = false">
            <div class="relative w-full max-w-lg overflow-hidden rounded-xl bg-white shadow-xl ring-1 ring-zinc-200 dark:bg-zinc-900 dark:ring-zinc-800"
                 @click.outside="open = false">
                <button type="button"
                        @click="open = false"
                        wire:click="closeModal"
                        aria-label="{{ __('profile.delete_account_cancel_button') }}"
                        class="absolute right-3 top-3 rounded p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-200 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <x-ui.icon name="x-mark" class="h-5 w-5" />
                </button>

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
                        <x-ui.button variant="secondary" type="button" @click="open = false" wire:click="closeModal">
                            {{ __('profile.delete_account_cancel_button') }}
                        </x-ui.button>
                        <x-ui.button variant="danger" type="submit">
                            {{ __('profile.delete_account_confirm_button') }}
                        </x-ui.button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</section>
</content>
</invoke>