<?php

namespace App\Http\Controllers;

use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use App\Models\ManualRealisasi;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PengeluaranController extends Controller
{
    /**
     * Rekap gabungan:
     * - biMBA Shop  : order dengan status "completed" (bimbashop_orders.order_total)
     * - Kasdana     : transaksi dengan status SETTLED/PAID (casdana_transactions.amount)
     * - Manual      : TIDAK dihitung dari order mentah (manual_orders/modul/sertifikat),
     *                 cukup dari tabel rekap yang sudah ada (manual_realisasi.jumlah_bayar),
     *                 dikelompokkan per rekap_number.
     */
    public function index(Request $request)
    {
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : now()->startOfMonth();

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : now()->endOfDay();

        // ===== 1. biMBA Shop =====
        $bimbashopQuery = BimbashopOrder::query()
            ->where('status', 'completed')
            ->whereBetween('order_date', [$startDate, $endDate]);

        $bimbashopTotal = (clone $bimbashopQuery)->sum('order_total');
        $bimbashopCount = (clone $bimbashopQuery)->count();

        // ===== 2. Kasdana =====
        $casdanaQuery = CasdanaTransaction::query()
            ->whereIn('status', ['SETTLED', 'PAID'])
            ->whereBetween('payment_date', [$startDate, $endDate]);

        $casdanaTotal = (clone $casdanaQuery)->sum('amount');
        $casdanaCount = (clone $casdanaQuery)->count();

        // ===== 3. Manual (rekapnya saja) =====
        $manualRekap = ManualRealisasi::query()
            ->whereNotNull('rekap_number')
            ->whereBetween('tgl_bayar', [$startDate, $endDate])
            ->selectRaw('rekap_number, COUNT(*) as jumlah_order, SUM(jumlah_bayar) as total_bayar, MIN(tgl_bayar) as tgl_bayar')
            ->groupBy('rekap_number')
            ->orderByDesc('tgl_bayar')
            ->get();

        $manualTotal = $manualRekap->sum('total_bayar');
        $manualCount = $manualRekap->count();

        $grandTotal = $bimbashopTotal + $casdanaTotal + $manualTotal;

        return view('pengeluaran.index', [
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'bimbashopTotal' => $bimbashopTotal,
            'bimbashopCount' => $bimbashopCount,
            'casdanaTotal'   => $casdanaTotal,
            'casdanaCount'   => $casdanaCount,
            'manualTotal'    => $manualTotal,
            'manualCount'    => $manualCount,
            'manualRekap'    => $manualRekap,
            'grandTotal'     => $grandTotal,
        ]);
    }
}