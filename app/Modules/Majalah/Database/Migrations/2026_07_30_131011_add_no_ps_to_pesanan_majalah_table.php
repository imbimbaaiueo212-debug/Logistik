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
    Schema::table('pesanan_majalah', function (Blueprint $table) {
        $table->string('no_ps', 50)->nullable()->after('judul')->index();
    });
}

public function down(): void
{
    Schema::table('pesanan_majalah', function (Blueprint $table) {
        $table->dropColumn('no_ps');
    });
}
};
