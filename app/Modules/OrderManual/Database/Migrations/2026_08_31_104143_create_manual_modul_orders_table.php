<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_modul_orders', function (Blueprint $table) {
            $table->id();

            // Identitas order
            $table->string('order_id')->nullable()->index();
            $table->dateTime('order_date')->nullable()->index();
            $table->dateTime('payment_date')->nullable();

            // Unit / customer
            $table->string('customer_name')->nullable()->index();
            $table->string('billing_first_name')->nullable(); // mitra
            $table->string('billing_last_name')->nullable()->index(); // no cab
            $table->string('shipping_first_name')->nullable();
            $table->string('shipping_last_name')->nullable();
            $table->text('shipping_address_1')->nullable();
            $table->string('shipping_address_2')->nullable();
            $table->string('shipping_city')->nullable();
            $table->string('shipping_phone')->nullable();

            // Produk modul
            $table->string('product_sku')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->integer('qty')->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('ship_total', 15, 2)->default(0);
            $table->integer('order_weight')->default(0); // gram
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('refunded_total', 15, 2)->default(0);

            // Status & grup
            $table->string('payment_method')->default('manual');
            $table->string('status')->default('pending')->index(); // pending|processing|completed
            $table->string('grup', 10)->nullable()->index(); // A|B|F|dll

            // Pengiriman
            $table->string('status_kirim')->nullable(); // Dikirim|Diambil
            $table->string('ekspedisi')->nullable();
            $table->string('service_pengiriman')->nullable();

            // Proses
            $table->boolean('is_processed')->default(false)->index();
            $table->dateTime('processed_at')->nullable();
            $table->dateTime('estimasi_print_pl')->nullable();
            $table->dateTime('estimasi_persiapan')->nullable();

            // Lain-lain
            $table->string('no_ps')->nullable();
            $table->text('notes')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_modul_orders');
    }
};