<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pesanan manual Kalender / Majalah (Create Manual gaya "Create Manual Modul").
 * Satu baris = satu item SKU; satu pesanan = banyak baris dengan manual_id yang sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('manual_cetak_orders')) {
            return;
        }

        Schema::create('manual_cetak_orders', function (Blueprint $table) {
            $table->id();

            $table->string('manual_id', 30)->index();            // MK-YYYYMMDD-0001 (kalender) / MJ-... (majalah)
            $table->string('jenis', 12)->index();                // kalender | majalah
            $table->string('status_unit', 10)->default('aktif')->index(); // aktif | pasif
            $table->string('no_cab', 50)->nullable()->index();
            $table->string('edisi', 30)->nullable()->index();    // dari SKU, mis. KLD27
            $table->string('kode_stokis', 20)->nullable()->index(); // dari SKU: JKT / TNG1 / SMG / dst (Opsi B)

            $table->string('order_id')->nullable()->index();     // ID pesanan biMBA Shop (jika ada)
            $table->dateTime('order_date')->nullable()->index();
            $table->string('customer_name')->nullable()->index();

            $table->string('billing_first_name')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->text('billing_address_1')->nullable();
            $table->string('billing_address_2')->nullable();
            $table->string('billing_city')->nullable();
            $table->string('billing_postcode', 20)->nullable();
            $table->string('billing_state')->nullable();
            $table->string('billing_kelurahan')->nullable();
            $table->string('billing_kecamatan')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 40)->nullable();

            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->string('shipping_company')->nullable();
            $table->text('shipping_address_1')->nullable();
            $table->string('shipping_address_2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_postcode', 20)->nullable();
            $table->string('shipping_state')->nullable();
            $table->string('shipping_kelurahan')->nullable();
            $table->string('shipping_kecamatan')->nullable();
            $table->string('shipping_phone', 40)->nullable();

            $table->unsignedBigInteger('product_id')->nullable()->index();
            $table->string('product_sku')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->integer('qty')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->integer('order_weight')->default(0);

            $table->string('payment_method')->default('manual');
            $table->string('status')->default('pending')->index();
            $table->string('grup', 10)->nullable();
            $table->string('no_ps', 100)->nullable();

            $table->string('status_kirim', 20)->nullable();      // Dikirim | Diambil
            $table->string('ekspedisi')->nullable();
            $table->string('service_pengiriman', 50)->nullable();

            $table->boolean('is_processed')->default(false)->index();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('synced_penjualan_at')->nullable(); // tahap 3: sudah dikirim ke Penjualan
            $table->text('catatan')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_cetak_orders');
    }
};
