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
        Schema::create('transfer_saldo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dari_pembukuan_id')->constrained('pembukuan')->restrictOnDelete();
            // beda dengan dari_pembukuan_id divalidasi di form request, bukan constraint DB
            // (lihat DATABASE_DESIGN.md poin 4)
            $table->foreignId('ke_pembukuan_id')->constrained('pembukuan')->restrictOnDelete();
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
        Schema::dropIfExists('transfer_saldo');
    }
};
