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
    Schema::table('jakarta_aktif', function (Blueprint $table) {
        $table->string('status_kirim')->default('Belum Dikirim')->after('kirim');
    });
}

public function down()
{
    Schema::table('jakarta_aktif', function (Blueprint $table) {
        $table->dropColumn('status_kirim');
    });
}
};
