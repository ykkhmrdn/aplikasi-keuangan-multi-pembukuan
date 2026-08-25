<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['dari_pembukuan_id', 'ke_pembukuan_id', 'jumlah', 'tanggal', 'keterangan'])]
class TransferSaldo extends Model
{
    // kata bahasa Indonesia tidak dipluralkan otomatis dengan benar oleh Eloquent
    protected $table = 'transfer_saldo';

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'tanggal' => 'date',
        ];
    }

    public function dariPembukuan(): BelongsTo
    {
        return $this->belongsTo(Pembukuan::class, 'dari_pembukuan_id');
    }

    public function kePembukuan(): BelongsTo
    {
        return $this->belongsTo(Pembukuan::class, 'ke_pembukuan_id');
    }
}
