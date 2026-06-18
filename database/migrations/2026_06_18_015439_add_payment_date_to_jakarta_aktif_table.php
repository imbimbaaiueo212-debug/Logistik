<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('jakarta_aktif', function (Blueprint $table) {
            $table->dateTime('payment_date')->nullable()->after('status_pembayaran');
            // atau pakai $table->date('payment_date') kalau hanya tanggal saja
        });
    }

    public function down()
    {
        Schema::table('jakarta_aktif', function (Blueprint $table) {
            $table->dropColumn('payment_date');
        });
    }
};