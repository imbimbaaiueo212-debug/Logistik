<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_majalah_unit', function (Blueprint $table) {

            $table->id();

            // Relasi ke kabupaten
            $table->foreignId('pesanan_majalah_kabupaten_id')
                ->constrained('pesanan_majalah_kabupaten')
                ->cascadeOnDelete();

            // Nomor urut unit
            // Contoh: 1, 2, 3
            $table->unsignedInteger('no')->default(0);

            // Nama unit
            // Contoh: Karang Talun
            $table->string('nama_unit');

            // Nomor cabang
            // Contoh: 919
            $table->string('no_cabang')->nullable();

            // Jumlah pesanan majalah
            // Contoh: 19
            $table->unsignedInteger('jumlah_pesanan')->default(0);

            // Alamat unit
            $table->text('alamat_unit')->nullable();

            // Nomor telepon unit
            $table->string('telepon')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_majalah_unit');
    }
};