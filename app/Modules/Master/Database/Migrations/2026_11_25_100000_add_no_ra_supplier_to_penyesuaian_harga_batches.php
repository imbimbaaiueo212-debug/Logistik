<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            if (! Schema::hasColumn('penyesuaian_harga_batches', 'no_ra')) {
                $table->string('no_ra', 30)->nullable()->unique()->after('id');
            }
            if (! Schema::hasColumn('penyesuaian_harga_batches', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->index()->after('no_ra');
            }
        });

        // Import yang sudah ada diberi No RA juga: RAPH-<tanggal dibuat>-<urut per hari>
        $urut = [];
        foreach (DB::table('penyesuaian_harga_batches')->whereNull('no_ra')->orderBy('id')->get(['id', 'created_at']) as $b) {
            $tgl = date('Ymd', strtotime($b->created_at ?: 'now'));
            $urut[$tgl] = ($urut[$tgl] ?? 0) + 1;
            DB::table('penyesuaian_harga_batches')->where('id', $b->id)->update([
                'no_ra' => 'RAPH-' . $tgl . '-' . str_pad((string) $urut[$tgl], 4, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('penyesuaian_harga_batches', function (Blueprint $table) {
            if (Schema::hasColumn('penyesuaian_harga_batches', 'no_ra')) {
                $table->dropUnique(['no_ra']);
                $table->dropColumn('no_ra');
            }
            if (Schema::hasColumn('penyesuaian_harga_batches', 'supplier_id')) {
                $table->dropColumn('supplier_id');
            }
        });
    }
};
