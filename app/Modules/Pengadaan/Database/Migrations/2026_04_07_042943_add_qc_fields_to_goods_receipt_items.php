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
        Schema::table('goods_receipt_items', function (Blueprint $table) {

    $table->integer('qty_received')->default(0);

    $table->integer('qty_ok')->nullable();
    $table->integer('qty_reject')->nullable();

    $table->enum('qc_status', ['pending', 'done'])->default('pending');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('goods_receipt_items', function (Blueprint $table) {
            //
        });
    }
};
