<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Reset password')" :description="__('Please enter your new password below')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="resetPassword" class="flex flex-col gap-6">
        <x-ui.field :label="__('Email')" for="email" required>
            <x-ui.input id="email" wire:model="email" type="email" required autocomplete="email" />
        </x-ui.field>

        <x-ui.field :label="__('Password')" for="password" required>
            <x-ui.input id="password" wire:model="password" type="password" required autocomplete="new-password" :placeholder="__('Password')" />
        </x-ui.field>

        <x-ui.field :label="__('Confirm password')" for="password_confirmation" required>
            <x-ui.input id="password_confirmation" wire:model="password_confirmation" type="password" required autocomplete="new-password" :placeholder="__('Confirm password')" />
        </x-ui.field>

        <div class="flex items-center justify-end">
            <x-ui.button type="submit" variant="primary" class="w-full">
                {{ __('Reset password') }}
            </x-ui.button>
        </div>
    </form>
</div>
</content>
</invoke>