<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * Komponen nested (bukan halaman penuh), ditempel di halaman Akun
 * (livewire.auth.ganti-password) berdampingan dengan form ganti password.
 */
class UbahUsername extends Component
{
    public string $username = '';

    public bool $berhasil = false;

    public function mount(): void
    {
        $this->username = Auth::user()->username;
    }

    public function simpan(): void
    {
        $this->berhasil = false;

        $this->validate([
            'username' => [
                'required', 'string', 'max:255',
                Rule::unique('users', 'username')->ignore(Auth::id()),
            ],
        ], [], [
            'username' => 'username',
        ]);

        Auth::user()->update(['username' => $this->username]);

        $this->berhasil = true;
    }

    public function render()
    {
        return view('livewire.auth.ubah-username');
    }
}
