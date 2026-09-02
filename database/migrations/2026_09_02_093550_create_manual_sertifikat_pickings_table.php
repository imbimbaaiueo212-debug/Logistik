<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manual_sertifikat_pickings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('manual_sertifikat_realisasi_id')
                  ->nullable()
                  ->index();

            $table->string('status', 50)->default('pending')->index()
                  ->comment('pending / completed');
            $table->dateTime('printed_at')->nullable();

            $table->timestamps();

            // $table->foreign('manual_sertifikat_realisasi_id')
            //       ->references('id')
            //       ->on('manual_sertifikat_realisasis')
            //       ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_sertifikat_pickings');
    }
};