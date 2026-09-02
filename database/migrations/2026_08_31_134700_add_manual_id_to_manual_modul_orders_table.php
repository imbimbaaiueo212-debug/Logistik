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
        $table->string('manual_id', 30)->nullable()->unique()->after('id');
    });
}

public function down()
{
    Schema::table('manual_modul_orders', function (Blueprint $table) {
        $table->dropColumn('manual_id');
    });
}
};
