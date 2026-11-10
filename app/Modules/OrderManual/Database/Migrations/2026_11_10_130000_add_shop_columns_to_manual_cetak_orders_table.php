<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Create Manual Kalender / Majalah: kolom hasil pencocokan biMBA Shop + Kasdana.
 * Status awal = on-hold. Berubah jadi processing bila order sudah ada di biMBA Shop DAN sudah lunas di Kasdana.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_cetak_orders', function (Blueprint $table) {
            $table->string('bimba_order_id', 100)->nullable()->after('order_id')->index();
            $table->string('status_bimbashop', 30)->nullable()->after('status');
            $table->string('status_bayar', 30)->nullable()->after('status_bimbashop');   // status Kasdana
            $table->dateTime('payment_date')->nullable()->after('status_bayar');
            $table->dateTime('shop_synced_at')->nullable()->after('payment_date');
        });

        // data lama yang masih "pending" dan belum diproses -> on-hold
        DB::table('manual_cetak_orders')
            ->where('status', 'pending')
            ->whereNull('synced_penjualan_at')
            ->update(['status' => 'on-hold']);
    }

    public function down(): void
    {
        Schema::table('manual_cetak_orders', function (Blueprint $table) {
            $table->dropColumn(['bimba_order_id', 'status_bimbashop', 'status_bayar', 'payment_date', 'shop_synced_at']);
        });
    }
};
