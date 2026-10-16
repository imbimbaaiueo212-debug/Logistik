<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packing_pasif', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->unsignedBigInteger('picking_pasif_id')->nullable()->index();
            $table->unsignedBigInteger('qc_outgoing_pasif_id')->nullable()->index();

            // Data utama (mirip Packing Aktif)
            $table->string('no_pl')->nullable()->index();
            $table->string('no_ps')->nullable();
            $table->date('tgl_turun_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('pengiriman')->nullable();
            $table->string('nama_barang')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->nullable()->default(0);
            $table->date('tgl_estimasi')->nullable();
            $table->decimal('berat', 10, 2)->nullable();

            // Status Packing
            $table->string('status_packing')->default('Pending'); // Pending / Proses / Selesai
            $table->string('pic_packing')->nullable();
            $table->timestamp('packing_at')->nullable();
            $table->unsignedBigInteger('packing_by')->nullable();

            // Tracking
            $table->string('kode_packing')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packing_pasif');
    }
};