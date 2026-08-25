<?php

namespace App\Models;

use App\Enums\TipeTransaksi;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pembukuan_id', 'kategori_id', 'tipe', 'jumlah', 'tanggal', 'keterangan'])]
class Transaksi extends Model
{
    // kata bahasa Indonesia tidak dipluralkan otomatis dengan benar oleh Eloquent
    protected $table = 'transaksi';

    protected function casts(): array
    {
        return [
            'tipe' => TipeTransaksi::class,
            'jumlah' => 'decimal:2',
            'tanggal' => 'date',
        ];
    }

    public function pembukuan(): BelongsTo
    {
        return $this->belongsTo(Pembukuan::class);
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(Kategori::class);
    }
}
