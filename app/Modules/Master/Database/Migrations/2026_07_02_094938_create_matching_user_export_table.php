<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matching_user_export', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('unit_kemitraan_id');
            $table->unsignedBigInteger('user_export_id')->nullable();

            $table->string('no_cab',100);
            $table->string('billing_last_name')->nullable();

            $table->boolean('status')->default(false);

            $table->timestamps();

            $table->index('unit_kemitraan_id');
            $table->index('user_export_id');
            $table->index('no_cab');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matching_user_export');
    }
};