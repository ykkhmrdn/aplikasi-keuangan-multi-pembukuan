<?php

namespace Tests\Feature\Kategori;

use App\Enums\TipeTransaksi;
use App\Livewire\Kategori\Index;
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

    public function test_guest_tidak_bisa_akses_halaman_kategori(): void
    {
        $this->get('/kategori')->assertRedirect('/login');
    }

    public function test_user_bisa_lihat_daftar_kategori(): void
    {
        $this->actingAs(User::factory()->create());

        Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class)
            ->assertSee('Gaji')
            ->assertSee('Global');
    }

    public function test_user_bisa_tambah_kategori_global(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('nama', 'Bonus')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('pembukuanId', 'global')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori', [
            'nama' => 'Bonus',
            'tipe' => 'pemasukan',
            'pembukuan_id' => null,
        ]);
    }

    public function test_tambah_kategori_tanpa_menyentuh_dropdown_tipe_dan_pembukuan_tetap_berhasil(): void
    {
        // regresi: dulu tipe gak ke-set default (cuma reset() ke null) walau dropdown-nya
        // visual udah keliatan "Pemasukan" terpilih - user yang gak sentuh dropdown sama
        // sekali (alur paling wajar) bakal ketolak validasi "tipe wajib diisi"
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('nama', 'Kategori Tanpa Sentuh Dropdown')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori', [
            'nama' => 'Kategori Tanpa Sentuh Dropdown',
            'tipe' => 'pemasukan',
            'pembukuan_id' => null,
        ]);
    }

    public function test_user_bisa_tambah_kategori_khusus_satu_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('nama', 'Modal Usaha')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('pembukuanId', (string) $pembukuan->id)
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori', [
            'nama' => 'Modal Usaha',
            'pembukuan_id' => $pembukuan->id,
        ]);
    }

    public function test_nama_wajib_diisi(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('nama', '')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->call('simpan')
            ->assertHasErrors(['nama' => 'required']);
    }

    public function test_tidak_bisa_tambah_kategori_duplikat_nama_tipe_dan_scope_sama(): void
    {
        $this->actingAs(User::factory()->create());
        Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class)
            ->call('tambah')
            ->set('nama', 'Gaji')
            ->set('tipe', TipeTransaksi::Pemasukan->value)
            ->set('pembukuanId', 'global')
            ->call('simpan')
            ->assertHasErrors(['nama' => 'unique']);
    }

    public function test_user_bisa_edit_nama_kategori(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class)
            ->call('edit', $kategori->id)
            ->set('nama', 'Gaji Bulanan')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori', ['id' => $kategori->id, 'nama' => 'Gaji Bulanan']);
    }

    public function test_tipe_terkunci_kalau_kategori_sudah_dipakai_di_transaksi(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pembukuan->id,
            'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan,
            'jumlah' => 1000,
            'tanggal' => now(),
        ]);

        Livewire::test(Index::class)
            ->call('edit', $kategori->id)
            ->assertSet('editingLocked', true)
            ->set('tipe', TipeTransaksi::Pengeluaran->value) // dicoba diubah, tapi harus tetap terkunci
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('kategori', ['id' => $kategori->id, 'tipe' => 'pemasukan']);
    }

    public function test_user_bisa_hapus_kategori_yang_belum_dipakai(): void
    {
        $this->actingAs(User::factory()->create());
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Livewire::test(Index::class)
            ->call('confirmHapus', $kategori->id)
            ->call('hapus', $kategori->id);

        $this->assertDatabaseMissing('kategori', ['id' => $kategori->id]);
    }

    public function test_kategori_yang_sudah_dipakai_tidak_bisa_dihapus(): void
    {
        $this->actingAs(User::factory()->create());
        $pembukuan = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pembukuan->id,
            'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan,
            'jumlah' => 1000,
            'tanggal' => now(),
        ]);

        Livewire::test(Index::class)
            ->call('confirmHapus', $kategori->id)
            ->call('hapus', $kategori->id)
            ->assertSet('deleteErrorMessage', function ($message) {
                return str_contains($message, 'tidak bisa dihapus');
            });

        $this->assertDatabaseHas('kategori', ['id' => $kategori->id]);
    }

    public function test_pencarian_menyaring_kategori_berdasarkan_nama(): void
    {
        $this->actingAs(User::factory()->create());
        Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);
        Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        Livewire::test(Index::class)
            ->set('search', 'gaj')
            ->assertSee('Gaji')
            ->assertDontSee('Belanja');
    }

    public function test_pencarian_reset_ke_halaman_pertama(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(Index::class)
            ->call('setPage', 2)
            ->set('search', 'apa saja')
            ->assertSet('paginators.page', 1);
    }

    public function test_urutan_nama_z_a_menampilkan_kategori_z_lebih_dulu(): void
    {
        $this->actingAs(User::factory()->create());
        Kategori::create(['nama' => 'Ayam', 'tipe' => TipeTransaksi::Pengeluaran]);
        Kategori::create(['nama' => 'Zebra', 'tipe' => TipeTransaksi::Pengeluaran]);

        $html = Livewire::test(Index::class)
            ->set('sort', 'nama_desc')
            ->html();

        $this->assertTrue(strpos($html, 'Zebra') < strpos($html, 'Ayam'));
    }

    public function test_daftar_kategori_dipaginasi_10_per_halaman(): void
    {
        $this->actingAs(User::factory()->create());

        // nama dipadding 2 digit biar urutan alfabet = urutan angka, hindari "Kategori 10" < "Kategori 2"
        foreach (range(1, 15) as $i) {
            Kategori::create(['nama' => 'Kategori '.str_pad($i, 2, '0', STR_PAD_LEFT), 'tipe' => TipeTransaksi::Pengeluaran]);
        }

        Livewire::test(Index::class)
            ->assertSee('Kategori 01')
            ->assertDontSee('Kategori 15')
            ->call('nextPage')
            ->assertSee('Kategori 15');
    }
}
