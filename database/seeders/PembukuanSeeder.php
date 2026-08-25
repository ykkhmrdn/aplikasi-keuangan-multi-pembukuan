<?php

namespace Database\Seeders;

use App\Enums\TipePembukuan;
use App\Models\Pembukuan;
use Illuminate\Database\Seeder;

/**
 * Seed 3 pembukuan tetap (Pribadi, Usaha, Kantor), tidak ada CRUD
 * tambah/hapus dari UI (lihat docs/SCOPE_LOCK.md).
 */
class PembukuanSeeder extends Seeder
{
    public function run(): void
    {
        foreach (TipePembukuan::cases() as $tipe) {
            Pembukuan::query()->updateOrCreate(
                ['tipe' => $tipe],
                ['nama' => $tipe->label()]
            );
        }
    }
}
