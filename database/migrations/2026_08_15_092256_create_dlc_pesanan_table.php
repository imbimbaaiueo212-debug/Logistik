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
        Schema::create('dlc_pesanan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('dlc_periode_id')->constrained('dlc_periodes')->cascadeOnDelete();
    $table->string('nama_unit', 150);
    $table->unsignedInteger('qty')->default(0);
    $table->string('no_cab', 20)->nullable();
    $table->text('alamat')->nullable();
    $table->string('telepon', 20)->nullable();
    $table->text('keterangan')->nullable();
    $table->timestamps();

    $table->index(['dlc_periode_id', 'nama_unit']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dlc_pesanan');
    }
};
