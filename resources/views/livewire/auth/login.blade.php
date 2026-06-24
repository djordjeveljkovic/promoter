<div class="flex flex-col gap-6">
    <x-auth-header :title="__('login.title')" :description="__('login.description')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="login" class="flex flex-col gap-6">
        <x-ui.field :label="__('login.email_label')" for="email" required>
            <x-ui.input id="email" wire:model="email" type="email" required autofocus autocomplete="email" placeholder="email@example.com" />
        </x-ui.field>

        <div class="relative">
            <x-ui.field :label="__('login.password_label')" for="password" required>
                <x-ui.input id="password" wire:model="password" type="password" required autocomplete="current-password" :placeholder="__('login.password_placeholder')" />
            </x-ui.field>

            @if (Route::has('password.request'))
                <x-ui.link class="absolute end-0 top-0 text-sm" variant="primary" :href="route('password.request')">
                    {{ __('login.forgot_password_link') }}
                </x-ui.link>
            @endif
        </div>

        <x-ui.checkbox wire:model="remember" :label="__('login.remember_me_label')" />

        <div class="flex items-center justify-end">
            <x-ui.button variant="primary" type="submit" class="w-full">
                {{ __('login.submit_button') }}
            </x-ui.button>
        </div>
    </form>
</div>
</content>
</invoke>