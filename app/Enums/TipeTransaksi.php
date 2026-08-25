<?php

namespace App\Enums;

/**
 * Tipe pemasukan/pengeluaran, dipakai di kategori dan transaksi.
 */
enum TipeTransaksi: string
{
    case Pemasukan = 'pemasukan';
    case Pengeluaran = 'pengeluaran';

    public function label(): string
    {
        return match ($this) {
            self::Pemasukan => 'Pemasukan',
            self::Pengeluaran => 'Pengeluaran',
        };
    }
}
