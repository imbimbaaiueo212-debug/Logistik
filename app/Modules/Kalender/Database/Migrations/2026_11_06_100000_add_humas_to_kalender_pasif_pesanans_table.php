<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom HUMAS (10 Humas + 1 pajangan unit) untuk rekap Unit Pasif.
 * Pemetaan kolom: qty = MURID, bacaan_unit = RELAWAN, humas = HUMAS.
 * RELAWAN @2 exp dan TOTAL tidak disimpan; selalu dihitung dari 3 kolom itu.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('kalender_pasif_pesanans', 'humas')) {
            Schema::table('kalender_pasif_pesanans', function (Blueprint $table) {
                $table->integer('humas')->default(0)->after('bacaan_unit');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('kalender_pasif_pesanans', 'humas')) {
            Schema::table('kalender_pasif_pesanans', function (Blueprint $table) {
                $table->dropColumn('humas');
            });
        }
    }
};
