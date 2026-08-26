<?php

namespace Tests\Feature\Transaksi;

use App\Enums\TipeTransaksi;
use App\Livewire\Transaksi\Index;
use App\Models\Kategori;
use App\Models\Pembukuan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
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

    public function test_jumlah_negatif_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('tambah')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('kategoriId', (string) $kategori->id)
            ->set('jumlah', '-50000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['jumlah']);
    }

    public function test_tanggal_yang_gak_masuk_akal_ditolak(): void
    {
        // batas 10 tahun ke belakang & 1 tahun ke depan, buat nangkep typo tahun
        // (mis. 2026 keketik jadi 3026), bukan larangan backdate wajar
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('tambah')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('kategoriId', (string) $kategori->id)
            ->set('jumlah', '50000')
            ->set('tanggal', now()->addYears(5)->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['tanggal']);
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

        $this->expectException(ModelNotFoundException::class);

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

    public function test_pencarian_menyaring_transaksi_berdasarkan_keterangan_atau_kategori(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $gaji = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);
        $belanja = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        Transaksi::create([
            'pembukuan_id' => $pembukuan->id, 'kategori_id' => $gaji->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 100000, 'tanggal' => now(), 'keterangan' => 'gaji bulan ini',
        ]);
        Transaksi::create([
            'pembukuan_id' => $pembukuan->id, 'kategori_id' => $belanja->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 50000, 'tanggal' => now(), 'keterangan' => 'jajan',
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->set('search', 'gaji')
            ->assertSee('100.000')
            ->assertDontSee('50.000');
    }

    public function test_pencarian_reset_ke_halaman_pertama(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->call('setPage', 2)
            ->set('search', 'apa saja')
            ->assertSet('paginators.page', 1);
    }

    public function test_urutan_jumlah_terbesar_menampilkan_transaksi_besar_lebih_dulu(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pembukuan->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 100000, 'tanggal' => now(),
        ]);
        Transaksi::create([
            'pembukuan_id' => $pembukuan->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 900000, 'tanggal' => now(),
        ]);

        $html = Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->set('sort', 'jumlah_terbesar')
            ->html();

        $this->assertTrue(strpos($html, '900.000') < strpos($html, '100.000'));
    }

    public function test_daftar_transaksi_dipaginasi_10_per_halaman(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        // keterangan dipadding 2 digit biar assertSee/assertDontSee gak ketuker substring (mis. "ke-1" ada di dalam "ke-10")
        // tanggal beda tiap transaksi biar urutan default (tanggal terbaru) konsisten dan bisa diprediksi
        foreach (range(1, 15) as $i) {
            Transaksi::create([
                'pembukuan_id' => $pembukuan->id, 'kategori_id' => $kategori->id,
                'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 10000,
                'tanggal' => now()->subDays(15 - $i), // makin besar $i, makin baru
                'keterangan' => 'Transaksi ke-'.str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        Livewire::test(Index::class, ['pembukuan' => $pembukuan])
            ->assertSee('Transaksi ke-15') // transaksi terbaru, harus di halaman 1
            ->assertDontSee('Transaksi ke-01') // transaksi terlama, harus di halaman 2
            ->call('nextPage')
            ->assertSee('Transaksi ke-01');
    }
}
