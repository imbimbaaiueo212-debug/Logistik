<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_modul_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('manual_modul_orders', 'manual_id')) {
                $table->string('manual_id', 30)->nullable()->index();
            }
            if (!Schema::hasColumn('manual_modul_orders', 'billing_first_name')) {
                $table->string('billing_first_name', 100)->nullable();
            }
            if (!Schema::hasColumn('manual_modul_orders', 'phone')) {
                $table->string('phone', 30)->nullable();
            }
            if (!Schema::hasColumn('manual_modul_orders', 'shipping_address_1')) {
                $table->text('shipping_address_1')->nullable();
            }
            if (!Schema::hasColumn('manual_modul_orders', 'shipping_address_2')) {
                $table->text('shipping_address_2')->nullable();
            }
            if (!Schema::hasColumn('manual_modul_orders', 'shipping_city')) {
                $table->string('shipping_city', 100)->nullable();
            }
            if (!Schema::hasColumn('manual_modul_orders', 'order_weight')) {
                $table->decimal('order_weight', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('manual_modul_orders', 'is_processed')) {
                $table->boolean('is_processed')->default(false);
            }
            if (!Schema::hasColumn('manual_modul_orders', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->index();
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'label')) {
                $table->string('label', 100)->nullable()->after('sku')->index();
            }
        });
    }

    public function down(): void
    {
        // sengaja kosong — jangan drop kolom yang mungkin sudah terisi data
    }
};