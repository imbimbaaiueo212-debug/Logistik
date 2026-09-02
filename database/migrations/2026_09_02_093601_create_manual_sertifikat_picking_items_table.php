<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_sertifikat_picking_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('manual_sertifikat_picking_id')->nullable();
            $table->index('manual_sertifikat_picking_id', 'ms_picking_items_picking_id_idx'); // ← nama pendek

            $table->string('item_sku', 100)->nullable();
            $table->string('item_name', 255)->nullable();
            $table->unsignedInteger('qty')->default(1);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_sertifikat_picking_items');
    }
};