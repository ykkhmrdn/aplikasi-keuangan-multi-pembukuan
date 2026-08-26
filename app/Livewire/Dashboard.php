<?php

namespace App\Livewire;

use App\Enums\TipeTransaksi;
use App\Models\PelunasanHutang;
use App\Models\Pembukuan;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Dashboard: kartu saldo tiap pembukuan, riwayat gabungan (transaksi +
 * transfer + hutang-piutang + pelunasan) per pembukuan, dan ringkasan
 * hutang-piutang outstanding (lihat docs/TODO.md, docs/DATABASE_DESIGN.md).
 */
#[Layout('layouts.app')]
class Dashboard extends Component
{
    public int $pembukuanTerpilihId;

    /** Berapa item riwayat terbaru yang ditampilkan, biar dashboard tetap ringkas. */
    private const JUMLAH_RIWAYAT = 15;

    public function mount(): void
    {
        $this->pembukuanTerpilihId = Pembukuan::orderBy('id')->value('id');
    }

    public function pilihPembukuan(int $id): void
    {
        $this->pembukuanTerpilihId = $id;
    }

    public function render()
    {
        $pembukuanList = Pembukuan::orderBy('id')->get();
        $pembukuanTerpilih = $pembukuanList->firstWhere('id', $this->pembukuanTerpilihId);

        return view('livewire.dashboard', [
            'pembukuanList' => $pembukuanList,
            'pembukuanTerpilih' => $pembukuanTerpilih,
            'riwayat' => $this->riwayatGabungan($pembukuanTerpilih),
            'piutangOutstanding' => $pembukuanTerpilih->piutangOutstanding(),
            'hutangOutstanding' => $pembukuanTerpilih->hutangOutstanding(),
        ]);
    }

    /**
     * Gabungkan transaksi, transfer saldo, dan hutang-piutang (+ pelunasan)
     * jadi satu riwayat urut tanggal, tanpa menggabungkan data di level
     * penyimpanan (lihat docs/DECISION_LOG.md).
     *
     * Tiap sumber dibatasi orderByDesc+limit(JUMLAH_RIWAYAT) sebelum digabung,
     * bukan ambil semua baris lalu potong belakangan — supaya query tetap
     * ringan walau riwayat pembukuan sudah ratusan/ribuan baris (hasil akhir
     * cuma butuh 15 teratas, dan 15 teratas gabungan pasti berasal dari 15
     * teratas salah satu sumber, jadi aman dipotong di level query).
     */
    private function riwayatGabungan(Pembukuan $pembukuan): Collection
    {
        $terbaru = fn ($query) => $query->orderByDesc('tanggal')->orderByDesc('id')->limit(self::JUMLAH_RIWAYAT);

        $transaksi = $terbaru($pembukuan->transaksi()->with('kategori'))->get()->map(fn ($t) => [
            'tanggal' => $t->tanggal,
            'deskripsi' => $t->kategori->nama,
            'jumlah' => $t->jumlah,
            'arah' => $t->tipe === TipeTransaksi::Pemasukan ? 'masuk' : 'keluar',
        ]);

        $transferKeluar = $terbaru($pembukuan->transferKeluar()->with('kePembukuan'))->get()->map(fn ($t) => [
            'tanggal' => $t->tanggal,
            'deskripsi' => "Transfer ke {$t->kePembukuan->nama}",
            'jumlah' => $t->jumlah,
            'arah' => 'keluar',
        ]);

        $transferMasuk = $terbaru($pembukuan->transferMasuk()->with('dariPembukuan'))->get()->map(fn ($t) => [
            'tanggal' => $t->tanggal,
            'deskripsi' => "Transfer dari {$t->dariPembukuan->nama}",
            'jumlah' => $t->jumlah,
            'arah' => 'masuk',
        ]);

        $bonDiberikan = $terbaru($pembukuan->hutangDiberikan()->with('kePembukuan'))->get()->map(fn ($hp) => [
            'tanggal' => $hp->tanggal,
            'deskripsi' => "Bon ke {$hp->kePembukuan->nama}",
            'jumlah' => $hp->jumlah,
            'arah' => 'keluar',
        ]);

        $bonDiterima = $terbaru($pembukuan->hutangDiterima()->with('dariPembukuan'))->get()->map(fn ($hp) => [
            'tanggal' => $hp->tanggal,
            'deskripsi' => "Bon dari {$hp->dariPembukuan->nama}",
            'jumlah' => $hp->jumlah,
            'arah' => 'masuk',
        ]);

        // pelunasan atas bon yang kita berikan = kita terima uang (kas masuk)
        $pelunasanDiterima = $terbaru(PelunasanHutang::whereHas(
            'hutangPiutang', fn ($q) => $q->where('dari_pembukuan_id', $pembukuan->id)
        )->with('hutangPiutang.kePembukuan'))->get()->map(fn ($pl) => [
            'tanggal' => $pl->tanggal,
            'deskripsi' => "Pelunasan dari {$pl->hutangPiutang->kePembukuan->nama}",
            'jumlah' => $pl->jumlah,
            'arah' => 'masuk',
        ]);

        // pelunasan atas bon yang kita terima = kita bayar (kas keluar)
        $pelunasanDibayar = $terbaru(PelunasanHutang::whereHas(
            'hutangPiutang', fn ($q) => $q->where('ke_pembukuan_id', $pembukuan->id)
        )->with('hutangPiutang.dariPembukuan'))->get()->map(fn ($pl) => [
            'tanggal' => $pl->tanggal,
            'deskripsi' => "Pelunasan ke {$pl->hutangPiutang->dariPembukuan->nama}",
            'jumlah' => $pl->jumlah,
            'arah' => 'keluar',
        ]);

        return $transaksi
            ->concat($transferKeluar)
            ->concat($transferMasuk)
            ->concat($bonDiberikan)
            ->concat($bonDiterima)
            ->concat($pelunasanDiterima)
            ->concat($pelunasanDibayar)
            ->sortByDesc('tanggal')
            ->take(self::JUMLAH_RIWAYAT)
            ->values();
    }
}
