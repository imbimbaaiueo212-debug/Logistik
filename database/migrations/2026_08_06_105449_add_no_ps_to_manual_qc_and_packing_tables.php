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
        $table->string('no_ps')->nullable()->after('no_pl');
    });

    Schema::table('manual_packings', function (Blueprint $table) {
        $table->string('no_ps')->nullable()->after('no_pl');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_qc_outgoings', function (Blueprint $table) {
            $table->dropColumn('no_ps');
        });

        Schema::table('manual_packings', function (Blueprint $table) {
            $table->dropColumn('no_ps');
        });
    }
};
