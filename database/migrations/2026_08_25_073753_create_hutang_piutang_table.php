<?php

use App\Enums\StatusHutangPiutang;
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
        Schema::create('hutang_piutang', function (Blueprint $table) {
            $table->id();
            // pemberi bon / pihak yang berpiutang
            $table->foreignId('dari_pembukuan_id')->constrained('pembukuan')->restrictOnDelete();
            // penerima bon / pihak yang berutang
            $table->foreignId('ke_pembukuan_id')->constrained('pembukuan')->restrictOnDelete();
            $table->decimal('jumlah', total: 15, places: 2);
            $table->date('tanggal');
            $table->enum('status', array_column(StatusHutangPiutang::cases(), 'value'))
                ->default(StatusHutangPiutang::BelumLunas->value);
            $table->date('tanggal_lunas')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hutang_piutang');
    }
};
