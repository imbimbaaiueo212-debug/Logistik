<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {
            $table->timestamp('qc_printed_at')->nullable()->after('picking_printed_at');
            $table->timestamp('ra_picking_printed_at')->nullable()->after('qc_printed_at');
            $table->timestamp('packing_printed_at')->nullable()->after('ra_picking_printed_at');
            $table->timestamp('distribusi_printed_at')->nullable()->after('packing_printed_at');
        });
    }

    public function down()
    {
        Schema::table('realisasi_aktif', function (Blueprint $table) {
            $table->dropColumn([
                'qc_printed_at',
                'ra_picking_printed_at',
                'packing_printed_at',
                'distribusi_printed_at'
            ]);
        });
    }
};