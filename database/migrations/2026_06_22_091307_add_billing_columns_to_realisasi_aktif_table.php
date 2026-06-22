<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {
            $table->string('billing_last_name')->nullable()->after('nama_unit');
            $table->string('billing_company')->nullable()->after('billing_last_name');
        });
    }

    public function down()
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {
            $table->dropColumn(['billing_last_name', 'billing_company']);
        });
    }
};