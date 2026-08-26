<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class GantiPassword extends Component
{
    public string $password_lama = '';

    public string $password_baru = '';

    public string $password_baru_confirmation = '';

    public bool $berhasil = false;

    public function simpan(): void
    {
        $this->berhasil = false;

        $this->validate([
            'password_lama' => ['required', 'current_password'],
            'password_baru' => ['required', 'confirmed', Password::min(8)],
        ], [
            'password_lama.current_password' => 'Password lama tidak cocok.',
        ], [
            'password_lama' => 'password lama',
            'password_baru' => 'password baru',
        ]);

        Auth::user()->update(['password' => $this->password_baru]);

        $this->reset(['password_lama', 'password_baru', 'password_baru_confirmation']);
        $this->berhasil = true;
    }

    public function render()
    {
        return view('livewire.auth.ganti-password');
    }
}
