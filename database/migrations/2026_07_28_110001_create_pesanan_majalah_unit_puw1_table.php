<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_majalah_unit_puw1', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELASI KE PERIODE PUW1
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('pesanan_majalah_puw1_id');

            $table->foreign(
                'pesanan_majalah_puw1_id',
                'pm_unit_puw1_puw1_fk'
            )
            ->references('id')
            ->on('pesanan_majalah_puw1')
            ->cascadeOnDelete();


            /*
            |--------------------------------------------------------------------------
            | DATA UNIT
            |--------------------------------------------------------------------------
            */

            // Nomor urut
            // Contoh: 1, 2, 3
            $table->unsignedInteger('no')->default(0);

            // Nama Unit
            // Contoh: Pisangan Lama
            $table->string('nama_unit');

            // Nomor Cabang
            // Contoh: 17
            $table->string('no_cabang')->nullable();

            // Kabupaten / Kota
            // Contoh: Kotamadya Jakarta Timur
            $table->string('kabupaten_kota')->nullable();

            // Jumlah pesanan
            // File Excel dapat memiliki angka desimal
            // Contoh: 134.4
            $table->decimal('jumlah_pesanan', 12, 2)->default(0);

            // Alamat unit
            $table->text('alamat_unit')->nullable();

            // Telepon unit
            $table->string('telepon')->nullable();

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_majalah_unit_puw1');
    }
};