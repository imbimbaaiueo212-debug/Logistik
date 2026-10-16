<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jakarta_aktif_items', function (Blueprint $table) {

            $table->id();

            // Header Order
            $table->foreignId('jakarta_aktif_id')
                ->constrained('jakarta_aktif')
                ->cascadeOnDelete();

            // Master Product
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            // Data dari Bimbashop
            $table->string('sku')->nullable();
            $table->string('label')->nullable();
            $table->string('nama_produk')->nullable();

            // Qty
            $table->integer('qty')->default(1);

            // Harga
            $table->decimal('harga',15,2)->default(0);

            $table->decimal('subtotal',15,2)->default(0);

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jakarta_aktif_items');
    }
};