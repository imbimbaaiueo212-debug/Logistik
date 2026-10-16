<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Database Logistik Pasif (order dengan SKU -LG).
 * Struktur sama dengan jakarta_aktif + jakarta_aktif_items.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('logistik_pasif')) {
            Schema::create('logistik_pasif', function (Blueprint $table) {
                $table->id();

                // Tanggal
                $table->date('tgl_input')->nullable();
                $table->dateTime('tgl_pesan')->nullable();
                $table->dateTime('estimasi_print_pl')->nullable();
                $table->dateTime('estimasi_persiapan')->nullable();

                // Penerima & pengiriman
                $table->string('kirim')->nullable();
                $table->string('status_kirim')->default('Belum Dikirim');
                $table->string('no_telpon')->nullable();
                $table->text('alamat_kirim')->nullable();
                $table->string('kab_kota_provinsi')->nullable();
                $table->string('ekspedisi')->nullable();
                $table->decimal('ongkir', 15, 2)->default(0);
                $table->string('service_pengiriman')->nullable();
                $table->string('tracking_number')->nullable();

                // Distribusi
                $table->string('distribusi_manual')->nullable();
                $table->string('nama_distributor')->nullable();
                $table->date('tgl_distribusi')->nullable();
                $table->string('status_distribusi')->nullable();

                // Validasi & pembayaran
                $table->string('validasi')->nullable();
                $table->string('jenis_bank')->nullable();
                $table->string('status_pembayaran')->nullable();
                $table->dateTime('payment_date')->nullable();

                // Order & unit
                $table->string('id_pesan')->unique()->nullable();
                $table->string('cabang')->nullable();
                $table->string('nama_unit')->nullable();
                $table->string('billing_last_name')->nullable();
                $table->string('billing_company')->nullable();
                $table->string('vendor')->nullable();
                $table->text('pesanan')->nullable();
                $table->string('status_pesan')->nullable();
                $table->integer('item_qty')->default(0);

                // Harga
                $table->decimal('berat', 10, 2)->default(0);
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('diskon', 15, 2)->default(0);
                $table->decimal('fee_payment', 15, 2)->default(0);
                $table->decimal('total', 15, 2)->default(0);

                // Status & lain-lain
                $table->string('status')->default('aktif');
                $table->string('grup', 10)->nullable();
                $table->string('sales')->nullable();
                $table->text('catatan')->nullable();

                // Proses
                $table->boolean('is_processed')->default(false);
                $table->timestamp('processed_at')->nullable();
                $table->timestamp('printed_at')->nullable();

                $table->timestamps();

                $table->index('tgl_pesan');
                $table->index('status_pembayaran');
            });
        }

        if (! Schema::hasTable('logistik_pasif_items')) {
            Schema::create('logistik_pasif_items', function (Blueprint $table) {
                $table->id();

                $table->foreignId('logistik_pasif_id')
                    ->constrained('logistik_pasif')
                    ->cascadeOnDelete();

                $table->foreignId('product_id')
                    ->nullable()
                    ->constrained('products')
                    ->nullOnDelete();

                $table->string('sku')->nullable();
                $table->string('label')->nullable();
                $table->string('nama_produk')->nullable();
                $table->integer('qty')->default(1);
                $table->decimal('harga', 15, 2)->default(0);
                $table->decimal('subtotal', 15, 2)->default(0);

                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('logistik_pasif_items');
        Schema::dropIfExists('logistik_pasif');
    }
};
