<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Tambah ke tabel jakarta_aktif
        Schema::table('jakarta_aktif', function (Blueprint $table) {
            if (!Schema::hasColumn('jakarta_aktif', 'billing_company')) {
                $table->string('billing_company')->nullable()->after('billing_last_name');
            }
        });

        // Tambah ke tabel realisasi_aktif
        Schema::table('realisasi_aktif', function (Blueprint $table) {
            if (!Schema::hasColumn('realisasi_aktif', 'billing_company')) {
                $table->string('billing_company')->nullable()->after('billing_last_name');
            }
        });
    }

    public function down()
    {
        Schema::table('jakarta_aktif', function (Blueprint $table) {
            $table->dropColumn('billing_company');
        });

        Schema::table('realisasi_aktif', function (Blueprint $table) {
            $table->dropColumn('billing_company');
        });
    }
};