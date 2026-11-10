<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('kalender_pasif_manual_transaksis', 'status_unit')) {
            Schema::table('kalender_pasif_manual_transaksis', function (Blueprint $table) {
                // Data lama dianggap pasif agar angka Report Angka Cetak tidak berubah.
                $table->string('status_unit', 10)->default('pasif')->index()->after('nama_unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kalender_pasif_manual_transaksis', 'status_unit')) {
            Schema::table('kalender_pasif_manual_transaksis', function (Blueprint $table) {
                $table->dropColumn('status_unit');
            });
        }
    }
};
