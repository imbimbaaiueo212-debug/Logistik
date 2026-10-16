<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Jenis (rumah) order Pasif: JKTP, DLC, EBT.
 * Data yang sudah ada otomatis menjadi JKTP.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['jakarta_pasif', 'realisasi_pasif'] as $tabel) {
            if (! Schema::hasColumn($tabel, 'jenis')) {
                Schema::table($tabel, function (Blueprint $table) {
                    $table->string('jenis', 10)->nullable()->index();
                });
            }
        }

        DB::table('jakarta_pasif')->whereNull('jenis')->update(['jenis' => 'JKTP']);

        // Rekap Aktual ikut jenis order asalnya (default JKTP)
        DB::statement(
            'UPDATE realisasi_pasif SET jenis = COALESCE((SELECT jp.jenis FROM jakarta_pasif jp WHERE jp.id = realisasi_pasif.jakarta_pasif_id), \'JKTP\') WHERE jenis IS NULL'
        );
    }

    public function down(): void
    {
        foreach (['jakarta_pasif', 'realisasi_pasif'] as $tabel) {
            if (Schema::hasColumn($tabel, 'jenis')) {
                Schema::table($tabel, function (Blueprint $table) {
                    $table->dropColumn('jenis');
                });
            }
        }
    }
};
