<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('qc_outgoing_pasif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('picking_pasif_id')->nullable()->index();
            $table->string('no_pl')->nullable()->index();
            $table->date('tgl_turun_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('pengiriman')->nullable();
            $table->string('nama_barang')->nullable();
            $table->date('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->nullable()->default(0);
            $table->date('tgl_estimasi')->nullable();
            $table->string('nama_stokis')->nullable();
            $table->string('estimasi_hari')->nullable();
            $table->string('kode_qc')->nullable();
            $table->timestamp('tgl_qc')->nullable();
            $table->string('status_qc')->default('Pending');
            $table->string('pic_qc')->nullable();
            $table->text('keterangan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qc_outgoing_pasif');
    }
};