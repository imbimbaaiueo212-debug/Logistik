<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {

            $table->string('kategori_order', 50)
                  ->nullable()
                  ->after('nama_barang');

            $table->index('kategori_order');
            $table->index(['jakarta_aktif_id', 'kategori_order']);
        });
    }

    public function down(): void
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {

            $table->dropIndex(['jakarta_aktif_id', 'kategori_order']);
            $table->dropIndex(['kategori_order']);

            $table->dropColumn('kategori_order');
        });
    }
};
