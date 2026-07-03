<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qc_outgoings', function (Blueprint $table) {
            $table->string('kode_qc')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('qc_outgoings', function (Blueprint $table) {
            $table->string('kode_qc')->nullable(false)->change();
        });
    }
};
