<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('jakarta_aktif', function (Blueprint $table) {
            $table->id();

            // ===================================
            // 1. Tanggal & Input Dasar
            // ===================================
            $table->date('tgl_input')->nullable();
            $table->date('tgl_pesan')->nullable();

            // ===================================
            // 2. Informasi Penerima & Pengiriman
            // ===================================
            $table->string('kirim')->nullable();
            $table->string('no_telpon')->nullable();
            $table->text('alamat_kirim')->nullable();
            $table->string('kab_kota_provinsi')->nullable();
            $table->string('ekspedisi')->nullable();
            $table->decimal('ongkir', 15, 2)->default(0);
            $table->string('service_pengiriman')->nullable();
            $table->string('tracking_number')->nullable();   // No Resi

            // ===================================
            // 3. Validasi & Pembayaran
            // ===================================
            $table->string('validasi')->nullable();
            $table->string('jenis_bank')->nullable();
            $table->string('status_pembayaran')->nullable();

            // ===================================
            // 4. Order & Unit Information
            // ===================================
            $table->string('id_pesan')->unique()->nullable();
            $table->string('cabang')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('vendor')->nullable();
            $table->string('pesanan')->nullable();
            $table->string('status_pesan')->nullable();

            // ===================================
            // 5. Harga & Perhitungan
            // ===================================
            $table->decimal('berat', 10, 2)->default(0);
            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('fee_payment', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            // ===================================
            // 6. Status & Lain-lain
            // ===================================
            $table->string('status')->default('aktif');
            $table->string('sales')->nullable();               // Nama inputer
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jakarta_aktif');
    }
};