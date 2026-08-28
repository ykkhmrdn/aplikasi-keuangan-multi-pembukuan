<?php

namespace Tests\Feature\Dashboard;

use App\Enums\TipeTransaksi;
use App\Livewire\Dashboard;
use App\Models\HutangPiutang;
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

    private function buatPembukuan(): array
    {
        return [
            Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']),
            Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']),
            Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']),
        ];
    }

    public function test_guest_tidak_bisa_akses_dashboard(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_user_bisa_lihat_kartu_saldo_semua_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        $this->buatPembukuan();

        Livewire::test(Dashboard::class)
            ->assertSee('Pribadi')
            ->assertSee('Usaha')
            ->assertSee('Kantor');
    }

    public function test_kartu_saldo_menampilkan_angka_yang_benar(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 750000, 'tanggal' => now(),
        ]);

        Livewire::test(Dashboard::class)->assertSee('750.000');
    }

    public function test_saldo_minus_ditandai_secara_visual(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        // pengeluaran tanpa pemasukan apapun - saldo pasti minus
        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 100000, 'tanggal' => now(),
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('-Rp100.000')
            ->assertSee('Saldo minus');
    }

    public function test_saldo_positif_tidak_ditandai_saldo_minus(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 100000, 'tanggal' => now(),
        ]);

        Livewire::test(Dashboard::class)->assertDontSee('Saldo minus');
    }

    public function test_user_bisa_pindah_pembukuan_terpilih(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $usaha] = $this->buatPembukuan();

        // pakai entitas HTML mentah (&mdash;) - itu bentuk asli di response, bukan
        // karakter em dash unicode yang cuma muncul setelah di-render browser
        Livewire::test(Dashboard::class)
            ->assertSet('pembukuanTerpilihId', $pribadi->id)
            ->assertDontSee('&mdash; Usaha', false)
            ->call('pilihPembukuan', $usaha->id)
            ->assertSet('pembukuanTerpilihId', $usaha->id)
            ->assertSee('&mdash; Usaha', false);
    }

    public function test_hutang_detail_menampilkan_item_per_pembukuan(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $usaha] = $this->buatPembukuan();

        // pribadi kasih bon ke usaha = usaha berhutang ke pribadi
        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $usaha->id,
            'jumlah' => 200000, 'tanggal' => now(),
        ]);

        Livewire::test(Dashboard::class)
            ->call('pilihPembukuan', $usaha->id)
            ->assertSeeInOrder(['Hutang (belum dibayar)', '200.000', 'Dari Pribadi'])
            ->assertDontSee('Piutang');
    }

    public function test_analisis_pengeluaran_menampilkan_breakdown_per_kategori(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 150000, 'tanggal' => now(),
        ]);

        Livewire::test(Dashboard::class)->assertSeeInOrder(['Belanja', '150.000', '100']);
    }

    public function test_kategori_baru_tanpa_transaksi_tetap_muncul_di_analisis(): void
    {
        $this->actingAs(User::factory()->create());
        $this->buatPembukuan();
        Kategori::create(['nama' => 'Hiburan', 'tipe' => TipeTransaksi::Pengeluaran]);

        Livewire::test(Dashboard::class)->assertSeeInOrder(['Hiburan', 'Rp0']);
    }

    public function test_filter_periode_custom_membatasi_analisis_pengeluaran(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 150000, 'tanggal' => '2026-01-01',
        ]);

        Livewire::test(Dashboard::class)
            ->set('periode', 'custom')
            ->set('tanggalDari', '2026-08-01')
            ->set('tanggalSampai', '2026-08-31')
            ->assertSeeInOrder(['Belanja', 'Rp0']);
    }

    public function test_filter_harian_tidak_menampilkan_transaksi_kemarin(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 100000, 'tanggal' => now()->subDay(),
        ]);
        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 50000, 'tanggal' => now(),
        ]);

        // default periode 'bulanan' bakal nangkep dua-duanya, ganti ke 'harian' -
        // transaksi kemarin harus ke-exclude, cuma transaksi hari ini yang kehitung
        Livewire::test(Dashboard::class)
            ->set('periode', 'harian')
            ->assertSeeInOrder(['Belanja', 'Rp50.000']);
    }

    public function test_filter_bulanan_tidak_menampilkan_transaksi_bulan_lalu(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        // transaksi persis di hari terakhir bulan lalu - kasus pergantian bulan,
        // gak boleh ketarik ke "Bulan ini" biarpun cuma selisih 1 hari
        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 200000,
            'tanggal' => now()->startOfMonth()->subDay(),
        ]);
        // transaksi persis di tanggal 1 bulan ini - harus kehitung (boundary awal bulan)
        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 75000,
            'tanggal' => now()->startOfMonth(),
        ]);

        Livewire::test(Dashboard::class)
            ->set('periode', 'bulanan')
            ->assertSeeInOrder(['Belanja', 'Rp75.000']);
    }

    public function test_agregasi_analisis_pengeluaran_akurat_untuk_banyak_transaksi_beda_kategori(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $makan = Kategori::create(['nama' => 'Makan & Minum', 'tipe' => TipeTransaksi::Pengeluaran]);
        $transport = Kategori::create(['nama' => 'Transportasi', 'tipe' => TipeTransaksi::Pengeluaran]);

        // 3 transaksi kategori Makan, 2 transaksi kategori Transportasi - total gak boleh
        // meleset (gak ada double count atau kepotong pas di-groupBy per kategori)
        foreach ([15000, 22500, 7500] as $jumlah) {
            Transaksi::create([
                'pembukuan_id' => $pribadi->id, 'kategori_id' => $makan->id,
                'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => $jumlah, 'tanggal' => now(),
            ]);
        }
        foreach ([10000, 5000] as $jumlah) {
            Transaksi::create([
                'pembukuan_id' => $pribadi->id, 'kategori_id' => $transport->id,
                'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => $jumlah, 'tanggal' => now(),
            ]);
        }
        // pemasukan gak boleh ikut kehitung ke Analisis Pengeluaran
        $gaji = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);
        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $gaji->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 5000000, 'tanggal' => now(),
        ]);

        // Makan: 15000+22500+7500 = 45000, Transportasi: 10000+5000 = 15000
        // total pengeluaran keduanya = 60000, jadi persentase 75% & 25% (bukan dihitung
        // dari total keseluruhan termasuk pemasukan - itu salah, harus cuma dari pengeluaran)
        Livewire::test(Dashboard::class)
            ->assertSeeInOrder(['Makan & Minum', 'Rp45.000', '75'])
            ->assertSeeInOrder(['Transportasi', 'Rp15.000', '25'])
            ->assertDontSee('Gaji');
    }

    public function test_filter_semua_waktu_menampilkan_transaksi_lintas_bulan(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 200000, 'tanggal' => '2020-01-01',
        ]);
        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 75000, 'tanggal' => now(),
        ]);

        // total gabungan lintas waktu, gak boleh ada yang ke-exclude atau double count
        Livewire::test(Dashboard::class)
            ->set('periode', 'semua')
            ->assertSeeInOrder(['Belanja', 'Rp275.000']);
    }
}
