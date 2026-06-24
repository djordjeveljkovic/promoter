<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('profile.password_page_heading')" :subheading="__('profile.password_page_subheading')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <x-ui.field :label="__('profile.current_password_label')" for="current_password" required>
                <x-ui.input id="current_password" wire:model="current_password" type="password" required autocomplete="current-password" />
            </x-ui.field>

            <x-ui.field :label="__('profile.new_password_label')" for="password" required>
                <x-ui.input id="password" wire:model="password" type="password" required autocomplete="new-password" />
            </x-ui.field>

            <x-ui.field :label="__('profile.confirm_password_label')" for="password_confirmation" required>
                <x-ui.input id="password_confirmation" wire:model="password_confirmation" type="password" required autocomplete="new-password" />
            </x-ui.field>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-ui.button variant="primary" type="submit" class="w-full">
                        {{ __('profile.save_button') }}
                    </x-ui.button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    {{ __('profile.saved_message') }}
                </x-action-message>
            </div>
        </form>
    </x-settings.layout>
</section>
</content>
</invoke>