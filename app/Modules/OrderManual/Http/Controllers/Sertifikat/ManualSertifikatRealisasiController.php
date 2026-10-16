<?php

namespace App\Modules\OrderManual\Http\Controllers\Sertifikat;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ManualSertifikatRealisasiController extends Controller
{


    public function realisasi(Request $request)
{
    $query = DB::table('manual_sertifikat_realisasis as r')
        ->leftJoin('manual_sertifikat_orders as o', 'o.id', '=', 'r.manual_sertifikat_order_id')
        ->leftJoin('manual_sertifikat_pickings as p', 'p.manual_sertifikat_realisasi_id', '=', 'r.id')
        ->select([
            'r.*',
            'o.order_id', 'o.product_sku', 'o.qty',
            'o.status_kirim', 'o.ekspedisi', 'o.service_pengiriman', 'o.catatan',
            'p.id as picking_id', 'p.status as picking_status',
        ]);

    // ➕ TAMBAHKAN INI
    if ($request->filled('kategori') && $request->kategori !== 'Semua') {
        $query->where('r.kategori_order', $request->kategori);
    }

    if ($request->filled('nama_unit')) {
        $query->where('r.nama_unit', 'like', '%' . $request->nama_unit . '%');
    }
    if ($request->filled('no_pl')) {
        $query->where('r.no_pl', 'like', '%' . $request->no_pl . '%');
    }
    if ($request->filled('start_date')) {
        $query->whereDate('r.tgl_turun_pl', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('r.tgl_turun_pl', '<=', $request->end_date);
    }

    $allData = $query->orderBy('r.tgl_turun_pl')->orderBy('r.id')->get();

    return view('ordermanual::order-manual.sertifikat.realisasi-index', [
        'data'        => $allData,
        'groupedData' => $allData->groupBy(fn ($i) => Carbon::parse($i->tgl_turun_pl)->toDateString()),
    ]);
}

           // ========================================
    // PRINT & PICKING LIST (sama seperti Modul)
    // ========================================

    public function pickingList($id)
    {
        $main = DB::table('manual_sertifikat_realisasis as r')
            ->leftJoin('manual_sertifikat_pickings as p', 'p.manual_sertifikat_realisasi_id', '=', 'r.id')
            ->leftJoin('manual_sertifikat_orders as o', 'o.id', '=', 'r.manual_sertifikat_order_id')
            ->where('r.id', $id)
            ->select(
                'r.*',
                'p.id as picking_id',
                'p.status as picking_status',
                'p.printed_at as picking_printed_at_p',
                'o.product_sku',
                'o.qty',
                'o.status_kirim',
                'o.ekspedisi',
                'o.service_pengiriman'
            )
            ->first();

        if (!$main) {
            return back()->with('error', 'Data realisasi tidak ditemukan.');
        }

        if (!$main->picking_id) {
            return back()->with('error', 'Picking belum dibuat untuk data ini.');
        }

        // Tandai sudah dicetak
        if (is_null($main->picking_printed_at)) {
            DB::table('manual_sertifikat_realisasis')->where('id', $id)->update([
                'picking_printed_at' => now(),
                'updated_at'         => now(),
            ]);
        }

        DB::table('manual_sertifikat_pickings')->where('id', $main->picking_id)->update([
            'printed_at' => now(),
            'status'     => 'completed',
            'updated_at' => now(),
        ]);

        $items = DB::table('manual_sertifikat_picking_items')
            ->where('manual_sertifikat_picking_id', $main->picking_id)
            ->orderBy('item_sku')
            ->get();

        return view('ordermanual::order-manual.sertifikat.picking-list', [
            'item'              => $main,
            'data'              => $items,
            'no_pl'             => $main->no_pl,
            'tgl_order'         => $main->tgl_turun_pl,
            'billing_last_name' => $main->billing_last_name,
            'billing_company'   => $main->billing_company,
            'kategori_order'    => $main->kategori_order ?? 'SERTIFIKAT',
        ]);
    }

    private function getRealisasiByIds(Request $request)
    {
        $ids = array_filter(explode(',', $request->get('ids', '')));
        if (empty($ids)) {
            return collect();
        }

        return DB::table('manual_sertifikat_realisasis as r')
            ->leftJoin('manual_sertifikat_orders as o', 'o.id', '=', 'r.manual_sertifikat_order_id')
            ->whereIn('r.id', $ids)
            ->select([
                'r.*',
                'o.order_id',
                'o.product_sku',
                'o.qty',
                'o.status_kirim',
                'o.ekspedisi',
                'o.service_pengiriman',
                'o.payment_date',
                'o.order_date',
                'o.catatan as order_catatan',
                'o.shipping_address_1',
                'o.shipping_city',
                'o.phone',
            ])
            ->orderBy('r.tgl_turun_pl')
            ->orderBy('r.no_pl')
            ->get();
    }

    public function printPrising(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        if ($request->boolean('mark_printed')) {
            DB::table('manual_sertifikat_realisasis')
                ->whereIn('id', $data->pluck('id'))
                ->whereNull('printed_at')
                ->update(['printed_at' => now(), 'updated_at' => now()]);
        }

        return view('ordermanual::order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Prising - Manual Sertifikat',
            'tipe'  => 'prising',
        ]);
    }

    public function printPemesanan(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('ordermanual::order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Picking - Manual Sertifikat',
            'tipe'  => 'pemesanan',
        ]);
    }

    public function printQc(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('ordermanual::order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA QC - Manual Sertifikat',
            'tipe'  => 'qc',
        ]);
    }

    public function printPacking(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('ordermanual::order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Packing - Manual Sertifikat',
            'tipe'  => 'packing',
        ]);
    }

    public function printEkspedisi(Request $request)
    {
        $data = $this->getRealisasiByIds($request);
        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data.');
        }

        return view('ordermanual::order-manual.sertifikat.print-ra', [
            'data'  => $data,
            'title' => 'RA Ekspedisi - Manual Sertifikat',
            'tipe'  => 'ekspedisi',
        ]);
    }
}
