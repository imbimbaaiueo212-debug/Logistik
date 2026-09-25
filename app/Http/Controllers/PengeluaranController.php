<?php

namespace App\Http\Controllers;

use App\Models\ManualRealisasi;
use App\Models\RealisasiAktif;
use App\Models\RealisasiPasif;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    /**
     * Rekap Pengeluaran = gabungan seluruh RA (Rekap Aktual), yaitu barang
     * yang benar-benar sudah turun PL dan diproses gudang (picking/QC/packing),
     * BUKAN dari status order mentah (bimbashop_orders) atau settlement
     * pembayaran gateway (casdana_transactions) — keduanya bisa "completed"
     * / "settled" tanpa barang benar-benar sudah direalisasikan.
     *
     * Sumbernya:
     * - RA Aktif   : realisasi_aktif   (unit Aktif / biMBA Shop yang sudah diproses gudang)
     * - RA Pasif   : realisasi_pasif   (unit Pasif / majalah pasif yang sudah diproses gudang)
     * - Manual     : manual_realisasi  (order manual: Modul, Sertifikat, dll)
     *
     * Ketiganya berbentuk sama: dikelompokkan per rekap_number, dijumlah dari
     * kolom jumlah_bayar, difilter berdasarkan tgl_bayar.
     */
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        // ===== 1. RA Aktif =====
        $aktifRekap = RealisasiAktif::query()
            ->whereNotNull('rekap_number')
            ->whereBetween('tgl_bayar', [$startDate, $endDate])
            ->selectRaw('rekap_number, COUNT(*) as jumlah_order, SUM(jumlah_bayar) as total_bayar, MIN(tgl_bayar) as tgl_bayar')
            ->groupBy('rekap_number')
            ->orderByDesc('tgl_bayar')
            ->get();

        $aktifTotal = $aktifRekap->sum('total_bayar');
        $aktifOrderCount = $aktifRekap->sum('jumlah_order');
        $aktifRekapCount = $aktifRekap->count();

        // ===== 2. RA Pasif =====
        $pasifRekap = RealisasiPasif::query()
            ->whereNotNull('rekap_number')
            ->whereBetween('tgl_bayar', [$startDate, $endDate])
            ->selectRaw('rekap_number, COUNT(*) as jumlah_order, SUM(jumlah_bayar) as total_bayar, MIN(tgl_bayar) as tgl_bayar')
            ->groupBy('rekap_number')
            ->orderByDesc('tgl_bayar')
            ->get();

        $pasifTotal = $pasifRekap->sum('total_bayar');
        $pasifOrderCount = $pasifRekap->sum('jumlah_order');
        $pasifRekapCount = $pasifRekap->count();

        // ===== 3. Manual =====
        $manualRekap = ManualRealisasi::query()
            ->whereNotNull('rekap_number')
            ->whereBetween('tgl_bayar', [$startDate, $endDate])
            ->selectRaw('rekap_number, COUNT(*) as jumlah_order, SUM(jumlah_bayar) as total_bayar, MIN(tgl_bayar) as tgl_bayar')
            ->groupBy('rekap_number')
            ->orderByDesc('tgl_bayar')
            ->get();

        $manualTotal = $manualRekap->sum('total_bayar');
        $manualOrderCount = $manualRekap->sum('jumlah_order');
        $manualRekapCount = $manualRekap->count();

        $grandTotal = $aktifTotal + $pasifTotal + $manualTotal;

        return view('pengeluaran.index', [
            'startDate'        => $startDate,
            'endDate'          => $endDate,

            'aktifRekap'       => $aktifRekap,
            'aktifTotal'       => $aktifTotal,
            'aktifOrderCount'  => $aktifOrderCount,
            'aktifRekapCount'  => $aktifRekapCount,

            'pasifRekap'       => $pasifRekap,
            'pasifTotal'       => $pasifTotal,
            'pasifOrderCount'  => $pasifOrderCount,
            'pasifRekapCount'  => $pasifRekapCount,

            'manualRekap'      => $manualRekap,
            'manualTotal'      => $manualTotal,
            'manualOrderCount' => $manualOrderCount,
            'manualRekapCount' => $manualRekapCount,

            'grandTotal'       => $grandTotal,
        ]);
    }
}