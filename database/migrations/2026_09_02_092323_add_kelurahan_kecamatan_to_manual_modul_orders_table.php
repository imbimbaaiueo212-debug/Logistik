<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_modul_orders', function (Blueprint $table) {
            // Billing
            if (!Schema::hasColumn('manual_modul_orders', 'billing_kelurahan')) {
                $table->string('billing_kelurahan', 100)->nullable()->after('shipping_city');
            }
            if (!Schema::hasColumn('manual_modul_orders', 'billing_kecamatan')) {
                $table->string('billing_kecamatan', 100)->nullable()->after('billing_kelurahan');
            }

            // Shipping
            if (!Schema::hasColumn('manual_modul_orders', 'shipping_kelurahan')) {
                $table->string('shipping_kelurahan', 100)->nullable()->after('billing_kecamatan');
            }
            if (!Schema::hasColumn('manual_modul_orders', 'shipping_kecamatan')) {
                $table->string('shipping_kecamatan', 100)->nullable()->after('shipping_kelurahan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('manual_modul_orders', function (Blueprint $table) {
            $table->dropColumn([
                'billing_kelurahan',
                'billing_kecamatan',
                'shipping_kelurahan',
                'shipping_kecamatan',
            ]);
        });
    }
};