<?php

namespace App\Models;

use App\Enums\StatusHutangPiutang;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['dari_pembukuan_id', 'ke_pembukuan_id', 'jumlah', 'tanggal', 'status', 'tanggal_lunas', 'keterangan'])]
class HutangPiutang extends Model
{
    // kata bahasa Indonesia tidak dipluralkan otomatis dengan benar oleh Eloquent
    protected $table = 'hutang_piutang';

    // default status di level model, supaya langsung terisi di object PHP
    // tanpa perlu refresh dari DB dulu (default DB cuma kepakai kalau kolom
    // tidak disebut sama sekali saat insert)
    protected $attributes = [
        'status' => StatusHutangPiutang::BelumLunas->value,
    ];

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal' => 'date',
            'status' => StatusHutangPiutang::class,
            'tanggal_lunas' => 'date',
        ];
    }

    /** Pemberi bon / pihak yang berpiutang. */
    public function dariPembukuan(): BelongsTo
    {
        return $this->belongsTo(Pembukuan::class, 'dari_pembukuan_id');
    }

    /** Penerima bon / pihak yang berutang. */
    public function kePembukuan(): BelongsTo
    {
        return $this->belongsTo(Pembukuan::class, 'ke_pembukuan_id');
    }

    public function pelunasan(): HasMany
    {
        return $this->hasMany(PelunasanHutang::class);
    }

    /** Sisa yang belum dilunasi, dihitung dari jumlah awal dikurangi total pelunasan. */
    public function sisaOutstanding(): string
    {
        return bcsub((string) $this->jumlah, (string) $this->pelunasan()->sum('jumlah'), 2);
    }
}
