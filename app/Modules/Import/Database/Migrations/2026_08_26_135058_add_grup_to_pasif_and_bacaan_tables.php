<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pasif_periodes', function (Blueprint $table) {
            $table->string('grup', 5)->default('F')->after('no_ps');
        });

        Schema::table('bacaan_periodes', function (Blueprint $table) {
            $table->string('grup', 5)->default('F')->after('no_ps');
        });
    }

    public function down(): void
    {
        Schema::table('pasif_periodes', function (Blueprint $table) {
            $table->dropColumn('grup');
        });

        Schema::table('bacaan_periodes', function (Blueprint $table) {
            $table->dropColumn('grup');
        });
    }
};