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
    Schema::table('dlc_periodes', function (Blueprint $table) {
        $table->string('no_ps')->nullable()->after('status');
    });
}

public function down()
{
    Schema::table('dlc_periodes', function (Blueprint $table) {
        $table->dropColumn('no_ps');
    });
}
};
