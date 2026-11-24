<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penyesuaian_harga', function (Blueprint $table) {
            $table->id();
            $table->string('grup', 60);                       // Modul & Raport / Seragam, Tas & KPK
            $table->unsignedSmallInteger('no');               // nomor urut di PDF
            $table->string('nama', 200);                      // nama produk di PDF
            $table->decimal('harga_2024', 15, 2);
            $table->decimal('harga_2026', 15, 2);
            $table->unsignedBigInteger('product_id')->nullable()->index(); // pemetaan ke Master Product
            $table->decimal('harga_beli_sebelum', 15, 2)->nullable();      // untuk fitur kembalikan
            $table->decimal('harga_jual_sebelum', 15, 2)->nullable();
            $table->timestamp('diterapkan_pada')->nullable();
            $table->timestamps();

            $table->unique(['grup', 'no']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penyesuaian_harga');
    }
};
