<?php

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
        Schema::create('pelunasan_hutang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hutang_piutang_id')->constrained('hutang_piutang')->cascadeOnDelete();
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
        Schema::dropIfExists('pelunasan_hutang');
    }
};
