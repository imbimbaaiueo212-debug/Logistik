<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('jakarta_pasif_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('jakarta_pasif_id')->constrained('jakarta_pasif')->onDelete('cascade');
        $table->unsignedBigInteger('product_id')->nullable();
        $table->string('sku')->nullable();
        $table->string('label')->nullable();
        $table->string('nama_produk')->nullable();
        $table->integer('qty')->default(1);
        $table->decimal('harga', 15, 2)->default(0);
        $table->decimal('subtotal', 15, 2)->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jakarta_pasif_items');
    }
};
