<?php

namespace App\Enums;

/**
 * Status pelunasan hutang-piutang, dihitung ulang otomatis tiap ada pelunasan
 * (lihat docs/DATABASE_DESIGN.md).
 */
enum StatusHutangPiutang: string
{
    case BelumLunas = 'belum_lunas';
    case Lunas = 'lunas';

    public function label(): string
    {
        return match ($this) {
            self::BelumLunas => 'Belum Lunas',
            self::Lunas => 'Lunas',
        };
    }
}
