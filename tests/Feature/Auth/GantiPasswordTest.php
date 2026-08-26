<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\GantiPassword;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class GantiPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_tidak_bisa_akses_halaman_ganti_password(): void
    {
        $this->get('/ganti-password')->assertRedirect('/login');
    }

    public function test_user_bisa_ganti_password_dengan_password_lama_benar(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);
        $this->actingAs($user);

        Livewire::test(GantiPassword::class)
            ->set('password_lama', 'password-lama')
            ->set('password_baru', 'password-baru-123')
            ->set('password_baru_confirmation', 'password-baru-123')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('berhasil', true);

        $this->assertTrue(Hash::check('password-baru-123', $user->fresh()->password));
    }

    public function test_ganti_password_gagal_kalau_password_lama_salah(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);
        $this->actingAs($user);

        Livewire::test(GantiPassword::class)
            ->set('password_lama', 'password-salah')
            ->set('password_baru', 'password-baru-123')
            ->set('password_baru_confirmation', 'password-baru-123')
            ->call('simpan')
            ->assertHasErrors(['password_lama']);

        $this->assertTrue(Hash::check('password-lama', $user->fresh()->password));
    }

    public function test_ganti_password_gagal_kalau_konfirmasi_tidak_cocok(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);
        $this->actingAs($user);

        Livewire::test(GantiPassword::class)
            ->set('password_lama', 'password-lama')
            ->set('password_baru', 'password-baru-123')
            ->set('password_baru_confirmation', 'tidak-cocok')
            ->call('simpan')
            ->assertHasErrors(['password_baru']);
    }

    public function test_ganti_password_gagal_kalau_password_baru_kurang_dari_8_karakter(): void
    {
        $user = User::factory()->create(['password' => 'password-lama']);
        $this->actingAs($user);

        Livewire::test(GantiPassword::class)
            ->set('password_lama', 'password-lama')
            ->set('password_baru', 'pendek')
            ->set('password_baru_confirmation', 'pendek')
            ->call('simpan')
            ->assertHasErrors(['password_baru']);
    }
}
