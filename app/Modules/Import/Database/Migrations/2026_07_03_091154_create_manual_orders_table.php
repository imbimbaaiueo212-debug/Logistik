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
        Schema::create('manual_orders', function (Blueprint $table) {
    $table->id();

    $table->date('order_date');

    $table->string('customer_name');
    $table->string('phone')->nullable();

    $table->string('product_sku');
    $table->string('product_name');

    $table->integer('qty')->default(1);

    $table->decimal('price',15,2)->default(0);
    $table->decimal('total',15,2)->default(0);

    $table->text('address')->nullable();

    $table->string('payment_method')->nullable();

    $table->enum('status',[
        'Pending',
        'Diproses',
        'Selesai',
        'Batal'
    ])->default('Pending');

    $table->text('notes')->nullable();

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_orders');
    }
};
