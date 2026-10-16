<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('picking_items', function (Blueprint $table) {

    $table->id();

    $table->foreignId('picking_id')
          ->constrained('pickings')
          ->cascadeOnDelete();

    $table->string('item_name');

    $table->string('item_sku')->nullable();

    $table->integer('item_qty')->default(1);

    $table->integer('qty_picked')->default(0);

    $table->boolean('cek')->default(false);

    $table->timestamps();

});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('picking_items');
    }
};
