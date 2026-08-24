<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('realisasi_pasif', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('jakarta_pasif_id')->nullable()->index();
            $table->string('no_pl')->nullable()->index();
            $table->dateTime('tgl_turun_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('pengiriman')->nullable();
            $table->string('service_pengiriman')->nullable();
            $table->text('nama_barang')->nullable();
            $table->string('kategori_order')->nullable()->index(); // Modul / Majalah / Sertifikat
            $table->unsignedBigInteger('product_id')->nullable();
            $table->json('product_ids')->nullable();
            $table->dateTime('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->string('nama_stokis')->nullable();
            $table->dateTime('tgl_estimasi')->nullable();
            $table->integer('estimasi_hari')->nullable();
            $table->string('penyebut')->nullable();
            $table->string('pengambil')->nullable();
            $table->text('ket')->nullable();
            $table->decimal('order_weight', 12, 2)->default(0);
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->string('rekap_number')->nullable()->index();
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('picking_printed_at')->nullable();
            $table->timestamps();

            $table->foreign('jakarta_pasif_id')
                ->references('id')
                ->on('jakarta_pasif')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_pasif');
    }
};