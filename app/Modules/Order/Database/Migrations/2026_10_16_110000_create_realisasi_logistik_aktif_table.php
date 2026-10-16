<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Realisasi Logistik Aktif (sumber data Rekap Aktual Logistik).
 * Struktur mengikuti realisasi_aktif, tanpa kolom picking/QC/packing.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('realisasi_logistik_aktif')) {
            return;
        }

        Schema::create('realisasi_logistik_aktif', function (Blueprint $table) {
            $table->id();
            $table->string('rekap_number', 20)->nullable()->index();
            $table->string('no_pl')->nullable();
            $table->date('tgl_turun_pl')->nullable();

            $table->string('nama_unit')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();

            $table->string('pengiriman')->nullable();
            $table->string('service_pengiriman')->nullable();

            $table->text('nama_barang')->nullable();
            $table->string('kategori_order', 50)->nullable()->index();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->text('product_ids')->nullable();

            $table->dateTime('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('order_weight', 12, 2)->nullable();
            $table->string('nama_stokis')->nullable();
            $table->dateTime('tgl_estimasi')->nullable();
            $table->integer('estimasi_hari')->nullable();
            $table->string('penyebut')->nullable();
            $table->string('pengambil')->nullable();
            $table->text('ket')->nullable();

            $table->foreignId('logistik_aktif_id')
                ->nullable()
                ->constrained('logistik_aktif')
                ->nullOnDelete();

            $table->timestamp('printed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('realisasi_logistik_aktif');
    }
};
