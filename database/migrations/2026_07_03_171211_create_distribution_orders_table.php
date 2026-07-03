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
        Schema::create('distribution_orders', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | RELASI PACKING
            |--------------------------------------------------------------------------
            | Menghubungkan Distribution Order dengan Packing
            | Sehingga data packing tidak perlu disimpan ulang.
            |--------------------------------------------------------------------------
            */

            $table->foreignId('packing_id')
                ->nullable()
                ->constrained('packing')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | DATA ORDER
            |--------------------------------------------------------------------------
            */

            $table->string('no_pl')->index();

            $table->date('tgl_turun_pl')->nullable();

            $table->string('nama_unit')->nullable();

            // Diambil dari Jakarta Aktif / Realisasi
            $table->string('metode_pengiriman')->nullable();

            $table->longText('nama_barang')->nullable();

            /*
            |--------------------------------------------------------------------------
            | PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            $table->date('tgl_bayar')->nullable();

            $table->unsignedBigInteger('jumlah_bayar')->nullable();

            /*
            |--------------------------------------------------------------------------
            | ESTIMASI
            |--------------------------------------------------------------------------
            */

            $table->date('tgl_estimasi')->nullable();

            /*
            |--------------------------------------------------------------------------
            | DISTRIBUSI
            |--------------------------------------------------------------------------
            */

            /*
             * Barang diambil sendiri atau menggunakan ekspedisi
             */

            $table->enum('jenis_pengiriman', [
                'diambil_sendiri',
                'ekspedisi'
            ])->nullable();

            /*
             * Jika Diambil Sendiri
             */

            $table->date('tgl_pengambilan')->nullable();

            $table->string('pengambil')->nullable();

            /*
             * Jika Menggunakan Ekspedisi
             */

            $table->date('tgl_pickup')->nullable();

            $table->string('ekspedisi')->nullable();

            $table->string('awb',100)->nullable();

            // REG, YES, OKE, ECONOMY, dll
            $table->string('service')->nullable();

            // 1 Hari, 2-3 Hari, 4 Hari, dll
            $table->string('estimasi_pengiriman')->nullable();

            /*
             * Barang diterima
             */

            $table->date('tgl_diterima')->nullable();

            $table->string('penerima')->nullable();

            /*
            |--------------------------------------------------------------------------
            | STATUS PENGIRIMAN
            |--------------------------------------------------------------------------
            */

            $table->enum('status_pengiriman', [

                'belum_pickup',

                'pickup',

                'transit',

                'hold',

                'retur',

                'delivered',

                'missing'

            ])->default('belum_pickup');

            /*
            |--------------------------------------------------------------------------
            | CATATAN
            |--------------------------------------------------------------------------
            */

            $table->text('catatan')->nullable();

            /*
            |--------------------------------------------------------------------------
            | WAKTU PROSES
            |--------------------------------------------------------------------------
            */

            // Kapan distribusi mulai diproses
            $table->timestamp('distribution_at')->nullable();

            // Kapan barang benar-benar diterima
            $table->timestamp('delivered_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | USER
            |--------------------------------------------------------------------------
            */

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | TIMESTAMP
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_orders');
    }
};