<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesanan_majalah_kabupaten', function (Blueprint $table) {

            $table->id();

            // Relasi ke pesanan_majalah
            $table->foreignId('pesanan_majalah_id')
                ->constrained('pesanan_majalah')
                ->cascadeOnDelete();

            // Nama kabupaten
            // Contoh: KABUPATEN PURBALINGGA
            $table->string('nama_kabupaten');

            // Contact Person
            // Contoh: Ibu Amel
            // atau: 1. Ibu Ira / 2. Ibu Rimah
            $table->text('contact_person')->nullable();

            // Nomor telepon Contact Person
            $table->text('telepon_contact_person')->nullable();

            // Untuk mengatur urutan kabupaten
            $table->unsignedInteger('urutan')->default(0);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesanan_majalah_kabupaten');
    }
};