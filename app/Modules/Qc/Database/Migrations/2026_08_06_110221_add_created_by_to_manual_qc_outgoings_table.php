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
    Schema::table('manual_qc_outgoings', function (Blueprint $table) {
        $table->unsignedBigInteger('created_by')->nullable()->after('tgl_qc');
    });
}

public function down()
{
    Schema::table('manual_qc_outgoings', function (Blueprint $table) {
        $table->dropColumn('created_by');
    });
}
};
