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
    Schema::table('realisasi_aktif', function (Blueprint $table) {
        $table->text('product_ids')->nullable()->after('product_id');
    });
}

public function down()
{
    Schema::table('realisasi_aktif', function (Blueprint $table) {
        $table->dropColumn('product_ids');
    });
}
};
