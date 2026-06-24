<div class="flex flex-col gap-6">
    <x-auth-header
        :title="__('Confirm password')"
        :description="__('This is a secure area of the application. Please confirm your password before continuing.')"
    />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="confirmPassword" class="flex flex-col gap-6">
        <x-ui.field :label="__('Password')" for="password" required>
            <x-ui.input id="password" wire:model="password" type="password" required autocomplete="new-password" :placeholder="__('Password')" />
        </x-ui.field>

        <x-ui.button variant="primary" type="submit" class="w-full">
            {{ __('Confirm') }}
        </x-ui.button>
    </form>
</div>
</content>
</invoke>