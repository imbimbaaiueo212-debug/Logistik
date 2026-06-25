<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickings', function (Blueprint $table) {

            $table->id();
            
            // Kolom utama tanpa foreign key sama sekali
            $table->unsignedBigInteger('jakarta_aktif_id')->nullable();
            
            $table->string('no_pl')->unique();
            $table->date('tgl_order')->nullable();
            $table->date('tgl_picking')->nullable();
            $table->time('jam_picking')->nullable();   // ubah jadi time
            
            $table->string('id_pesan')->nullable();
            $table->string('cabang')->nullable();
            $table->string('vendor')->nullable();
            $table->string('nama_unit')->nullable();

            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();

            $table->string('kirim')->nullable();
            $table->string('no_telpon')->nullable();
            $table->text('alamat_kirim')->nullable();
            $table->string('kab_kota_provinsi')->nullable();

            $table->string('ekspedisi')->nullable();
            $table->string('service_pengiriman')->nullable();
            $table->string('tracking_number')->nullable();

            $table->text('pesanan')->nullable();

            $table->string('jenis_bank')->nullable();
            $table->string('status_pembayaran')->nullable();

            $table->decimal('harga', 15, 2)->default(0);
            $table->decimal('diskon', 15, 2)->default(0);
            $table->decimal('ongkir', 15, 2)->default(0);
            $table->decimal('fee_payment', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->decimal('berat', 10, 2)->default(0);
            $table->decimal('berat_bimbashop', 10, 2)->nullable();
            $table->decimal('berat_aktual', 10, 2)->nullable();

            $table->integer('total_item')->default(0);
            $table->integer('total_qty')->default(0);
            $table->string('dipicking_oleh')->nullable();

            $table->string('pic_qc')->nullable();
            $table->timestamp('qc_at')->nullable();

            $table->string('pic_packing')->nullable();
            $table->timestamp('packing_at')->nullable();

            $table->string('pic_finishing')->nullable();
            $table->timestamp('finishing_at')->nullable();

            $table->timestamp('printed_at')->nullable();

            $table->enum('status', [
                'draft', 'picking', 'checking', 'packing', 
                'finishing', 'completed', 'printed'
            ])->default('draft');

            $table->text('catatan')->nullable();

            // created_by tanpa foreign key
            $table->unsignedBigInteger('created_by')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickings');
    }
};