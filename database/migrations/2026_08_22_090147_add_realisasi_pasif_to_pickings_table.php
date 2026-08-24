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
    Schema::table('pickings', function (Blueprint $table) {
        $table->unsignedBigInteger('realisasi_pasif_id')->nullable()->after('realisasi_aktif_id')->index();
        $table->unsignedBigInteger('jakarta_pasif_id')->nullable()->after('jakarta_aktif_id')->index();

        // Optional foreign key
        // $table->foreign('realisasi_pasif_id')->references('id')->on('realisasi_pasif')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('pickings', function (Blueprint $table) {
        $table->dropColumn(['realisasi_pasif_id', 'jakarta_pasif_id']);
    });
}
};
