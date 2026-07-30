<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // MANUAL REALISASI (mirror realisasi_aktif)
        // =====================================================
        Schema::create('manual_realisasi', function (Blueprint $table) {
            $table->id();

            $table->foreignId('manual_order_id')
                ->constrained('manual_orders')
                ->cascadeOnDelete();

            $table->string('rekap_number')->nullable();
            $table->string('no_pl')->nullable();              // = order_id
            $table->dateTime('tgl_turun_pl')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->string('pengiriman')->nullable();         // ekspedisi / jasa kurir
            $table->string('service_pengiriman')->nullable();
            $table->string('nama_barang')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->json('product_ids')->nullable();
            $table->string('kategori_order')->default('Majalah');
            $table->dateTime('tgl_bayar')->nullable();
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('order_weight', 12, 2)->default(0);
            $table->string('nama_stokis')->nullable();
            $table->dateTime('tgl_estimasi')->nullable();
            $table->integer('estimasi_hari')->nullable();
            $table->string('penyebut')->nullable();
            $table->string('pengambil')->nullable();
            $table->text('ket')->nullable();
            $table->boolean('is_processed')->default(true);
            $table->timestamp('printed_at')->nullable();
            $table->timestamp('picking_printed_at')->nullable();

            $table->timestamps();

            $table->index('no_pl');
            $table->index('tgl_turun_pl');
            $table->index('kategori_order');
        });

        // =====================================================
        // MANUAL PICKINGS
        // =====================================================
        Schema::create('manual_pickings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('manual_realisasi_id')
                ->constrained('manual_realisasi')
                ->cascadeOnDelete();

            $table->foreignId('manual_order_id')->nullable()
                ->constrained('manual_orders')
                ->nullOnDelete();

            $table->string('no_pl')->nullable();
            $table->string('kategori_order')->nullable();
            $table->date('tgl_order')->nullable();
            $table->date('tgl_picking')->nullable();
            $table->dateTime('payment_date')->nullable();
            $table->date('waktu_estimasi_persiapan')->nullable();
            $table->time('jam_picking')->nullable();
            $table->string('id_pesan')->nullable();
            $table->string('vendor')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->text('kirim')->nullable();
            $table->string('no_telpon')->nullable();
            $table->text('alamat_kirim')->nullable();
            $table->string('kab_kota_provinsi')->nullable();
            $table->string('ekspedisi')->nullable();
            $table->string('service_pengiriman')->nullable();
            $table->string('pesanan')->nullable();
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('berat', 12, 2)->default(0);
            $table->integer('total_item')->default(0);
            $table->integer('total_qty')->default(0);
            $table->string('status')->default('completed');
            $table->timestamp('printed_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });

        // =====================================================
        // MANUAL PICKING ITEMS
        // =====================================================
        Schema::create('manual_picking_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('manual_picking_id')
                ->constrained('manual_pickings')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('item_name')->nullable();
            $table->string('item_sku')->nullable();
            $table->integer('item_qty')->default(1);
            $table->integer('qty_picked')->default(0);
            $table->boolean('cek')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_picking_items');
        Schema::dropIfExists('manual_pickings');
        Schema::dropIfExists('manual_realisasi');
    }
};