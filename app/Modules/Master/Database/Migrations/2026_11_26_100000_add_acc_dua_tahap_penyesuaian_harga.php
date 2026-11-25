<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyesuaian_harga', function (Blueprint $table) {
            if (! Schema::hasColumn('penyesuaian_harga', 'disetujui_beli_pada')) {
                $table->timestamp('disetujui_beli_pada')->nullable();
            }
        });

        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('penyesuaian_harga_batches', 'acc_beli_by')) {
                $table->unsignedBigInteger('acc_beli_by')->nullable();
                $table->timestamp('acc_beli_at')->nullable();
                $table->string('catatan_beli', 500)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('penyesuaian_harga', function (Blueprint $table) {
            $table->dropColumn('disetujui_beli_pada');
        });
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            $table->dropColumn(['acc_beli_by', 'acc_beli_at', 'catatan_beli']);
        });
    }
};
