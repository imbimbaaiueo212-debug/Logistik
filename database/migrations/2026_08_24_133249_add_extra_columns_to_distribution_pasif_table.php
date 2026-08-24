<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('distribution_pasif', function (Blueprint $table) {
        $table->date('tgl_pickup')->nullable()->after('status_pengiriman');
        $table->string('awb')->nullable()->after('tgl_pickup'); // sama dengan no_resi
        $table->date('tgl_diterima')->nullable()->after('awb');
        $table->string('penerima')->nullable()->after('tgl_diterima');
        $table->decimal('berat_aktual', 10, 2)->nullable()->change(); // sudah ada, pastikan
        $table->string('koli')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('distribution_pasif', function (Blueprint $table) {
        $table->dropColumn(['tgl_pickup', 'awb', 'tgl_diterima', 'penerima']);
    });
}
};
