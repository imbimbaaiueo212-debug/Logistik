<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unit_kemitraan', function (Blueprint $table) {

            $table->string('status_pengelolaan')
                  ->nullable()
                  ->after('status');

            $table->string('mitra_pengelolaan')
                  ->nullable()
                  ->after('status_pengelolaan');

        });
    }

    public function down(): void
    {
        Schema::table('unit_kemitraan', function (Blueprint $table) {

            $table->dropColumn([
                'status_pengelolaan',
                'mitra_pengelolaan',
            ]);

        });
    }
};