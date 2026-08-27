<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('spare_pasifs', function (Blueprint $table) {
            $table->id();

            $table->string('edisi', 20)->index();          // M159, M160, dst
            $table->string('judul')->nullable();
            $table->string('periode')->nullable();
            $table->string('bulan', 20)->nullable();
            $table->string('tahun', 10)->nullable();

            // Snapshot perhitungan
            $table->unsignedInteger('dlc_total')->default(0);
            $table->unsignedInteger('pasif_total')->default(0);
            $table->unsignedInteger('bacaan_total')->default(0);
            $table->unsignedInteger('grand_total')->default(0);

            $table->decimal('spare_raw', 12, 3)->default(0); // hasil * 0.03 sebelum round
            $table->unsignedInteger('spare')->default(0);    // setelah round()
            $table->unsignedInteger('lembar')->default(0);   // ceil(spare / 200)

            $table->string('grup', 5)->default('A')->index(); // ← wajib grup A
            $table->string('status', 20)->default('aktif')->index(); // aktif / nonaktif
            $table->string('no_ps')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            // Satu edisi hanya 1 record aktif (bisa diubah kalau mau history)
            $table->unique(['edisi', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('spare_pasifs');
    }
};