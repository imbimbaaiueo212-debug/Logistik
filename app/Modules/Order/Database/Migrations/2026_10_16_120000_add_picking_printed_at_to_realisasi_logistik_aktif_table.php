<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('realisasi_logistik_aktif', 'picking_printed_at')) {
            Schema::table('realisasi_logistik_aktif', function (Blueprint $table) {
                $table->timestamp('picking_printed_at')->nullable()->after('printed_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('realisasi_logistik_aktif', function (Blueprint $table) {
            $table->dropColumn('picking_printed_at');
        });
    }
};
