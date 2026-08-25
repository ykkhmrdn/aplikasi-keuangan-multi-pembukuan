<?php

namespace App\Models;

use App\Enums\TipePembukuan;
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
}
