<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_packings', function (Blueprint $table) {
            $table->id();

            // Relasi utama
            $table->unsignedBigInteger('manual_picking_id')->unique();
            $table->unsignedBigInteger('manual_qc_outgoing_id')->nullable();

            // Data dari Picking / QC Manual
            $table->string('no_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('grup')->nullable();
            $table->string('kategori_order')->nullable();
            $table->string('pic_picking')->nullable();

            // Field packing (mirip dengan model Packing biasa)
            $table->decimal('berat', 10, 2)->nullable();
            $table->decimal('berat_aktual', 10, 2)->nullable();
            $table->date('tgl_packing')->nullable();
            $table->string('nama_packer')->nullable();
            $table->string('koli')->nullable();
            $table->enum('status_packing', ['Pending', 'Proses', 'Selesai', 'Batal'])->default('Pending');
            $table->text('keterangan_packing')->nullable();

            // Tracking
            $table->unsignedBigInteger('packing_by')->nullable();
            $table->timestamp('packing_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();

            // Foreign key
            $table->foreign('manual_picking_id')
                  ->references('id')
                  ->on('manual_pickings')
                  ->onDelete('cascade');

            $table->foreign('manual_qc_outgoing_id')
                  ->references('id')
                  ->on('manual_qc_outgoings')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_packings');
    }
};