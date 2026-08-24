<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('picking_pasif', function (Blueprint $table) {   // singular juga biar konsisten
            $table->id();

            $table->unsignedBigInteger('realisasi_pasif_id');
            $table->unsignedBigInteger('jakarta_pasif_id')->nullable();

            // Data order
            $table->string('no_pl')->nullable();
            $table->string('id_pesan')->nullable();
            $table->string('kategori_order')->nullable();

            $table->date('tgl_order')->nullable();
            $table->date('tgl_picking')->nullable();
            $table->date('payment_date')->nullable();
            $table->date('waktu_estimasi_persiapan')->nullable();
            $table->time('jam_picking')->nullable();

            // Customer / Unit
            $table->string('vendor')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();

            // Alamat & Kontak
            $table->string('kirim')->nullable();
            $table->string('no_telpon')->nullable();
            $table->text('alamat_kirim')->nullable();
            $table->string('kab_kota_provinsi')->nullable();

            // Pengiriman
            $table->string('ekspedisi')->nullable();
            $table->string('service_pengiriman')->nullable();

            // Barang
            $table->text('pesanan')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('berat', 10, 2)->default(0);
            $table->unsignedInteger('total_item')->default(0);
            $table->unsignedInteger('total_qty')->default(0);

            // Status
            $table->string('status')->default('completed');
            $table->timestamp('printed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            // Index
            $table->index('realisasi_pasif_id');
            $table->index('jakarta_pasif_id');
            $table->index('status');
            $table->index('tgl_picking');
            $table->index('kategori_order');
        });

        // Foreign Key
        Schema::table('picking_pasif', function (Blueprint $table) {
            $table->foreign('realisasi_pasif_id')
                  ->references('id')
                  ->on('realisasi_pasif')          // ← singular
                  ->onDelete('cascade');

            $table->foreign('jakarta_pasif_id')
                  ->references('id')
                  ->on('jakarta_pasif')            // ← singular (kalau beda bilang)
                  ->onDelete('set null');

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('picking_pasif');
    }
};