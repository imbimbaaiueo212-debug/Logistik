<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom Persiapan untuk Logistik Aktif & Logistik Pasif.
 * Data Persiapan Logistik dibaca langsung dari Realisasi (RA) di Penjualan,
 * jadi status persiapan disimpan di baris RA-nya.
 */
return new class extends Migration
{
    private array $tables = ['realisasi_logistik_aktif', 'realisasi_logistik_pasif'];

    public function up(): void
    {
        foreach ($this->tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }

            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                if (! Schema::hasColumn($tbl, 'tgl_terima')) {
                    $table->timestamp('tgl_terima')->nullable();
                }
                if (! Schema::hasColumn($tbl, 'tgl_picking')) {
                    $table->date('tgl_picking')->nullable();
                }
                if (! Schema::hasColumn($tbl, 'status_persiapan')) {
                    $table->string('status_persiapan')->default('Belum');
                }
                if (! Schema::hasColumn($tbl, 'pic')) {
                    $table->string('pic')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $tbl) {
            if (! Schema::hasTable($tbl)) {
                continue;
            }

            Schema::table($tbl, function (Blueprint $table) use ($tbl) {
                foreach (['tgl_terima', 'tgl_picking', 'status_persiapan', 'pic'] as $col) {
                    if (Schema::hasColumn($tbl, $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
