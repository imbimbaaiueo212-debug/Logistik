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
    Schema::table('packing_pasif', function (Blueprint $table) {
        if (!Schema::hasColumn('packing_pasif', 'tgl_packing')) {
            $table->date('tgl_packing')->nullable()->after('tgl_estimasi');
        }
        if (!Schema::hasColumn('packing_pasif', 'nama_packer')) {
            $table->string('nama_packer')->nullable()->after('pic_packing');
        }
        if (!Schema::hasColumn('packing_pasif', 'berat_aktual')) {
            $table->decimal('berat_aktual', 10, 2)->nullable()->after('berat');
        }
        if (!Schema::hasColumn('packing_pasif', 'koli')) {
            $table->string('koli')->nullable()->after('berat_aktual');
        }
        if (!Schema::hasColumn('packing_pasif', 'keterangan_packing')) {
            $table->string('keterangan_packing')->nullable()->after('keterangan');
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packing_pasif', function (Blueprint $table) {
            //
        });
    }
};
