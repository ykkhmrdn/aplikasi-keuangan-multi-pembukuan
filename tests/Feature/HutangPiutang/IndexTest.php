<?php

namespace Tests\Feature\HutangPiutang;

use App\Enums\StatusHutangPiutang;
use App\Livewire\HutangPiutang\Index;
use App\Models\HutangPiutang;
use App\Models\PelunasanHutang;
use App\Models\Pembukuan;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use RefreshDatabase;

    private function buatDuaPembukuan(): array
    {
        return [
            Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']),
            Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']),
        ];
    }

    public function test_guest_tidak_bisa_akses_halaman_hutang_piutang(): void
    {
        [$pribadi] = $this->buatDuaPembukuan();

        $this->get("/hutang-piutang/{$pribadi->tipe->value}")->assertRedirect('/login');
    }

    public function test_user_bisa_lihat_hutang_yang_diterima(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        // pribadi kasih bon ke kantor = kantor berhutang ke pribadi
        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id,
            'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000,
            'tanggal' => now(),
        ]);

        // dilihat dari halaman Kantor (sisi yang berutang) - itu yang tampil
        Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->assertSee('Dari Pribadi')
            ->assertSee('200.000')
            ->assertDontSee('Piutang');
    }

    public function test_halaman_pemberi_bon_tidak_menampilkan_piutang(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000, 'tanggal' => now(),
        ]);

        // dilihat dari halaman Pribadi (sisi yang kasih bon) - bon ini gak tampil,
        // karena sengaja cuma sisi Hutang yang ditampilkan (permintaan client)
        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->assertDontSee('Ke Kantor')
            ->assertDontSee('200.000');
    }

    public function test_user_bisa_catat_bon_baru(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '300000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('hutang_piutang', [
            'dari_pembukuan_id' => $pribadi->id,
            'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 300000,
            'status' => 'belum_lunas',
        ]);
    }

    public function test_pembukuan_penerima_harus_beda_dengan_pemberi(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $pribadi->id)
            ->set('jumlah', '100000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['kePembukuanId']);
    }

    public function test_jumlah_bon_harus_lebih_dari_nol(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '0')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['jumlah']);
    }

    public function test_jumlah_bon_negatif_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '-100000')
            ->set('tanggal', now()->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['jumlah']);
    }

    public function test_tanggal_bon_yang_gak_masuk_akal_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('tambah')
            ->set('dariPembukuanId', (string) $pribadi->id)
            ->set('kePembukuanId', (string) $kantor->id)
            ->set('jumlah', '100000')
            ->set('tanggal', now()->addYears(5)->format('Y-m-d'))
            ->call('simpan')
            ->assertHasErrors(['tanggal']);
    }

    public function test_user_bisa_edit_bon(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000, 'tanggal' => now(), 'keterangan' => 'bon awal',
        ]);

        Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->call('edit', $hp->id)
            ->assertSet('jumlah', '200000.00')
            ->set('jumlah', '250000')
            ->set('keterangan', 'bon direvisi')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('hutang_piutang', [
            'id' => $hp->id, 'jumlah' => 250000, 'keterangan' => 'bon direvisi',
        ]);
    }

    public function test_edit_jumlah_tidak_boleh_kurang_dari_pelunasan_yang_sudah_dibayar(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);
        PelunasanHutang::create(['hutang_piutang_id' => $hp->id, 'jumlah' => 300000, 'tanggal' => now()]);

        Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->call('edit', $hp->id)
            ->set('jumlah', '200000') // di bawah 300rb yang udah dibayar
            ->call('simpan')
            ->assertHasErrors(['jumlah']);
    }

    public function test_edit_dicek_ulang_terhadap_pelunasan_terbaru_bukan_nilai_lama(): void
    {
        // simulasi race condition: form edit dibuka & diisi SEBELUM ada pelunasan,
        // tapi pelunasan lain masuk & tercatat SETELAH itu, SEBELUM form edit disubmit -
        // pengecekan "jumlah gak boleh kurang dari total pelunasan" harus pakai data
        // TERBARU (dicek ulang di dalam lock), bukan snapshot lama waktu form dibuka
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        // form edit dibuka & diisi 450rb - sah pada saat itu, belum ada pelunasan sama sekali
        $component = Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->call('edit', $hp->id)
            ->set('jumlah', '450000');

        // "pelunasan lain" masuk & lunas di antara form dibuka dan disubmit (simulasi request bersamaan)
        Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->call('melunasi', $hp->id)
            ->set('jumlahPelunasan', '480000')
            ->call('simpanPelunasan')
            ->assertHasNoErrors();

        // form edit yang kepalang keisi 450rb (di bawah 480rb yang baru aja dibayar) disubmit -
        // harus ditolak, bukan malah lolos dan bikin sisa outstanding jadi minus
        $component->call('simpan')->assertHasErrors(['jumlah']);

        $hp->refresh();
        $this->assertEquals('500000.00', $hp->jumlah); // jumlah TIDAK berubah, tetap 500rb
        $this->assertEquals('20000.00', $hp->sisaOutstanding()); // 500rb - 480rb, bukan minus
    }

    public function test_edit_bon_yang_gak_melibatkan_pembukuan_ini_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);

        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $usaha->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('edit', $hp->id);
    }

    public function test_user_bisa_hapus_bon(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->call('hapus', $hp->id);

        $this->assertDatabaseMissing('hutang_piutang', ['id' => $hp->id]);
    }

    public function test_hapus_bon_yang_sudah_ada_pelunasan_tetap_boleh_dan_saldo_kembali_normal(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);
        PelunasanHutang::create(['hutang_piutang_id' => $hp->id, 'jumlah' => 200000, 'tanggal' => now()]);

        // sebelum hapus: pribadi minus 300rb (500rb keluar, 200rb pelunasan masuk), kantor plus 300rb
        $this->assertEquals('-300000.00', $pribadi->fresh()->saldo());
        $this->assertEquals('300000.00', $kantor->fresh()->saldo());

        Livewire::test(Index::class, ['pembukuan' => $kantor])->call('hapus', $hp->id);

        $this->assertDatabaseMissing('hutang_piutang', ['id' => $hp->id]);
        $this->assertDatabaseMissing('pelunasan_hutang', ['hutang_piutang_id' => $hp->id]);
        // saldo otomatis balik normal begitu bon + pelunasannya kehapus (cascade)
        $this->assertEquals('0.00', $pribadi->fresh()->saldo());
        $this->assertEquals('0.00', $kantor->fresh()->saldo());
    }

    public function test_hapus_bon_yang_gak_melibatkan_pembukuan_ini_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);

        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $usaha->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('hapus', $hp->id);
    }

    public function test_tanggal_pelunasan_yang_gak_masuk_akal_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->set('tanggalPelunasan', now()->subYears(15)->format('Y-m-d'))
            ->call('simpanPelunasan')
            ->assertHasErrors(['tanggalPelunasan']);
    }

    public function test_jumlah_pelunasan_nol_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->set('jumlahPelunasan', '0')
            ->call('simpanPelunasan')
            ->assertHasErrors(['jumlahPelunasan']);

        $this->assertEquals('500000.00', $hp->fresh()->sisaOutstanding());
    }

    public function test_jumlah_pelunasan_negatif_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->set('jumlahPelunasan', '-50000')
            ->call('simpanPelunasan')
            ->assertHasErrors(['jumlahPelunasan']);

        $this->assertEquals('500000.00', $hp->fresh()->sisaOutstanding());
    }

    public function test_pelunasan_dicek_ulang_terhadap_sisa_terbaru_bukan_nilai_lama(): void
    {
        // simulasi race condition: sisa outstanding berubah (lewat pelunasan lain) SETELAH
        // form pelunasan dibuka tapi SEBELUM disubmit - lockForUpdate + re-check di dalam
        // transaction harus pakai sisa TERBARU, bukan snapshot lama waktu form dibuka
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        $component = Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->assertSet('jumlahPelunasan', '500000.00');

        // "pelunasan lain" masuk duluan di antara form dibuka dan disubmit (simulasi request bersamaan)
        PelunasanHutang::create([
            'hutang_piutang_id' => $hp->id, 'jumlah' => 400000, 'tanggal' => now(),
        ]);

        // form yang sudah kepalang keisi 500000 (sisa lama) disubmit - harus ditolak
        // karena sisa terbaru cuma 100000, bukan malah lolos dan bikin sisa jadi minus
        $component->call('simpanPelunasan')->assertHasErrors(['jumlahPelunasan']);

        $this->assertEquals('100000.00', $hp->fresh()->sisaOutstanding());
    }

    public function test_pelunasan_penuh_mengubah_status_jadi_lunas(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->assertSet('jumlahPelunasan', '500000.00')
            ->call('simpanPelunasan')
            ->assertHasNoErrors();

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::Lunas, $hp->status);
        $this->assertNotNull($hp->tanggal_lunas);
        $this->assertEquals('0.00', $hp->sisaOutstanding());
    }

    public function test_pelunasan_partial_tidak_mengubah_status(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->set('jumlahPelunasan', '200000')
            ->call('simpanPelunasan')
            ->assertHasNoErrors();

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::BelumLunas, $hp->status);
        $this->assertEquals('300000.00', $hp->sisaOutstanding());

        // pelunasan kedua melunasi sisanya
        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->assertSet('jumlahPelunasan', '300000.00')
            ->call('simpanPelunasan')
            ->assertHasNoErrors();

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::Lunas, $hp->status);
    }

    public function test_pelunasan_melebihi_sisa_ditolak(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->set('jumlahPelunasan', '600000')
            ->call('simpanPelunasan')
            ->assertHasErrors(['jumlahPelunasan']);

        $hp->refresh();
        $this->assertEquals(StatusHutangPiutang::BelumLunas, $hp->status);
        $this->assertEquals('500000.00', $hp->sisaOutstanding());
    }

    public function test_ringkasan_outstanding_benar(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000, 'tanggal' => now(),
        ]);
        HutangPiutang::create([
            'dari_pembukuan_id' => $kantor->id, 'ke_pembukuan_id' => $pribadi->id,
            'jumlah' => 50000, 'tanggal' => now(),
        ]);

        $this->assertEquals('50000.00', $pribadi->hutangOutstanding());
    }

    public function test_saldo_konsisten_setelah_bon_dan_pelunasan(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        // pribadi kasih bon: kas keluar 500rb, kantor terima bon: kas masuk 500rb
        $this->assertEquals('-500000.00', $pribadi->fresh()->saldo());
        $this->assertEquals('500000.00', $kantor->fresh()->saldo());

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id)
            ->call('simpanPelunasan');

        // kantor bayar utang penuh: kas keluar 500rb (balik ke 0)
        // pribadi terima pelunasan: kas masuk 500rb (balik ke 0)
        $this->assertEquals('0.00', $pribadi->fresh()->saldo());
        $this->assertEquals('0.00', $kantor->fresh()->saldo());
    }

    public function test_bon_pembukuan_lain_tidak_bisa_dilunasi_lewat_pembukuan_ini(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);

        // bon ini sama sekali gak melibatkan Pribadi (Usaha <-> Kantor)
        $hp = HutangPiutang::create([
            'dari_pembukuan_id' => $usaha->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 500000, 'tanggal' => now(),
        ]);

        $this->expectException(ModelNotFoundException::class);

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('melunasi', $hp->id);
    }

    public function test_pencarian_menyaring_hutang_berdasarkan_keterangan_atau_nama_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);

        // dua bon yang sama-sama diterima Kantor, dari pemberi berbeda
        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 100000, 'tanggal' => now(), 'keterangan' => 'bon dana kas kecil',
        ]);
        HutangPiutang::create([
            'dari_pembukuan_id' => $usaha->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 200000, 'tanggal' => now(),
        ]);

        Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->set('search', 'pribadi')
            ->assertSee('100.000') // dari Pribadi cocok
            ->assertDontSee('200.000'); // dari Usaha gak cocok
    }

    public function test_pencarian_reset_ke_halaman_pertama(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatDuaPembukuan();

        Livewire::test(Index::class, ['pembukuan' => $pribadi])
            ->call('setPage', 2)
            ->set('search', 'apa saja')
            ->assertSet('paginators.page', 1);
    }

    public function test_urutan_jumlah_terbesar_menampilkan_bon_besar_lebih_dulu(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 100000, 'tanggal' => now(),
        ]);
        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 900000, 'tanggal' => now(),
        ]);

        $html = Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->set('sort', 'jumlah_terbesar')
            ->html();

        $this->assertTrue(strpos($html, '900.000') < strpos($html, '100.000'));
    }

    public function test_daftar_hutang_dipaginasi_10_per_halaman(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $kantor] = $this->buatDuaPembukuan();

        // keterangan dipadding 2 digit biar assertSee/assertDontSee gak ketuker substring
        // tanggal beda tiap bon biar urutan default (tanggal terbaru) konsisten dan bisa diprediksi
        foreach (range(1, 15) as $i) {
            HutangPiutang::create([
                'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
                'jumlah' => 10000,
                'tanggal' => now()->subDays(15 - $i),
                'keterangan' => 'Bon ke-'.str_pad($i, 2, '0', STR_PAD_LEFT),
            ]);
        }

        Livewire::test(Index::class, ['pembukuan' => $kantor])
            ->assertSee('Bon ke-15') // terbaru, harus di halaman 1
            ->assertDontSee('Bon ke-01') // terlama, harus di halaman 2
            ->call('nextPage')
            ->assertSee('Bon ke-01');
    }
}
