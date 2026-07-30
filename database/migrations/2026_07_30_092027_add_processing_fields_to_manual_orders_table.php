<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manual_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('manual_orders', 'is_processed')) {
                $table->boolean('is_processed')->default(false)->after('status');
            }
            if (!Schema::hasColumn('manual_orders', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('is_processed');
            }
            if (!Schema::hasColumn('manual_orders', 'status_kirim')) {
                $table->string('status_kirim', 50)->nullable()->after('processed_at');
            }
            if (!Schema::hasColumn('manual_orders', 'ekspedisi')) {
                $table->string('ekspedisi', 100)->nullable();
            }
            if (!Schema::hasColumn('manual_orders', 'service_pengiriman')) {
                $table->string('service_pengiriman', 100)->nullable();
            }
            if (!Schema::hasColumn('manual_orders', 'payment_date')) {
                $table->timestamp('payment_date')->nullable();
            }
            if (!Schema::hasColumn('manual_orders', 'estimasi_print_pl')) {
                $table->timestamp('estimasi_print_pl')->nullable();
            }
            if (!Schema::hasColumn('manual_orders', 'estimasi_persiapan')) {
                $table->timestamp('estimasi_persiapan')->nullable();
            }
            if (!Schema::hasColumn('manual_orders', 'catatan')) {
                $table->text('catatan')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('manual_orders', function (Blueprint $table) {
            $table->dropColumn([
                'is_processed',
                'processed_at',
                'status_kirim',
                'ekspedisi',
                'service_pengiriman',
                'payment_date',
                'estimasi_print_pl',
                'estimasi_persiapan',
                'catatan',
            ]);
        });
    }
};