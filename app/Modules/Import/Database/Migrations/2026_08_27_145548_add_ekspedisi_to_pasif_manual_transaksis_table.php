<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasif_manual_transaksis', function (Blueprint $table) {
            $table->string('ekspedisi')->nullable()->after('status_kirim');
            $table->string('service_pengiriman')->nullable()->after('ekspedisi');
        });
    }

    public function down(): void
    {
        Schema::table('pasif_manual_transaksis', function (Blueprint $table) {
            $table->dropColumn(['ekspedisi', 'service_pengiriman']);
        });
    }
};