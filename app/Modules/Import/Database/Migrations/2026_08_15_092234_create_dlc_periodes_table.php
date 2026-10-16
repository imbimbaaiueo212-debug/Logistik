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
        Schema::create('dlc_periodes', function (Blueprint $table) {
    $table->id();
    $table->string('edisi', 20);                 // M159
    $table->string('judul', 100)->nullable();    // Majalah M159 (Juli)
    $table->string('periode', 50);               // 23-31 2026
    $table->string('bulan', 20)->nullable();
    $table->year('tahun')->nullable();
    $table->string('status', 20)->default('draft'); // draft, aktif, selesai
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dlc_periodes');
    }
};
