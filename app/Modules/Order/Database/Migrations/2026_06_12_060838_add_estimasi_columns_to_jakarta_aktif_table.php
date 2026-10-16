<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('jakarta_aktif', function (Blueprint $table) {
        // Tambahkan setelah kolom yang pasti ada
        $table->dateTime('estimasi_print_pl')->nullable()->after('tgl_pesan');
        $table->dateTime('estimasi_persiapan')->nullable()->after('estimasi_print_pl');
    });
}

public function down()
{
    Schema::table('jakarta_aktif', function (Blueprint $table) {
        $table->dropColumn(['estimasi_print_pl', 'estimasi_persiapan']);
    });
}
};