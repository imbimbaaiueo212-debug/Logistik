<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickings', function (Blueprint $table) {
            $table->foreignId('realisasi_aktif_id')
                  ->nullable()
                  ->after('jakarta_aktif_id')
                  ->constrained('realisasi_aktif')
                  ->onDelete('cascade');

            // Index untuk performa
            $table->index('realisasi_aktif_id');
        });
    }

    public function down(): void
    {
        Schema::table('pickings', function (Blueprint $table) {
            $table->dropForeign(['realisasi_aktif_id']);
            $table->dropIndex(['realisasi_aktif_id']);
            $table->dropColumn('realisasi_aktif_id');
        });
    }
};