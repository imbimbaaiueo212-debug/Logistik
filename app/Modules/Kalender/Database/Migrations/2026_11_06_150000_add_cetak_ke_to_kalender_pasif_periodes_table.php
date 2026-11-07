<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kalender_pasif_periodes', function (Blueprint $table) {
            // Cetak ke berapa (1, 2, ...), diisi manual di Daftar Pasif
            $table->unsignedTinyInteger('cetak_ke')->nullable()->after('no_ps');
        });
    }

    public function down(): void
    {
        Schema::table('kalender_pasif_periodes', function (Blueprint $table) {
            $table->dropColumn('cetak_ke');
        });
    }
};
