<div class="mt-4 flex flex-col gap-6">
    <p class="text-center text-sm text-zinc-700 dark:text-zinc-300">
        {{ __('Please verify your email address by clicking on the link we just emailed to you.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <x-ui.alert variant="success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </x-ui.alert>
    @endif

    <div class="flex flex-col items-center justify-between space-y-3">
        <x-ui.button wire:click="sendVerification" variant="primary" class="w-full">
            {{ __('Resend verification email') }}
        </x-ui.button>

        <x-ui.link variant="primary" class="text-sm cursor-pointer" wire:click="logout">
            {{ __('Log out') }}
        </x-ui.link>
    </div>
</div>
</content>
</invoke>