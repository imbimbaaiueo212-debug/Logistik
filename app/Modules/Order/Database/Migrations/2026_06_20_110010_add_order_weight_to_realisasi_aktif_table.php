<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {
            $table->decimal('order_weight', 12, 2)->nullable()->after('jumlah_bayar');
            // $table->decimal('berat', 12, 2)->nullable()->after('jumlah_bayar'); // alternatif
        });
    }

    public function down()
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {
            $table->dropColumn('order_weight');
            // $table->dropColumn('berat');
        });
    }
};