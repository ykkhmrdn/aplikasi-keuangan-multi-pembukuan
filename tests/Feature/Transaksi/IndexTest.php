<?php

namespace Tests\Feature\Transaksi;

use App\Enums\TipeTransaksi;
use App\Livewire\Transaksi\Index;
use App\Models\Kategori;
use App\Models\Pembukuan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private function buatPembukuan(string $tipe = 'pribadi'): Pembukuan
    {
        return Pembukuan::create(['nama' => ucfirst($tipe), 'tipe' => $tipe]);
    }

    public function test_guest_tidak_bisa_akses_halaman_transaksi(): void
    {
        $pembukuan = $this->buatPembukuan();

        $this->get("/transaksi/{$pembukuan->tipe->value}")->assertRedirect('/login');
    }

    public function test_user_bisa_lihat_daftar_transaksi_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pembukuan->id,
            'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan,
            'jumlah' => 500000,
            'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->assertSee('Gaji')
            ->assertSee('500.000');
    }

    public function test_user_bisa_tambah_transaksi_dengan_kategori_global(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('tambah')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('kategoriId', (string) $kategori->id)
            ->set('jumlah', '750000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi', [
            'pembukuan_id' => $pembukuan->id,
            'kategori_id' => $kategori->id,
            'jumlah' => 750000,
        ]);
    }

    public function test_user_bisa_tambah_transaksi_dengan_kategori_khusus_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create([
            'nama' => 'Modal', 'tipe' => TipeTransaksi::Pemasukan, 'pembukuan_id' => $pembukuan->id,
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('tambah')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('kategoriId', (string) $kategori->id)
            ->set('jumlah', '100000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi', ['kategori_id' => $kategori->id]);
    }

    public function test_kategori_dari_pembukuan_lain_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan('pribadi');
        $pembukuanLain = $this->buatPembukuan('kantor');
        $kategoriMilikLain = Kategori::create([
            'nama' => 'Khusus Kantor', 'tipe' => TipeTransaksi::Pemasukan, 'pembukuan_id' => $pembukuanLain->id,
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('tambah')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('kategoriId', (string) $kategoriMilikLain->id)
            ->set('jumlah', '100000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['kategoriId']);
    }

    public function test_jumlah_harus_lebih_dari_nol(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('tambah')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('kategoriId', (string) $kategori->id)
            ->set('jumlah', '0')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['jumlah']);
    }

    public function test_user_bisa_edit_transaksi(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);
        $transaksi = Transaksi::create([
            'pembukuan_id' => $pembukuan->id,
            'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan,
            'jumlah' => 100000,
            'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('edit', $transaksi->id)
            ->set('jumlah', '200000')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('transaksi', ['id' => $transaksi->id, 'jumlah' => 200000]);
    }

    public function test_transaksi_pembukuan_lain_tidak_bisa_diedit_lewat_pembukuan_ini(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan('pribadi');
        $pembukuanLain = $this->buatPembukuan('kantor');
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);
        $transaksiMilikLain = Transaksi::create([
            'pembukuan_id' => $pembukuanLain->id,
            'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan,
            'jumlah' => 100000,
            'tanggal' => now(),
        ]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('edit', $transaksiMilikLain->id);
    }

    public function test_user_bisa_hapus_transaksi(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);
        $transaksi = Transaksi::create([
            'pembukuan_id' => $pembukuan->id,
            'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan,
            'jumlah' => 100000,
            'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('confirmHapus', $transaksi->id)
            ->call('hapus', $transaksi->id);

        $this->assertDatabaseMissing('transaksi', ['id' => $transaksi->id]);
    }

    public function test_filter_tanggal_menyaring_daftar_transaksi(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pembukuan->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 100000, 'tanggal' => '2026-01-01',
        ]);
        Transaksi::create([
            'pembukuan_id' => $pembukuan->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 200000, 'tanggal' => '2026-06-01',
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->set('filterDari', '2026-05-01')
            ->assertSee('200.000')
            ->assertDontSee('100.000');
    }
}
