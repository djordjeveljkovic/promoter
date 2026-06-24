<?php

namespace App\Livewire\Settings;

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    public string $password = '';

    /**
     * Safety-net close action. Bound to the modal's close (X) button as a
     * `wire:click` handler so the modal can always be dismissed even if the
     * Alpine `x-data` state ever gets stuck on `open: true` (e.g. after a
     * wire:navigate morph leaves a stale state). Resets the password field
     * too so a previously typed password doesn't linger.
     */
    public function closeModal(): void
    {
        $this->reset('password');
        $this->dispatch('close-delete-account-modal');
    }

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}
