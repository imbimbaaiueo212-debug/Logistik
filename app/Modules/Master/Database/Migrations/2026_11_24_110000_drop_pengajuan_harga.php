<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur "Pengajuan Harga" dihapus; penggantinya alur import + ACC di Penyesuaian Harga.
 * Berkas surat lama di storage/app/private/price-change TIDAK ikut dihapus (arsip).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('price_change_logs');
        Schema::dropIfExists('price_change_request_files');
        Schema::dropIfExists('price_change_request_items');
        Schema::dropIfExists('price_change_requests');

        if (Schema::hasColumn('products', 'harga_request_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('harga_request_id');
            });
        }

        // Penanda di Master Product: harga diperbarui oleh batch penyesuaian mana
        if (! Schema::hasColumn('products', 'harga_diubah_pada')) {
            Schema::table('products', function (Blueprint $table) {
                $table->timestamp('harga_diubah_pada')->nullable();
            });
        }
        if (! Schema::hasColumn('products', 'harga_batch_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->unsignedBigInteger('harga_batch_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'harga_batch_id')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('harga_batch_id');
            });
        }
        // Tabel pengajuan lama tidak dibuat ulang.
    }
};
