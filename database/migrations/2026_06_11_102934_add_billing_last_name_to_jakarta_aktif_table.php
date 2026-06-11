<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('jakarta_aktif', function (Blueprint $table) {
        $table->string('billing_last_name')->nullable()->after('nama_unit');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jakarta_aktif', function (Blueprint $table) {
            //
        });
    }
};
