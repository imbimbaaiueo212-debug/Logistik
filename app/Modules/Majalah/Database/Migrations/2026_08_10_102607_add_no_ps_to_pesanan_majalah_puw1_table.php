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
    Schema::table('pesanan_majalah_puw1', function (Blueprint $table) {
        $table->string('no_ps', 50)->nullable()->after('periode');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pesanan_majalah_puw1', function (Blueprint $table) {
            //
        });
    }
};
