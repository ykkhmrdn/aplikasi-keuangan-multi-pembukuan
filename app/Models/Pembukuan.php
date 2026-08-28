<?php

namespace App\Models;

use App\Enums\StatusHutangPiutang;
use App\Enums\TipePembukuan;
use App\Enums\TipeTransaksi;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pembukuan fixed 3 baris (Pribadi, Usaha, Kantor), tidak ada CRUD
 * tambah/hapus dari UI (lihat docs/SCOPE_LOCK.md).
 */
#[Fillable(['nama', 'tipe'])]
class Pembukuan extends Model
{
    // kata bahasa Indonesia tidak dipluralkan otomatis dengan benar oleh Eloquent
    protected $table = 'pembukuan';

    protected function casts(): array
    {
        return [
            'tipe' => TipePembukuan::class,
        ];
    }

    /** Route model binding pakai tipe (pribadi/usaha/kantor), bukan id, biar URL rapi. */
    public function getRouteKeyName(): string
    {
        return 'tipe';
    }

    public function kategori(): HasMany
    {
        return $this->hasMany(Kategori::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    /** Transfer saldo keluar dari pembukuan ini. */
    public function transferKeluar(): HasMany
    {
        return $this->hasMany(TransferSaldo::class, 'dari_pembukuan_id');
    }

    /** Transfer saldo masuk ke pembukuan ini. */
    public function transferMasuk(): HasMany
    {
        return $this->hasMany(TransferSaldo::class, 'ke_pembukuan_id');
    }

    /** Bon yang diberikan pembukuan ini ke pembukuan lain (piutang). */
    public function hutangDiberikan(): HasMany
    {
        return $this->hasMany(HutangPiutang::class, 'dari_pembukuan_id');
    }

    /** Bon yang diterima pembukuan ini dari pembukuan lain (hutang). */
    public function hutangDiterima(): HasMany
    {
        return $this->hasMany(HutangPiutang::class, 'ke_pembukuan_id');
    }

    /**
     * Saldo dihitung dinamis, bukan kolom cache (lihat docs/DATABASE_DESIGN.md
     * bagian "Formula Saldo" untuk rincian lengkap tiap komponen).
     */
    public function saldo(): string
    {
        $pemasukan = (string) $this->transaksi()->where('tipe', TipeTransaksi::Pemasukan)->sum('jumlah');
        $pengeluaran = (string) $this->transaksi()->where('tipe', TipeTransaksi::Pengeluaran)->sum('jumlah');
        $transferMasuk = (string) $this->transferMasuk()->sum('jumlah');
        $transferKeluar = (string) $this->transferKeluar()->sum('jumlah');
        // terima bon = kas masuk, kasih bon = kas keluar
        $bonDiterima = (string) $this->hutangDiterima()->sum('jumlah');
        $bonDiberikan = (string) $this->hutangDiberikan()->sum('jumlah');
        // bayar utang (pelunasan atas bon yang kita terima) = kas keluar
        $bayarUtang = (string) PelunasanHutang::whereHas(
            'hutangPiutang', fn ($query) => $query->where('ke_pembukuan_id', $this->id)
        )->sum('jumlah');
        // terima pelunasan (atas bon yang kita berikan) = kas masuk
        $terimaPelunasan = (string) PelunasanHutang::whereHas(
            'hutangPiutang', fn ($query) => $query->where('dari_pembukuan_id', $this->id)
        )->sum('jumlah');

        $masuk = bcadd(bcadd($pemasukan, $transferMasuk, 2), bcadd($bonDiterima, $terimaPelunasan, 2), 2);
        $keluar = bcadd(bcadd($pengeluaran, $transferKeluar, 2), bcadd($bonDiberikan, $bayarUtang, 2), 2);

        return bcsub($masuk, $keluar, 2);
    }

    /** Total hutang outstanding (bon yang diterima, belum dilunasi ke lawan). */
    public function hutangOutstanding(): string
    {
        return $this->hutangDiterima()
            ->where('status', StatusHutangPiutang::BelumLunas)
            ->get()
            ->reduce(fn ($total, HutangPiutang $hp) => bcadd($total, $hp->sisaOutstanding(), 2), '0.00');
    }
}
