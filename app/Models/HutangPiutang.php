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

    /**
     * Sisa yang belum dilunasi, dihitung dari jumlah awal dikurangi total pelunasan.
     *
     * Kalau relasi pelunasan sudah di-eager-load (mis. lewat with() di halaman list),
     * jumlahkan dari collection yang sudah ada (pakai bcadd biar presisi desimal tetap
     * terjaga, bukan Collection::sum() yang bisa kena floating point) supaya tidak query
     * ulang per baris (N+1). Kalau belum di-load, query sum() langsung ke DB seperti biasa.
     */
    public function sisaOutstanding(): string
    {
        if ($this->relationLoaded('pelunasan')) {
            $totalPelunasan = $this->pelunasan->reduce(
                fn (string $carry, PelunasanHutang $item) => bcadd($carry, (string) $item->jumlah, 2),
                '0.00'
            );
        } else {
            $totalPelunasan = (string) $this->pelunasan()->sum('jumlah');
        }

        return bcsub((string) $this->jumlah, $totalPelunasan, 2);
    }
}
