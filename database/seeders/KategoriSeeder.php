<?php

namespace Database\Seeders;

use App\Enums\TipeTransaksi;
use App\Models\Kategori;
use Illuminate\Database\Seeder;

/**
 * Seed kategori umum sebagai starting point, bisa ditambah/edit/hapus
 * sendiri oleh user di aplikasi (lihat docs/SCOPE_LOCK.md poin 2).
 * pembukuan_id null = kategori global, dipakai di semua pembukuan.
 */
class KategoriSeeder extends Seeder
{
    public function run(): void
    {
        $kategoriPemasukan = [
            'Gaji',
            'Bonus',
            'Hasil Usaha',
            'Pendapatan Lain-lain',
        ];

        $kategoriPengeluaran = [
            'Makan & Minum',
            'Transportasi',
            'Belanja',
            'Tagihan & Utilitas',
            'Kesehatan',
            'Hiburan',
            'Pendidikan',
            'Pengeluaran Lain-lain',
        ];

        foreach ($kategoriPemasukan as $nama) {
            $this->seedKategori($nama, TipeTransaksi::Pemasukan);
        }

        foreach ($kategoriPengeluaran as $nama) {
            $this->seedKategori($nama, TipeTransaksi::Pengeluaran);
        }
    }

    private function seedKategori(string $nama, TipeTransaksi $tipe): void
    {
        Kategori::query()->updateOrCreate(
            ['pembukuan_id' => null, 'nama' => $nama, 'tipe' => $tipe],
            []
        );
    }
}
