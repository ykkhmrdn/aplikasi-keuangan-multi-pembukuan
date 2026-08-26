<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.guest')]
class Login extends Component
{
    #[Validate('required|string')]
    public string $username = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    // batas percobaan login gagal sebelum dikunci sementara (proteksi brute force)
    private const MAKS_PERCOBAAN = 5;

    private const LAMA_KUNCI_DETIK = 60;

    /**
     * Proses login. Single-user, tidak ada registrasi (lihat docs/SCOPE_LOCK.md).
     */
    public function login(): void
    {
        $this->validate();

        if (RateLimiter::tooManyAttempts($this->throttleKey(), self::MAKS_PERCOBAAN)) {
            $sisaDetik = RateLimiter::availableIn($this->throttleKey());

            $this->addError('username', "Terlalu banyak percobaan login. Coba lagi dalam {$sisaDetik} detik.");

            return;
        }

        if (! Auth::attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->throttleKey(), self::LAMA_KUNCI_DETIK);

            $this->addError('username', 'Username atau password salah.');

            return;
        }

        RateLimiter::clear($this->throttleKey());

        session()->regenerate();

        $this->redirect(route('dashboard'), navigate: true);
    }

    /** Key rate limiter digabung username + IP, biar gak ganggu user lain dari IP sama. */
    private function throttleKey(): string
    {
        return Str::lower($this->username).'|'.request()->ip();
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}
