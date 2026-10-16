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
    Schema::table('picking_pasif', function (Blueprint $table) {
        $table->timestamp('tgl_terima')->nullable()->after('tgl_picking');
        $table->string('status_persiapan')->default('Belum')->after('status');
        $table->string('pic')->nullable()->after('status_persiapan');
    });
}

public function down(): void
{
    Schema::table('picking_pasif', function (Blueprint $table) {
        $table->dropColumn(['tgl_terima', 'status_persiapan', 'pic']);
    });
}
};
