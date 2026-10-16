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
    Schema::table('manual_pickings', function (Blueprint $table) {
        if (!Schema::hasColumn('manual_pickings', 'tgl_terima')) {
            $table->timestamp('tgl_terima')->nullable()->after('grup');
        }
        if (!Schema::hasColumn('manual_pickings', 'status_persiapan')) {
            $table->string('status_persiapan')->default('Belum')->after('tgl_terima');
        }
        if (!Schema::hasColumn('manual_pickings', 'tgl_picking')) {
            $table->date('tgl_picking')->nullable()->after('status_persiapan');
        }
        if (!Schema::hasColumn('manual_pickings', 'pic')) {
            $table->string('pic')->nullable()->after('tgl_picking');
        }
    });
}

public function down(): void
{
    Schema::table('manual_pickings', function (Blueprint $table) {
        $table->dropColumn(['tgl_terima', 'status_persiapan', 'tgl_picking', 'pic']);
    });
}
};
