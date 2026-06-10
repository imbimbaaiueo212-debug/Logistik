<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('casdana_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->string('merchant');
            $table->string('customer');
            $table->string('status');
            $table->dateTime('payment_date')->nullable();
            $table->string('payment_channel')->nullable();
            $table->string('payment_code')->nullable();
            $table->decimal('amount', 15, 2);
            $table->text('raw_data')->nullable(); // untuk menyimpan data lengkap dari excel
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('casdana_transactions');
    }
};