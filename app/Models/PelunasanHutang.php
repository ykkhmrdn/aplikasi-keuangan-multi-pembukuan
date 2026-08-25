<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['hutang_piutang_id', 'jumlah', 'tanggal', 'keterangan'])]
class PelunasanHutang extends Model
{
    // kata bahasa Indonesia tidak dipluralkan otomatis dengan benar oleh Eloquent
    protected $table = 'pelunasan_hutang';

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal' => 'date',
        ];
    }

    public function hutangPiutang(): BelongsTo
    {
        return $this->belongsTo(HutangPiutang::class);
    }
}
