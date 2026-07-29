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
    Schema::table('jakarta_aktif', function (Blueprint $table) {
        $table->string('grup', 10)->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('jakarta_aktif', function (Blueprint $table) {
        $table->dropColumn('grup');
    });
}
};
