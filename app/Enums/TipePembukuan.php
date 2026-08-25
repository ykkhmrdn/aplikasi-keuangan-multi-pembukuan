<?php

namespace App\Enums;

/**
 * Tipe pembukuan, fixed 3 jenis sesuai scope (lihat docs/SCOPE_LOCK.md).
 * Tidak ada CRUD tambah/hapus pembukuan dari UI.
 */
enum TipePembukuan: string
{
    case Pribadi = 'pribadi';
    case Usaha = 'usaha';
    case Kantor = 'kantor';

    public function label(): string
    {
        return match ($this) {
            self::Pribadi => 'Pribadi',
            self::Usaha => 'Usaha',
            self::Kantor => 'Kantor',
        };
    }
}
