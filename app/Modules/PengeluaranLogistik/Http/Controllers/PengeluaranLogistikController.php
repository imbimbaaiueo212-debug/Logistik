<?php

namespace App\Modules\PengeluaranLogistik\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\PengeluaranLogistik\Services\LaporanPengeluaranLogistik;
use App\Modules\PengeluaranLogistik\Support\DaftarStokis;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Report Pengeluaran Logistik: stokis yang order ke Logistik, isi ordernya per kategori,
 * unit yang membeli ke stokis itu, dan transfer gudang manual (qty saja).
 * Kalender dan Majalah tidak dihitung di sini (punya Report Angka Cetak sendiri).
 */
class PengeluaranLogistikController extends Controller
{
    public function index(Request $request, LaporanPengeluaranLogistik $laporan)
    {
        $dari   = $this->tanggal($request->get('dari'));
        $sampai = $this->tanggal($request->get('sampai'));

        $stokis   = strtoupper(trim((string) $request->get('stokis', '')));
        $stokis   = array_key_exists($stokis, DaftarStokis::semua()) ? $stokis : '';
        $kategori = trim((string) $request->get('kategori', ''));

        // On-hold yang sudah SETTLED / SUCCESS di Casdana dinaikkan ke processing (bila servicenya ada di project ini)
        $dinaikkan = 0;
        $sync = \App\Modules\Import\Services\ShopStatusSync::class;
        if (class_exists($sync)) {
            try {
                $dinaikkan = (int) $sync::naikkanOnHold();
            } catch (\Throwable $e) {
                Log::error('Report Pengeluaran Logistik: sync on-hold gagal: ' . $e->getMessage());
            }
        }

        $report = $laporan->build($dari, $sampai, $stokis ?: null, $kategori ?: null);

        return view('pengeluaran-logistik::index', [
            'report'       => $report,
            'dinaikkan'    => $dinaikkan,
            'daftarStokis' => DaftarStokis::semua(),
            'daftarKategori' => $laporan->daftarKategori(),
            'filter'       => [
                'dari'     => $request->get('dari'),
                'sampai'   => $request->get('sampai'),
                'stokis'   => $stokis,
                'kategori' => $kategori,
            ],
        ]);
    }

    private function tanggal($v): ?Carbon
    {
        if (blank($v)) {
            return null;
        }
        try {
            return Carbon::parse($v);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
