<?php

namespace Tests\Feature\Transfer;

use App\Livewire\Transfer\Index;
use App\Models\Pembukuan;
use App\Models\TransferSaldo;
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

        TransferSaldo::create([
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

    public function test_jumlah_transfer_negatif_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '-100000')
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

    public function test_pencarian_menyaring_transfer_berdasarkan_keterangan_atau_nama_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        TransferSaldo::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 100000, 'tanggal' => now(), 'keterangan' => 'modal awal',
        ]);
        TransferSaldo::create([
            'dari_pembukuan_id' => $usaha->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000, 'tanggal' => now(), 'keterangan' => 'operasional',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'modal')
            ->assertSee('100.000')
            ->assertDontSee('200.000');
    }

    public function test_pencarian_reset_ke_halaman_pertama(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->call('setPage', 2)
            ->set('search', 'apa saja')
            ->assertSet('paginators.page', 1);
    }

    public function test_urutan_jumlah_terbesar_menampilkan_transfer_besar_lebih_dulu(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        TransferSaldo::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 100000, 'tanggal' => now(),
        ]);
        TransferSaldo::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 900000, 'tanggal' => now(),
        ]);

        $html = Livewire::test(Index::class)
            ->set('sort', 'jumlah_terbesar')
            ->html();

        $this->assertTrue(strpos($html, '900.000') < strpos($html, '100.000'));
    }

    public function test_daftar_transfer_dipaginasi_10_per_halaman(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        // keterangan dipadding 2 digit biar assertSee/assertDontSee gak ketuker substring
        // tanggal beda tiap transfer biar urutan default (tanggal terbaru) konsisten dan bisa diprediksi
        foreach (range(1, 15) as $i) {
            TransferSaldo::create([
                'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
                'jumlah' => 10000,
                'tanggal' => now()->subDays(15 - $i),
                'keterangan' => 'Transfer ke-'.str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        Livewire::test(Index::class)
            ->assertSee('Transfer ke-15') // terbaru, harus di halaman 1
            ->assertDontSee('Transfer ke-01') // terlama, harus di halaman 2
            ->call('nextPage')
            ->assertSee('Transfer ke-01');
    }
}
