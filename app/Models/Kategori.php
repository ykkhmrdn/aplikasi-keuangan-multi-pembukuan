<?php

namespace App\Models;

use App\Enums\TipeTransaksi;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Kategori transaksi, bisa ditambah/edit/hapus sendiri oleh user.
 * pembukuan_id null = kategori global, dipakai di semua pembukuan.
 */
#[Fillable(['pembukuan_id', 'nama', 'tipe'])]
class Kategori extends Model
{
    // kata bahasa Indonesia tidak dipluralkan otomatis dengan benar oleh Eloquent
    protected $table = 'kategori';

    protected function casts(): array
    {
        return [
            'tipe' => TipeTransaksi::class,
        ];
    }

    public function pembukuan(): BelongsTo
    {
        return $this->belongsTo(Pembukuan::class);
    }

    public function transaksi(): HasMany
    {
        return $this->hasMany(Transaksi::class);
    }

    /**
     * Kategori hanya bisa dihapus kalau belum dipakai di transaksi manapun
     * (lihat docs/DATABASE_DESIGN.md).
     */
    public function sudahDipakai(): bool
    {
        return $this->transaksi()->exists();
    }
}
