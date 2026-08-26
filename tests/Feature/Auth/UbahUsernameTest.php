<?php

namespace Tests\Feature\Auth;

use App\Livewire\Auth\UbahUsername;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UbahUsernameTest extends TestCase
{
    use RefreshDatabase;

    public function test_form_terisi_username_saat_ini(): void
    {
        $user = User::factory()->create(['username' => 'admin']);
        $this->actingAs($user);

        Livewire::test(UbahUsername::class)
            ->assertSet('username', 'admin');
    }

    public function test_user_bisa_ubah_username(): void
    {
        $user = User::factory()->create(['username' => 'admin']);
        $this->actingAs($user);

        Livewire::test(UbahUsername::class)
            ->set('username', 'reza')
            ->call('simpan')
            ->assertHasNoErrors()
            ->assertSet('berhasil', true);

        $this->assertEquals('reza', $user->fresh()->username);
    }

    public function test_username_wajib_diisi(): void
    {
        $user = User::factory()->create(['username' => 'admin']);
        $this->actingAs($user);

        Livewire::test(UbahUsername::class)
            ->set('username', '')
            ->call('simpan')
            ->assertHasErrors(['username' => 'required']);
    }

    public function test_username_tidak_boleh_sama_dengan_user_lain(): void
    {
        User::factory()->create(['username' => 'reza']);
        $user = User::factory()->create(['username' => 'admin']);
        $this->actingAs($user);

        Livewire::test(UbahUsername::class)
            ->set('username', 'reza')
            ->call('simpan')
            ->assertHasErrors(['username' => 'unique']);
    }

    public function test_username_boleh_disimpan_sama_dengan_punya_sendiri(): void
    {
        $user = User::factory()->create(['username' => 'admin']);
        $this->actingAs($user);

        Livewire::test(UbahUsername::class)
            ->set('username', 'admin')
            ->call('simpan')
            ->assertHasNoErrors();
    }
}
