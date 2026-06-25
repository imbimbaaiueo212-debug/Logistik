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
        $table->boolean('picking_generated')->default(false);
        $table->foreignId('picking_id')->nullable()->constrained('pickings');
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
