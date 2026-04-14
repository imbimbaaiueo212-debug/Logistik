<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();

            $table->enum('type', [
                'IN',
                'OUT',
                'TRANSFER_IN',
                'TRANSFER_OUT',
                'ADJUSTMENT',
                'RETURN'
            ]);

            $table->integer('qty'); // bisa + atau -

            $table->integer('stock_before');
            $table->integer('stock_after');

            $table->string('reference_type')->nullable(); // GR, TRANSFER, dll
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
