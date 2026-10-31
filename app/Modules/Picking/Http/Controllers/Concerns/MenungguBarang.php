<?php

namespace App\Modules\Picking\Http\Controllers\Concerns;

use App\Modules\Picking\Models\Picking;
use App\Modules\Picking\Services\KetersediaanBarangService;

/**
 * Picking Majalah/Kalender tertahan (HOLD) selama edisi/tahunnya belum ditandai "datang".
 * Pesanan tetap berjalan di Penjualan; hanya Picking yang menahan dan Picking pula yang membuka.
 */
trait MenungguBarang
{
    /** @return array<int,string> kosong = barang sudah ada / bukan Majalah-Kalender */
    protected function barangBelumDatang(Picking $picking): array
    {
        return app(KetersediaanBarangService::class)->belumDatang($picking);
    }

    protected function responBarangBelumDatang(array $daftar)
    {
        return response()->json([
            'success' => false,
            'message' => 'Barang belum datang (' . implode(', ', $daftar) . '). '
                . 'Tandai "Barang Sudah Datang" dulu agar Picking bisa diproses.',
        ], 423);
    }
}
