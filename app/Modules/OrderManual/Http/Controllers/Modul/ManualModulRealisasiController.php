<?php

namespace App\Modules\OrderManual\Http\Controllers\Modul;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ManualModulRealisasiController extends Controller
{


    public function realisasi(Request $request)
{
    $query = DB::table('manual_modul_realisasis as r')
        ->leftJoin('manual_modul_orders as o', 'o.id', '=', 'r.manual_modul_order_id')
        ->leftJoin('manual_modul_pickings as p', 'p.manual_modul_realisasi_id', '=', 'r.id')
        ->select([
            'r.id',
            'r.manual_modul_order_id',
            'r.no_pl',
            'r.tgl_turun_pl',
            'r.nama_unit',
            'r.billing_last_name',
            'r.billing_company',
            'r.kategori_order',
            'r.nama_barang',
            'r.rekap_number',
            'r.picking_printed_at',
            'r.printed_at',
            'r.created_at',
            'o.order_id',
            'o.product_sku',
            'o.qty',
            'o.grup',
            'o.status_kirim',
            'o.ekspedisi',
            'o.service_pengiriman',
            'o.catatan',
            'p.id as picking_id',
            'p.status as picking_status',
            'p.printed_at as picking_printed_at_p',
        ]);

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

    $allData = $query
        ->orderBy('r.tgl_turun_pl')
        ->orderBy('r.id')
        ->get();

    // Assign rekap_number per tanggal
    foreach ($allData->groupBy(fn ($item) => Carbon::parse($item->tgl_turun_pl)->toDateString()) as $tanggal => $rows) {
        $rekapNumber = $this->generateRekapNumberModul($tanggal);

        DB::table('manual_modul_realisasis')
            ->whereIn('id', $rows->pluck('id'))
            ->whereNull('rekap_number')
            ->update([
                'rekap_number' => $rekapNumber,
                'updated_at'   => now(),
            ]);

        foreach ($rows as $row) {
            if (empty($row->rekap_number)) {
                $row->rekap_number = $rekapNumber;
            }
        }
    }

    $groupedData = $allData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    return view('ordermanual::order-manual.modul.realisasi-index', [
        'data'        => $allData,
        'groupedData' => $groupedData,
    ]);
}

private function generateRekapNumberModul($tanggal)
{
    $date = Carbon::parse($tanggal)->format('ymd');

    $existing = DB::table('manual_modul_realisasis')
        ->whereDate('tgl_turun_pl', $tanggal)
        ->whereNotNull('rekap_number')
        ->orderBy('rekap_number')
        ->value('rekap_number');

    if ($existing) {
        return $existing;
    }

    return 'RAMM-' . $date . '-' . str_pad(1, 4, '0', STR_PAD_LEFT);
}

/** Print Picking List Manual Modul */
public function printPickingList($id)
{
    $main = DB::table('manual_modul_realisasis as r')
        ->leftJoin('manual_modul_pickings as p', 'p.manual_modul_realisasi_id', '=', 'r.id')
        ->leftJoin('manual_modul_orders as o', 'o.id', '=', 'r.manual_modul_order_id')
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
        DB::table('manual_modul_realisasis')->where('id', $id)->update([
            'picking_printed_at' => now(),
            'updated_at'         => now(),
        ]);
    }

    DB::table('manual_modul_pickings')->where('id', $main->picking_id)->update([
        'printed_at' => now(),
        'status'     => 'completed',
        'updated_at' => now(),
    ]);

    $items = DB::table('manual_modul_picking_items')
        ->where('manual_modul_picking_id', $main->picking_id)
        ->orderBy('item_sku')
        ->get();

    return view('ordermanual::order-manual.modul.picking-list', [
        'item'              => $main,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
    ]);
}

private function getRealisasiByIds(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));
    if (empty($ids)) {
        return collect();
    }

    return DB::table('manual_modul_realisasis as r')
        ->leftJoin('manual_modul_orders as o', 'o.id', '=', 'r.manual_modul_order_id')
        ->whereIn('r.id', $ids)
        ->select([
            'r.*',
            'o.order_id',
            'o.product_sku',
            'o.qty',
            'o.grup',
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
        DB::table('manual_modul_realisasis')
            ->whereIn('id', $data->pluck('id'))
            ->whereNull('printed_at')
            ->update(['printed_at' => now(), 'updated_at' => now()]);
    }

    $pdf = Pdf::loadView('ordermanual::order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA Prising - Manual Modul',
        'tipe'  => 'prising',
    ])->setPaper('a4', 'landscape');

    return $pdf->stream('RA-Prising-Manual-Modul-' . now()->format('Ymd-His') . '.pdf');
}

public function printPemesanan(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    $pdf = Pdf::loadView('ordermanual::order-manual.modul.print-ra-picking', [
        'data'  => $data,
        'title' => 'RA Picking - Manual Modul',
        'tipe'  => 'pemesanan',
    ])->setPaper('a4', 'landscape');

    $filename = 'RA-Picking-Manual-Modul-' . now()->format('Ymd-His') . '.pdf';

    // View di browser (bukan download)
    return $pdf->stream($filename);
}

public function printQc(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    return view('ordermanual::order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA QC - Manual Modul',
        'tipe'  => 'qc',
    ]);
}

public function printPacking(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    return view('ordermanual::order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA Packing - Manual Modul',
        'tipe'  => 'packing',
    ]);
}

public function printEkspedisi(Request $request)
{
    $data = $this->getRealisasiByIds($request);
    if ($data->isEmpty()) {
        return back()->with('error', 'Tidak ada data.');
    }

    return view('ordermanual::order-manual.modul.print-ra', [
        'data'  => $data,
        'title' => 'RA Ekspedisi - Manual Modul',
        'tipe'  => 'ekspedisi',
    ]);
}
}
