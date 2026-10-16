<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_orders', function (Blueprint $table) {
            $table->string('no_cabang', 50)->nullable()->after('order_id')->index();
            $table->string('mitra_pengelolaan')->nullable()->after('no_cabang');
            $table->string('source')->default('manual')->after('status'); // manual | pesanan_majalah | bimba_shop
            $table->string('bimba_order_id')->nullable()->after('order_id')->index();
            $table->dateTime('bimba_order_date')->nullable()->after('order_date');
            $table->unsignedBigInteger('pesanan_majalah_id')->nullable()->index();
            $table->string('pesanan_majalah_type')->nullable(); // kabupaten | kotamadya | puw1
            $table->unsignedBigInteger('pesanan_majalah_unit_id')->nullable()->index();
            $table->boolean('is_synced_bimba')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('manual_orders', function (Blueprint $table) {
            $table->dropColumn([
                'no_cabang',
                'mitra_pengelolaan',
                'source',
                'bimba_order_id',
                'bimba_order_date',
                'pesanan_majalah_id',
                'pesanan_majalah_type',
                'pesanan_majalah_unit_id',
                'is_synced_bimba',
            ]);
        });
    }
};