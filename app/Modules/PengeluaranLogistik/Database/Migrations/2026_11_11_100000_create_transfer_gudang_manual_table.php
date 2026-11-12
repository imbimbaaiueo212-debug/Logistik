<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_gudang_manual', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal');
            $table->string('dari', 100)->default('Logistik');
            $table->string('tujuan', 150);                 // gudang / pihak tujuan transfer
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('nama_barang', 255);            // snapshot nama produk
            $table->string('sku', 100)->nullable();        // snapshot label produk
            $table->string('kategori', 150)->nullable();   // snapshot products.kategori
            $table->unsignedInteger('qty');                // hanya qty, tidak ada nominal uang
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('dibuat_oleh')->nullable();
            $table->timestamps();

            $table->index('tanggal');
            $table->index('tujuan');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_gudang_manual');
    }
};
