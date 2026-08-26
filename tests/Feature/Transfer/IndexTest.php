<?php

namespace Tests\Feature\Transfer;

use App\Livewire\Transfer\Index;
use App\Models\Pembukuan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_tidak_bisa_akses_halaman_transfer(): void
    {
        $this->get('/transfer')->assertRedirect('/login');
    }

    public function test_user_bisa_lihat_riwayat_transfer(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        \App\Models\TransferSaldo::create([
            'dari_pembukuan_id' => $pribadi->id,
            'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 300000,
            'tanggal' => now(),
        ]);

        Livewire::test(Index::class)
            ->assertSee('Pribadi')
            ->assertSee('Kantor')
            ->assertSee('300.000');
    }

    public function test_user_bisa_transfer_saldo_antar_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '500000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transfer_saldo', [
            'dari_pembukuan_id' => $pribadi->id,
            'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000,
        ]);
    }

    public function test_pembukuan_tujuan_harus_beda_dengan_asal(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $pribadi->id)
            ->set('jumlah', '500000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['kePembukuanId']);
    }

    public function test_jumlah_transfer_harus_lebih_dari_nol(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '0')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['jumlah']);
    }

    public function test_saldo_kedua_pembukuan_konsisten_setelah_transfer(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        $this->assertEquals('0.00', $pribadi->saldo());
        $this->assertEquals('0.00', $kantor->saldo());

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '500000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan');

        $this->assertEquals('-500000.00', $pribadi->fresh()->saldo());
        $this->assertEquals('500000.00', $kantor->fresh()->saldo());
    }
}
