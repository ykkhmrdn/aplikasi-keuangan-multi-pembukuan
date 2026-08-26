<?php

namespace Tests\Feature\Dashboard;

use App\Enums\TipeTransaksi;
use App\Livewire\Dashboard;
use App\Models\HutangPiutang;
use App\Models\Kategori;
use App\Models\Pembukuan;
use App\Models\Transaksi;
use App\Models\TransferSaldo;
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
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $usaha->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 400000, 'tanggal' => now(),
        ]);

        Livewire::test(Dashboard::class)
            ->assertSet('pembukuanTerpilihId', $pribadi->id)
            ->assertDontSee('Riwayat Usaha')
            ->call('pilihPembukuan', $usaha->id)
            ->assertSet('pembukuanTerpilihId', $usaha->id)
            ->assertSee('Riwayat Usaha')
            ->assertSeeInOrder(['Gaji', '400.000']);
    }

    public function test_riwayat_gabungan_menampilkan_transaksi_transfer_dan_hutang_piutang(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $usaha, $kantor] = $this->buatPembukuan();
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 100000, 'tanggal' => '2026-08-01',
        ]);
        TransferSaldo::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $usaha->id,
            'jumlah' => 50000, 'tanggal' => '2026-08-02',
        ]);
        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 30000, 'tanggal' => '2026-08-03',
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('Gaji')
            ->assertSee('Transfer ke Usaha')
            ->assertSee('Bon ke Kantor');
    }

    public function test_ringkasan_outstanding_sesuai_pembukuan_terpilih(): void
    {
        $this->actingAs(User::factory()->create());
        [$pribadi, $usaha] = $this->buatPembukuan();

        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $usaha->id,
            'jumlah' => 200000, 'tanggal' => now(),
        ]);

        Livewire::test(Dashboard::class)->assertSeeInOrder(['Piutang', '200.000']);
    }
}
