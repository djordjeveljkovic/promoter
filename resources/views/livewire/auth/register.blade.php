<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <x-ui.field :label="__('Name')" for="name" required>
            <x-ui.input id="name" wire:model="name" type="text" required autofocus autocomplete="name" :placeholder="__('Full name')" />
        </x-ui.field>

        <x-ui.field :label="__('Email address')" for="email" required>
            <x-ui.input id="email" wire:model="email" type="email" required autocomplete="email" placeholder="email@example.com" />
        </x-ui.field>

        <x-ui.field :label="__('Password')" for="password" required>
            <x-ui.input id="password" wire:model="password" type="password" required autocomplete="new-password" :placeholder="__('Password')" />
        </x-ui.field>

        <x-ui.field :label="__('Confirm password')" for="password_confirmation" required>
            <x-ui.input id="password_confirmation" wire:model="password_confirmation" type="password" required autocomplete="new-password" :placeholder="__('Confirm password')" />
        </x-ui.field>

        <div class="flex items-center justify-end">
            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ __('Create account') }}
            </x-ui.button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Already have an account?') }}
        <x-ui.link variant="primary" :href="route('login')">{{ __('Log in') }}</x-ui.link>
    </div>
</div>
</content>
</invoke>