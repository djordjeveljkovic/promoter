<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('profile.page_heading')" :subheading="__('profile.page_subheading')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <x-ui.field :label="__('profile.name_label')" for="name" required>
                <x-ui.input id="name" wire:model="name" type="text" required autofocus autocomplete="name" />
            </x-ui.field>

            <div>
                <x-ui.field :label="__('profile.email_label')" for="email" required>
                    <x-ui.input id="email" wire:model="email" type="email" required autocomplete="email" />
                </x-ui.field>

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! auth()->user()->hasVerifiedEmail())
                    <div class="mt-4">
                        <p class="text-sm text-zinc-700 dark:text-zinc-300">
                            {{ __('profile.verification.unverified_notice') }}

                            <button type="button" class="text-sm text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300 cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('profile.verification.resend_link') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <x-ui.alert variant="success" class="mt-2">
                                {{ __('profile.verification.link_sent_message') }}
                            </x-ui.alert>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <x-ui.button variant="primary" type="submit" class="w-full">
                        {{ __('profile.save_button') }}
                    </x-ui.button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('profile.saved_message') }}
                </x-action-message>
            </div>
        </form>

        {{-- Account deletion is intentionally NOT exposed here.
             Only a supreme admin can remove accounts, via /superadmin/users. --}}
    </x-settings.layout>
</section>
</content>
</invoke>