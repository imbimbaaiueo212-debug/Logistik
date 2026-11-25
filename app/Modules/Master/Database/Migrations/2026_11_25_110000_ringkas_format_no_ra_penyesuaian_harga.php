<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** RAPH-20261125-0001  ->  RA261125-01 */
    public function up(): void
    {
        $rows = DB::table('penyesuaian_harga_batches')->where('no_ra', 'like', 'RAPH-%')->orderBy('id')->get(['id', 'no_ra']);
        foreach ($rows as $r) {
            if (preg_match('/^RAPH-(\d{2})(\d{6})-(\d+)$/', $r->no_ra, $m)) {
                DB::table('penyesuaian_harga_batches')->where('id', $r->id)->update([
                    'no_ra' => 'RA' . substr($m[1] . $m[2], 2, 6) . '-' . str_pad((string) ((int) $m[3]), 2, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }

    public function down(): void
    {
        // tidak dikembalikan: format lama hanya tampilan
    }
};
