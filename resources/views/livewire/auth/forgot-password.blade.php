<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Forgot password')" :description="__('Enter your email to receive a password reset link')" />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <x-ui.field :label="__('Email Address')" for="email" required>
            <x-ui.input id="email" wire:model="email" type="email" required autofocus placeholder="email@example.com" />
        </x-ui.field>

        <x-ui.button variant="primary" type="submit" class="w-full">
            {{ __('Email password reset link') }}
        </x-ui.button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('Or, return to') }}
        <x-ui.link variant="primary" :href="route('login')">{{ __('log in') }}</x-ui.link>
    </div>
</div>
</content>
</invoke>