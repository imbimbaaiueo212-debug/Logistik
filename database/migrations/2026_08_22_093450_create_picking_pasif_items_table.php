<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('picking_pasif_item', function (Blueprint $table) {   // singular biar konsisten
            $table->id();

            $table->unsignedBigInteger('picking_pasif_id');
            $table->unsignedBigInteger('product_id')->nullable();

            $table->string('item_name')->nullable();
            $table->string('item_sku')->nullable();
            $table->unsignedInteger('item_qty')->default(0);
            $table->unsignedInteger('qty_picked')->default(0);
            $table->boolean('cek')->default(false);

            $table->timestamps();

            $table->index('picking_pasif_id');
            $table->index('product_id');
            $table->index('item_sku');
        });

        // Foreign Key
        Schema::table('picking_pasif_item', function (Blueprint $table) {
            $table->foreign('picking_pasif_id')
                  ->references('id')
                  ->on('picking_pasif')          // ← singular
                  ->onDelete('cascade');

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')               // cek dulu apakah tabelnya "products" atau "product"
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picking_pasif_item');
    }
};