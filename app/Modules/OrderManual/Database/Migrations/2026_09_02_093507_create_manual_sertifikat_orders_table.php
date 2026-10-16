<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_sertifikat_orders', function (Blueprint $table) {
            $table->id();

            // Identitas order
            $table->string('manual_id', 30)->nullable()->index()
                  ->comment('Contoh: MS-20260902-0001');
            $table->string('order_id', 100)->nullable()->index()
                  ->comment('ID pesanan dari Bimba Shop (jika ada)');

            // Tanggal
            $table->dateTime('order_date')->nullable()->index();
            $table->dateTime('payment_date')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('estimasi_print_pl')->nullable();
            $table->dateTime('estimasi_persiapan')->nullable();

            // Customer / Unit
            $table->string('customer_name', 150)->nullable()->index();
            $table->string('billing_first_name', 100)->nullable();
            $table->string('billing_last_name', 50)->nullable()->index()
                  ->comment('Biasanya No Cabang');

            // Produk
            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_sku', 100)->nullable()->index();
            $table->string('product_name', 255)->nullable();
            $table->unsignedInteger('qty')->default(1);

            // Harga
            $table->decimal('price', 15, 2)->nullable()
                  ->comment('Harga jual (total item)');
            $table->decimal('total', 15, 2)->nullable();
            $table->decimal('ship_total', 15, 2)->nullable();
            $table->decimal('discount_total', 15, 2)->nullable();
            $table->decimal('refunded_total', 15, 2)->nullable();
            $table->decimal('order_weight', 12, 2)->nullable()
                  ->comment('Berat dalam gram');

            // Status
            $table->string('status', 50)->default('pending')->index();
            $table->string('status_kirim', 50)->nullable()
                  ->comment('Dikirim / Diambil');
            $table->string('status_bayar', 50)->nullable();
            $table->string('status_bimbashop', 50)->nullable();
            $table->string('payment_method', 50)->nullable()->default('manual');

            // Pengiriman
            $table->string('ekspedisi', 100)->nullable();
            $table->string('service_pengiriman', 50)->nullable();

            // Alamat Shipping
            $table->text('shipping_address_1')->nullable();
            $table->text('shipping_address_2')->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_kelurahan', 100)->nullable();
            $table->string('shipping_kecamatan', 100)->nullable();

            // Alamat Billing (opsional)
            $table->string('billing_kelurahan', 100)->nullable();
            $table->string('billing_kecamatan', 100)->nullable();

            // Kontak & catatan
            $table->string('phone', 30)->nullable();
            $table->text('catatan')->nullable();
            $table->text('notes')->nullable();

            // Flag proses
            $table->boolean('is_processed')->default(false)->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_sertifikat_orders');
    }
};