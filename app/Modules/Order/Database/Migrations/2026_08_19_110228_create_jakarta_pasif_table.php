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
    Schema::create('jakarta_pasif', function (Blueprint $table) {
        $table->id();
        $table->date('tgl_input')->nullable();
        $table->dateTime('tgl_pesan')->nullable();
        $table->string('kirim')->nullable();
        $table->string('no_telpon')->nullable();
        $table->text('alamat_kirim')->nullable();
        $table->string('kab_kota_provinsi')->nullable();
        $table->integer('ongkir')->default(0);
        $table->string('nama_unit')->nullable();
        $table->string('pesanan')->nullable();
        $table->decimal('harga', 15, 2)->default(0);
        $table->decimal('berat', 10, 2)->default(0);
        $table->integer('item_qty')->default(0);
        $table->decimal('total', 15, 2)->default(0);
        $table->string('jenis_bank')->nullable();
        $table->string('status_pembayaran')->nullable();
        $table->string('status_pesan')->nullable();
        $table->string('id_pesan')->nullable()->index();
        $table->string('status')->default('aktif');
        $table->dateTime('payment_date')->nullable();
        $table->decimal('amount', 15, 2)->default(0);
        $table->string('billing_last_name')->nullable();
        $table->string('billing_company')->nullable();
        $table->string('status_kirim')->nullable();
        $table->string('ekspedisi')->nullable();
        $table->string('service_pengiriman')->nullable();
        $table->dateTime('estimasi_print_pl')->nullable();
        $table->dateTime('estimasi_persiapan')->nullable();
        $table->boolean('is_processed')->default(false);
        $table->dateTime('processed_at')->nullable();
        $table->string('validasi')->nullable();
        $table->text('catatan')->nullable();
        $table->string('grup')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jakarta_pasif');
    }
};
