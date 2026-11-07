<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Spare Pasif 3% Kalender: berdiri sendiri, diinput manual (tidak dari import Kalender)
        Schema::create('kalender_spare_manual', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 100)->nullable()->index();   // dikosongkan: diambil dari biMBA Shop
            $table->string('id_manual', 50)->unique();              // dibuat sendiri / otomatis (KSP0001)
            $table->date('tgl_order');
            $table->string('nama');
            $table->string('sku', 100);                              // barang / SKU, mis. KLD27
            $table->string('edisi', 10)->nullable()->index();        // diturunkan dari SKU (KLD27)
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kalender_spare_manual');
    }
};
