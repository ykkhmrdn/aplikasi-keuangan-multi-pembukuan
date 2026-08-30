<?php

namespace App\Livewire;

use App\Enums\StatusHutangPiutang;
use App\Enums\TipeTransaksi;
use App\Models\Kategori;
use App\Models\Pembukuan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Dashboard: kartu saldo tiap pembukuan, detail hutang outstanding per item,
 * dan analisis pengeluaran per kategori dengan filter periode (lihat
 * docs/TODO.md, docs/DATABASE_DESIGN.md, docs/DECISION_LOG.md).
 */
#[Layout('layouts.app')]
class Dashboard extends Component
{
    public int $pembukuanTerpilihId;

    /** Preset periode analisis: harian/mingguan/bulanan/tahunan/semua/custom. */
    public string $periode = 'bulanan';

    /** Dipakai kalau periode = custom (rentang tanggal bebas). */
    public string $tanggalDari = '';

    public string $tanggalSampai = '';

    public function mount(): void
    {
        $this->pembukuanTerpilihId = Pembukuan::orderBy('id')->value('id');
    }

    /**
     * ID yang dikirim divalidasi dulu - kalau bukan salah satu dari 3 pembukuan
     * yang ada, diabaikan (bukan diproses jadi error). Tanpa ini, ID sembarangan
     * (mis. dikirim manual ke endpoint Livewire, bukan lewat klik kartu di UI)
     * bikin render() fatal error karena firstWhere() dapat null - ditemukan &
     * diperbaiki di audit QA 29 Agt 2026, lihat docs/DECISION_LOG.md.
     */
    public function pilihPembukuan(int $id): void
    {
        if (Pembukuan::whereKey($id)->exists()) {
            $this->pembukuanTerpilihId = $id;
        }
    }

    public function updatedPeriode(): void
    {
        // ganti dari custom ke preset lain: bersihin rentang custom biar gak nyangkut
        if ($this->periode !== 'custom') {
            $this->tanggalDari = '';
            $this->tanggalSampai = '';
        }
    }

    public function render()
    {
        $pembukuanList = Pembukuan::orderBy('id')->get();
        $pembukuanTerpilih = $pembukuanList->firstWhere('id', $this->pembukuanTerpilihId);

        return view('livewire.dashboard', [
            'pembukuanList' => $pembukuanList,
            'pembukuanTerpilih' => $pembukuanTerpilih,
            'hutangOutstanding' => $pembukuanTerpilih->hutangOutstanding(),
            'hutangDetail' => $this->hutangDetail($pembukuanTerpilih),
            'analisisPengeluaran' => $this->analisisPengeluaran($pembukuanTerpilih),
        ]);
    }

    /**
     * Daftar hutang outstanding pembukuan ini (bon yang diterima, belum lunas),
     * lengkap sama dari pembukuan mana asalnya - dipakai buat detail per-item
     * di kartu Hutang (client minta di meeting 28 Agt 2026, gak cukup cuma total).
     */
    private function hutangDetail(Pembukuan $pembukuan): Collection
    {
        return $pembukuan->hutangDiterima()
            ->where('status', StatusHutangPiutang::BelumLunas)
            ->with(['dariPembukuan', 'pelunasan'])
            ->orderByDesc('tanggal')
            ->get();
    }

    /**
     * Rentang tanggal dari periode terpilih. null = tanpa batas (dipakai buat
     * preset "semua waktu" atau custom yang belum diisi).
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function rentangTanggal(): array
    {
        $now = now();

        return match ($this->periode) {
            'harian' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'mingguan' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'bulanan' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'tahunan' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
            'custom' => [
                $this->tanggalDari !== '' ? Carbon::parse($this->tanggalDari)->startOfDay() : null,
                $this->tanggalSampai !== '' ? Carbon::parse($this->tanggalSampai)->endOfDay() : null,
            ],
            default => [null, null], // 'semua'
        };
    }

    /**
     * Breakdown pengeluaran per kategori buat pembukuan + periode terpilih.
     *
     * Query mulai DARI Kategori (bukan dari Transaksi di-group), supaya kategori
     * baru yang belum pernah dipakai tetap muncul di daftar sebagai Rp0 - ini
     * eksplisit diminta client di meeting 28 Agt 2026.
     */
    private function analisisPengeluaran(Pembukuan $pembukuan): Collection
    {
        [$dari, $sampai] = $this->rentangTanggal();

        $kategoriList = Kategori::where('tipe', TipeTransaksi::Pengeluaran)
            ->where(function ($query) use ($pembukuan) {
                $query->whereNull('pembukuan_id')->orWhere('pembukuan_id', $pembukuan->id);
            })
            ->get();

        $totalPerKategori = $pembukuan->transaksi()
            ->where('tipe', TipeTransaksi::Pengeluaran)
            ->whereIn('kategori_id', $kategoriList->pluck('id'))
            ->when($dari, fn ($query) => $query->where('tanggal', '>=', $dari))
            ->when($sampai, fn ($query) => $query->where('tanggal', '<=', $sampai))
            ->selectRaw('kategori_id, SUM(jumlah) as total')
            ->groupBy('kategori_id')
            ->pluck('total', 'kategori_id');

        $totalKeseluruhan = $totalPerKategori->reduce(
            fn ($total, $jumlah) => bcadd($total, (string) $jumlah, 2), '0.00'
        );

        return $kategoriList->map(function (Kategori $kategori) use ($totalPerKategori, $totalKeseluruhan) {
            $jumlah = (string) ($totalPerKategori[$kategori->id] ?? '0.00');
            $persen = bccomp($totalKeseluruhan, '0', 2) > 0
                ? round((float) bcdiv($jumlah, $totalKeseluruhan, 6) * 100, 1)
                : 0.0;

            return [
                'kategori' => $kategori->nama,
                'jumlah' => $jumlah,
                'persen' => $persen,
            ];
        })
            ->sortByDesc(fn ($item) => (float) $item['jumlah'])
            ->values();
    }
}
