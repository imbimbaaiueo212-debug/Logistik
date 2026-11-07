<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kolom bisa sudah ada (pernah dibuat dari migrasi versi awal), jadi dicek dulu
        if (Schema::hasColumn('kalender_spare_manual', 'tahap')) {
            return;
        }

        Schema::table('kalender_spare_manual', function (Blueprint $table) {
            // Spare ini ikut cetak ke berapa (1, 2, ...). Data lama otomatis tahap 1.
            $table->unsignedTinyInteger('tahap')->default(1)->after('qty')->index();
        });
    }

    public function down(): void
    {
        Schema::table('kalender_spare_manual', function (Blueprint $table) {
            $table->dropColumn('tahap');
        });
    }
};
