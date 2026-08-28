<?php

namespace Tests\Feature;

use App\Enums\TipeTransaksi;
use App\Models\HutangPiutang;
use App\Models\Kategori;
use App\Models\PelunasanHutang;
use App\Models\Pembukuan;
use App\Models\Transaksi;
use App\Models\TransferSaldo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifikasi manual saldo lintas 3 pembukuan sekaligus (transaksi + transfer +
 * hutang-piutang + pelunasan campur), dibandingkan dengan hitungan tangan -
 * bagian dari QA pre-deploy 28 Agt 2026 (lihat docs/DECISION_LOG.md).
 */
class SaldoVerifikasiManualTest extends TestCase
{
    use RefreshDatabase;

    public function test_saldo_akurat_untuk_skenario_campuran_lintas_pembukuan(): void
    {
        $pribadi = Pembukuan::create(['nama' => 'Pribadi', 'tipe' => 'pribadi']);
        $usaha = Pembukuan::create(['nama' => 'Usaha', 'tipe' => 'usaha']);
        $kantor = Pembukuan::create(['nama' => 'Kantor', 'tipe' => 'kantor']);

        $katMasuk = Kategori::create(['nama' => 'Gaji', 'tipe' => TipeTransaksi::Pemasukan]);
        $katKeluar = Kategori::create(['nama' => 'Belanja', 'tipe' => TipeTransaksi::Pengeluaran]);

        // Pribadi: +1.000.000 pemasukan, -200.000 pengeluaran
        Transaksi::create(['pembukuan_id' => $pribadi->id, 'kategori_id' => $katMasuk->id, 'tipe' => TipeTransaksi::Pemasukan, 'jumlah' => 1000000, 'tanggal' => now()]);
        Transaksi::create(['pembukuan_id' => $pribadi->id, 'kategori_id' => $katKeluar->id, 'tipe' => TipeTransaksi::Pengeluaran, 'jumlah' => 200000, 'tanggal' => now()]);
        // Transfer Pribadi -> Usaha 300.000
        TransferSaldo::create(['dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $usaha->id, 'jumlah' => 300000, 'tanggal' => now()]);
        // Pribadi kasih bon ke Kantor 150.000
        $hp = HutangPiutang::create(['dari_pembukuan_id' => $pribadi->id, 'ke_pembukuan_id' => $kantor->id, 'jumlah' => 150000, 'tanggal' => now()]);
        // Kantor lunasi sebagian 50.000
        PelunasanHutang::create(['hutang_piutang_id' => $hp->id, 'jumlah' => 50000, 'tanggal' => now()]);

        // hitungan tangan:
        // Pribadi = +1.000.000 -200.000 -300.000(transfer keluar) -150.000(bon diberikan) +50.000(terima pelunasan) = 400.000
        // Usaha   = +300.000(transfer masuk) = 300.000
        // Kantor  = +150.000(bon diterima) -50.000(bayar utang) = 100.000
        $this->assertEquals('400000.00', $pribadi->fresh()->saldo());
        $this->assertEquals('300000.00', $usaha->fresh()->saldo());
        $this->assertEquals('100000.00', $kantor->fresh()->saldo());

        // total uang di sistem harus tetap sama sebelum & sesudah (konservasi -
        // gak ada uang nongol/ilang gara-gara transfer/bon/pelunasan)
        $totalSaldo = bcadd(bcadd($pribadi->fresh()->saldo(), $usaha->fresh()->saldo(), 2), $kantor->fresh()->saldo(), 2);
        $totalMasukBersih = bcsub('1000000.00', '200000.00', 2); // cuma transaksi asli yang nambah/ngurangin total uang beneran
        $this->assertEquals($totalMasukBersih, $totalSaldo);
    }
}
