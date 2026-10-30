<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_kalender_unit_kotamadya', function (Blueprint $table) {

            $table->id();

            // Relasi ke pesanan_kalender_kotamadya
            $table->unsignedBigInteger('pesanan_kalender_kotamadya_id');

            // Foreign key
            $table->foreign(
                'pesanan_kalender_kotamadya_id',
                'pk_unit_kotamadya_kotamadya_fk'
            )
            ->references('id')
            ->on('pesanan_kalender_kotamadya')
            ->onDelete('cascade');

            // Nomor urut unit
            $table->unsignedInteger('no')->default(0);

            // Nama unit
            $table->string('nama_unit');

            // Nomor cabang
            $table->string('no_cabang')->nullable();

            // Jumlah pesanan kalender
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
        Schema::dropIfExists('pesanan_kalender_unit_kotamadya');
    }
};