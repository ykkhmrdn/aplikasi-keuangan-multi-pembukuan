<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_diarahkan_ke_login_saat_akses_halaman_utama(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_halaman_login_bisa_diakses(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_user_bisa_login_dengan_kredensial_benar(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'rahasia123',
        ]);

        Livewire::test(Login::class)
            ->set('username', 'admin')
            ->set('password', 'rahasia123')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
    }

    public function test_user_gagal_login_dengan_password_salah(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'rahasia123',
        ]);

        Livewire::test(Login::class)
            ->set('username', 'admin')
            ->set('password', 'password-salah')
            ->call('login')
            ->assertHasErrors('username');

        $this->assertGuest();
    }

    public function test_user_yang_sudah_login_diarahkan_keluar_dari_halaman_login(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect('/');
    }

    public function test_user_bisa_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect('/login');

        $this->assertGuest();
    }
}
