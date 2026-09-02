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
    Schema::create('manual_modul_realisasis', function (Blueprint $table) {
        $table->id();
        $table->foreignId('manual_modul_order_id')
              ->nullable()
              ->constrained('manual_modul_orders')
              ->nullOnDelete();
        $table->string('no_pl')->nullable()->index();
        $table->date('tgl_turun_pl')->nullable()->index();
        $table->string('nama_unit')->nullable();
        $table->string('billing_last_name')->nullable();
        $table->string('billing_company')->nullable();
        $table->string('kategori_order')->default('Modul');
        $table->text('nama_barang')->nullable();
        $table->string('rekap_number')->nullable()->index();
        $table->dateTime('picking_printed_at')->nullable();
        $table->dateTime('printed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('manual_modul_pickings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('manual_modul_realisasi_id')
              ->nullable()
              ->constrained('manual_modul_realisasis')
              ->nullOnDelete();
        $table->string('status')->default('pending');
        $table->dateTime('printed_at')->nullable();
        $table->timestamps();
    });

    Schema::create('manual_modul_picking_items', function (Blueprint $table) {
        $table->id();
        $table->foreignId('manual_modul_picking_id')
              ->constrained('manual_modul_pickings')
              ->onDelete('cascade');
        $table->string('item_sku')->nullable();
        $table->string('item_name')->nullable();
        $table->integer('qty')->default(0);
        $table->timestamps();
    });
}
};
