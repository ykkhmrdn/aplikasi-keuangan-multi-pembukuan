<?php

namespace Tests\Feature;

use App\Enums\TipeTransaksi;
use App\Models\HutangPiutang;
use App\Models\Kategori;
use App\Models\Pembukuan;
use App\Models\Transaksi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteBindingTest extends TestCase
{
    use RefreshDatabase;

    public function test_tipe_pembukuan_tidak_valid_di_url_menghasilkan_404(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/transaksi/tidak-ada')->assertNotFound();
        $this->get('/hutang-piutang/tidak-ada')->assertNotFound();
    }

    /**
     * Verifikasi isolasi data antar pembukuan lewat HTTP route beneran (bukan cuma
     * Livewire::test yang inject model pembukuan langsung, tapi lewat resolusi URL
     * asli /transaksi/{tipe}) - pastikan data pembukuan lain tidak ikut kebocor.
     */
    public function test_url_transaksi_pembukuan_berbeda_menampilkan_data_yang_berbeda(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);
        $kategori = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);

        Transaksi::create([
            'pembukuan_id' => $pribadi->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 111000, 'tanggal' => now(),
            'keterangan' => 'punya pribadi',
        ]);
        Transaksi::create([
            'pembukuan_id' => $usaha->id, 'kategori_id' => $kategori->id,
            'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 222000, 'tanggal' => now(),
            'keterangan' => 'punya usaha',
        ]);

        $this->get('/transaksi/pribadi')
            ->assertSee('punya pribadi')
            ->assertDontSee('punya usaha');

        $this->get('/transaksi/usaha')
            ->assertSee('punya usaha')
            ->assertDontSee('punya pribadi');
    }

    public function test_url_hutang_piutang_pembukuan_berbeda_menampilkan_data_yang_berbeda(): void
    {
        $this->actingAs(User::factory()->create());
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        // bon pribadi <-> kantor, sama sekali gak melibatkan usaha
        HutangPiutang::create([
            'dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id,
            'jumlah' => 333000, 'tanggal' => now(), 'keterangan' => 'bon pribadi ke kantor',
        ]);

        // cuma sisi Hutang (penerima bon) yang ditampilkan - Kantor (berutang) lihat,
        // Pribadi (pemberi/piutang) dan Usaha (gak terlibat) sama-sama gak lihat
        $this->get('/hutang-piutang/kantor')->assertSee('bon pribadi ke kantor');
        $this->get('/hutang-piutang/pribadi')->assertDontSee('bon pribadi ke kantor');
        $this->get('/hutang-piutang/usaha')->assertDontSee('bon pribadi ke kantor');
    }
}
