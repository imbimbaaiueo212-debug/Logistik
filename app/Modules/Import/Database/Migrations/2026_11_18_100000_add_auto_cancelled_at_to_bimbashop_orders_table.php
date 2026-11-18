<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bimbashop_orders', function (Blueprint $table) {
            // Diisi saat order dibatalkan OTOMATIS oleh CasdanaStatusSync (tidak dibayar 1x24 jam).
            // Dipakai supaya hanya order auto-cancel yang boleh kembali ke processing
            // saat pembayaran muncul belakangan. Cancel manual / dari Shop tidak disentuh.
            $table->timestamp('auto_cancelled_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('bimbashop_orders', function (Blueprint $table) {
            $table->dropColumn('auto_cancelled_at');
        });
    }
};
