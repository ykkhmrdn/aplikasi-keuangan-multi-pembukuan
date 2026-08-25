<?php

use App\Enums\TipeTransaksi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembukuan_id')->constrained('pembukuan')->cascadeOnDelete();
            $table->foreignId('kategori_id')->constrained('kategori')->restrictOnDelete();
            // disimpan terpisah dari kategori.tipe supaya riwayat transaksi lama
            // tidak berubah makna kalau tipe kategori pernah diedit (lihat DATABASE_DESIGN.md)
            $table->enum('tipe', array_column(TipeTransaksi::cases(), 'value'));
            $table->decimal('jumlah', total: 15, places: 2);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
