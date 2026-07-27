<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_majalah', function (Blueprint $table) {

            $table->id();

            // Judul dokumen
            $table->string('judul')->nullable();

            // Contoh: JULI M159
            $table->string('bulan')->nullable();

            // Contoh: 2026
            $table->year('tahun')->nullable();

            // Contoh: 2026-07
            $table->string('periode', 7)->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_majalah');
    }
};