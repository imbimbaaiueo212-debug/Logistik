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
        Schema::table('manual_realisasi', function (Blueprint $table) {
            $table->string('grup', 10)->nullable()->after('kategori_order')->index();
        });

        Schema::table('manual_pickings', function (Blueprint $table) {
            $table->string('grup', 10)->nullable()->after('kategori_order')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manual_realisasi_and_picking', function (Blueprint $table) {
            //
        });
    }
};
