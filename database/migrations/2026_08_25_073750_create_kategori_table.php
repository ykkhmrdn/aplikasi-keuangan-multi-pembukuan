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
        Schema::create('kategori', function (Blueprint $table) {
            $table->id();
            // null = kategori global, dipakai di semua pembukuan
            $table->foreignId('pembukuan_id')->nullable()->constrained('pembukuan')->cascadeOnDelete();
            $table->string('nama');
            $table->enum('tipe', array_column(TipeTransaksi::cases(), 'value'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
