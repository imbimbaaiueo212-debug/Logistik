<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pickings', function (Blueprint $table) {
            $table->id();
            $table->string('no_pl')->unique();
            $table->date('tgl_order')->nullable();
            $table->date('tgl_picking')->nullable();
            $table->string('jam_picking')->nullable();
            $table->string('nama_unit')->nullable();
            $table->string('billing_last_name')->nullable();
            $table->string('billing_company')->nullable();
            $table->string('status_kirim')->default('Ambil Sendiri');
            $table->integer('total_item')->default(0);
            $table->integer('total_qty')->default(0);
            $table->string('dipicking_oleh')->nullable();
            $table->enum('status', ['draft', 'in_progress', 'completed', 'printed'])->default('draft');
            $table->timestamp('printed_at')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('picking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('picking_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->string('item_sku');
            $table->integer('item_qty');
            $table->integer('qty_picked')->default(0);
            $table->boolean('cek')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('picking_items');
        Schema::dropIfExists('pickings');
    }
};