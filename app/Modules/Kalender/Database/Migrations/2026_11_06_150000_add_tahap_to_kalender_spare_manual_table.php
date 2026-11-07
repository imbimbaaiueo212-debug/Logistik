<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kalender_spare_manual', function (Blueprint $table) {
            // Cetak tahap ke berapa (1, 2, 3, ...). Data lama otomatis tahap 1.
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
