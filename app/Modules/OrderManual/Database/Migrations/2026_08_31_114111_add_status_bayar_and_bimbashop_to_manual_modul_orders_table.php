<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('manual_modul_orders', function (Blueprint $table) {
        $table->string('status_bayar', 50)->nullable()->after('payment_date');
        $table->string('status_bimbashop', 50)->nullable()->after('status_bayar');
    });
}

public function down()
{
    Schema::table('manual_modul_orders', function (Blueprint $table) {
        $table->dropColumn(['status_bayar', 'status_bimbashop']);
    });
}
};
