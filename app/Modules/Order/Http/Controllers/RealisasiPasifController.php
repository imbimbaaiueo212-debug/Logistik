<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Picking\Models\Picking;          // Tambahkan
use App\Modules\Order\Models\JakartaPasif;
use App\Modules\Master\Models\Product;
use App\Modules\Order\Models\RealisasiPasif;
use App\Modules\Picking\Models\PickingPasif;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class RealisasiPasifController extends Controller
{

    public function jakartaPasifPrinted(Request $request)
{
    $query = RealisasiPasif::query();

    // ==========================
    // JENIS (rumah): JKTP / DLC / EBT -> masing-masing punya Rekap Aktual sendiri
    // ==========================
    $jenis = strtoupper((string) $request->get('jenis', 'JKTP'));
    if (! in_array($jenis, ['JKTP', 'DLC', 'EBT'], true)) {
        $jenis = 'JKTP';
    }

    $query->where(function ($q) use ($jenis) {
        $q->where('jenis', $jenis);

        // data lama tanpa jenis dianggap JKTP
        if ($jenis === 'JKTP') {
            $q->orWhereNull('jenis');
        }
    });

    // ==========================
    // FILTER
    // ==========================
    if ($request->filled('kategori')) {
        $query->where('kategori_order', $request->kategori);
    }

    if ($request->filled('id_pesan')) {
        $query->where('no_pl', 'like', '%' . $request->id_pesan . '%');
    }

    if ($request->filled('nama_unit')) {
        $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
    }

    if ($request->filled('start_date')) {
        $query->whereDate('tgl_turun_pl', '>=', $request->start_date);
    }

    if ($request->filled('end_date')) {
        $query->whereDate('tgl_turun_pl', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 30);

    $data = (clone $query)
        ->with([
            'pickingPasif',   // relasi ke PickingPasif
            'product',
            'jakartaPasif',
        ])
        ->orderBy('tgl_turun_pl')
        ->orderBy('created_at')
        ->paginate($perPage)
        ->appends($request->query());

    $allData = (clone $query)
        ->with([
            'pickingPasif',
            'product',
            'jakartaPasif',
        ])
        ->orderBy('tgl_turun_pl')
        ->orderBy('created_at')
        ->get();

    // =====================================================
    // ASSIGN REKAP NUMBER BERDASARKAN SELURUH DATA
    // =====================================================
    if ($allData->isNotEmpty()) {

        foreach ($allData->groupBy(fn ($item) => Carbon::parse($item->tgl_turun_pl)->toDateString()) as $tanggal => $rows) {
    $rekapNumber = $this->generateRekapNumberPasif($tanggal, $jenis);

    // Samakan SEMUA baris tanggal ini ke nomor yang sama
    RealisasiPasif::whereIn('id', $rows->pluck('id'))
        ->update([
            'rekap_number' => $rekapNumber,
            'updated_at'   => now(),
        ]);

    foreach ($rows as $row) {
        $row->rekap_number = $rekapNumber;
    }
}
    }

    // =====================================================
    // GROUP BERDASARKAN SELURUH DATA
    // =====================================================
    $groupedData = $allData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    return view('order::order.jakarta-pasif-printed', [
        'data'        => $data,
        'jenis'       => $jenis,
        'groupedData' => $groupedData,
    ]);
}
private function generateRekapNumberPasif($tanggal, $jenis = null)
{
    $jenis  = strtoupper((string) ($jenis ?: 'JKTP'));
    $prefix = ['JKTP' => 'RAP', 'DLC' => 'RAD', 'EBT' => 'RAE'][$jenis] ?? 'RAP';
    $date   = Carbon::parse($tanggal)->format('ymd');

    // Kalau tanggal + jenis ini SUDAH punya rekap_number -> pakai yang sama
    $existing = RealisasiPasif::whereDate('tgl_turun_pl', $tanggal)
        ->where(function ($q) use ($jenis) {
            $q->where('jenis', $jenis);

            if ($jenis === 'JKTP') {
                $q->orWhereNull('jenis');
            }
        })
        ->whereNotNull('rekap_number')
        ->orderBy('rekap_number')
        ->value('rekap_number');

    if ($existing) {
        return $existing; // jangan naik nomor
    }

    // Baru pertama kali untuk tanggal + jenis ini
    return $prefix . '-' . $date . '-' . str_pad(1, 4, '0', STR_PAD_LEFT);
}

   /**
 * Print Picking List Pasif - HTML
 */
public function printPickingListPasif($id)
{
    $main = RealisasiPasif::with([
        'pickingPasif.items.product',
        'jakartaPasif',
    ])->findOrFail($id);

    if (!$main->pickingPasif) {
        return back()->with('error', 'Picking Pasif belum dibuat.');
    }

    // Mark printed
    if (is_null($main->picking_printed_at)) {
        $main->update(['picking_printed_at' => now()]);
    }

    if (is_null($main->pickingPasif->printed_at)) {
        $main->pickingPasif->update([
            'printed_at' => now(),
            'status'     => 'completed',
        ]);
    }

    // Relasi items: coba 'items' dulu, fallback 'pickingItems'
    $itemsCollection = $main->pickingPasif->items
        ?? $main->pickingPasif->pickingItems
        ?? collect();

    $items = $itemsCollection->sortBy('item_sku')->values();

    // Pakai blade PASIF kalau ada, fallback ke aktif
    $view = view()->exists('order::order.picking-list-pasif')
        ? 'order::order.picking-list-pasif'
        : 'order::order.picking-list';

    return view($view, [
        'item'              => $main,
        'picking'           => $main->pickingPasif,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
        'jakarta_pasif'     => $main->jakartaPasif,
        'is_pasif'          => true,
    ]);
}

/**
 * Print Picking List PDF - Pasif
 */
public function printPickingListPdfPasif($id)
{
    $main = RealisasiPasif::with([
        'pickingPasif.items',
        'jakartaPasif',
    ])->findOrFail($id);

    if (!$main->pickingPasif) {
        abort(404, 'Picking Pasif data not found');
    }

    if (is_null($main->picking_printed_at)) {
        $main->update(['picking_printed_at' => now()]);
    }

    if (is_null($main->pickingPasif->printed_at)) {
        $main->pickingPasif->update([
            'printed_at' => now(),
            'status'     => 'completed',
        ]);
    }

    $itemsQuery = method_exists($main->pickingPasif, 'items')
        ? $main->pickingPasif->items()
        : $main->pickingPasif->pickingItems();

    $items = $itemsQuery
        ->orderBy('item_sku')
        ->get()
        ->transform(function ($item) {
            $item->item_name = preg_replace('/\s+/', ' ', trim($item->item_name ?? ''));
            return $item;
        });

    $view = view()->exists('order::order.picking-list-pdf-pasif')
        ? 'order::order.picking-list-pdf-pasif'
        : 'order::order.picking-list-pdf';

    $pdf = Pdf::loadView($view, [
        'item'              => $main,
        'picking'           => $main->pickingPasif,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
        'jakarta_pasif'     => $main->jakartaPasif,
        'is_pasif'          => true,
    ]);

    $pdf->setPaper('A5', 'portrait');
    $pdf->setOptions([
        'margin-top'           => 8,
        'margin-right'         => 6,
        'margin-bottom'        => 18,
        'margin-left'          => 6,
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled'         => true,
    ]);

    $filename = 'Picking_List_Pasif_' . ($main->no_pl ?? 'unknown') .
                '_' . ($main->kategori_order ?? '') .
                '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->stream($filename);
}

/**
 * Mark Picking Printed - Pasif (AJAX)
 */
public function markPickingPrintedPasif($id)
{
    $item = RealisasiPasif::with('pickingPasif')->findOrFail($id);

    $item->update([
        'picking_printed_at' => now(),
    ]);

    if ($item->pickingPasif) {
        $item->pickingPasif->update([
            'printed_at' => now(),
            'status'     => 'completed',
        ]);
    }

    return response()->json(['success' => true]);
}


// ====================== PRINT REALISASI PASIF ======================

/**
 * Cetak RA Prising / Pricing - Pasif
 */
public function printRealisasiPasifPdf(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = RealisasiPasif::whereIn('id', $ids)
        ->with(['jakartaPasif', 'pickingPasif'])
        ->get();

    // Hanya yang picking sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at)
            || !is_null($item->pickingPasif?->printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang siap dicetak (Picking List belum selesai).');
    }

    // Tandai sudah print RA
    if ($request->boolean('mark_printed')) {
        RealisasiPasif::whereIn('id', $filteredData->pluck('id'))
            ->whereNull('printed_at')
            ->update([
                'printed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    $filteredData = RealisasiPasif::whereIn('id', $filteredData->pluck('id'))
        ->with(['jakartaPasif'])
        ->get();

    // Urut: jumlah item sedikit → tgl → no_pl
    $filteredData = $filteredData
        ->sort(function ($a, $b) {
            $countA = empty($a->nama_barang) ? 0 : substr_count($a->nama_barang, '|') + 1;
            $countB = empty($b->nama_barang) ? 0 : substr_count($b->nama_barang, '|') + 1;

            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);
            if ($dateCompare != 0) {
                return $dateCompare;
            }

            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);
        })
        ->values();

    $firstDate = optional($filteredData->first())->tgl_turun_pl;
    // Pakai nomor yang sudah tersimpan di data, jangan generate baru
$docNumber = $filteredData->first()->rekap_number
    ?? $this->generateRekapNumberPasif(
        optional($filteredData->first())->tgl_turun_pl ?? now(),
        optional($filteredData->first())->jenis
    );

    // Reuse view Aktif jika belum ada khusus pasif
    $view = view()->exists('order::order.jakarta-pasif-printed-pdf')
        ? 'order::order.jakarta-pasif-printed-pdf'
        : 'order::order.jakarta-printed-pdf';

    $pdf = Pdf::loadView($view, [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
        'is_pasif'  => true,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream('RA-Pasif-Pricing-' . now()->format('d-m-Y_H-i') . '.pdf');
}

/**
 * Print RA Picking / Pemesanan - Pasif
 */
public function printPemesananPasif(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = RealisasiPasif::whereIn('id', $ids)
        ->with(['jakartaPasif', 'pickingPasif'])
        ->get();

    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at)
            || !is_null($item->pickingPasif?->printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak.');
    }

    $filteredData = $filteredData
        ->sort(function ($a, $b) {
            $countA = empty($a->nama_barang) ? 0 : substr_count($a->nama_barang, '|') + 1;
            $countB = empty($b->nama_barang) ? 0 : substr_count($b->nama_barang, '|') + 1;

            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);
            if ($dateCompare != 0) {
                return $dateCompare;
            }

            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);
        })
        ->values();

    $groupedData = $filteredData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    $docNumber = $this->generateRekapNumberPasif(
        optional($filteredData->first())->tgl_turun_pl ?? now(),
        optional($filteredData->first())->jenis
    );

    $view = view()->exists('order::order.print-pemesanan-pasif')
        ? 'order::order.print-pemesanan-pasif'
        : 'order::order.print-pemesanan';

    $pdf = Pdf::loadView($view, [
        'data'        => $filteredData,
        'groupedData' => $groupedData,
        'docNumber'   => $docNumber,
        'is_pasif'    => true,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream('RA-Pemesanan-Picking-Pasif-' . now()->format('d-m-Y_H-i') . '.pdf');
}

/**
 * Print QC - Pasif
 */
public function printQCPasif(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = RealisasiPasif::whereIn('id', $ids)
        ->with(['jakartaPasif', 'pickingPasif'])
        ->get();

    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at)
            || !is_null($item->pickingPasif?->printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai untuk QC.');
    }

    $filteredData = $filteredData
        ->sort(function ($a, $b) {
            $countA = empty($a->nama_barang) ? 0 : substr_count($a->nama_barang, '|') + 1;
            $countB = empty($b->nama_barang) ? 0 : substr_count($b->nama_barang, '|') + 1;

            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);
            if ($dateCompare != 0) {
                return $dateCompare;
            }

            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);
        })
        ->values();

    $docNumber = $this->generateRekapNumberPasif(
        optional($filteredData->first())->tgl_turun_pl ?? now(),
        optional($filteredData->first())->jenis
    );

    $view = view()->exists('order::order.print-qc-pasif')
        ? 'order::order.print-qc-pasif'
        : 'order::order.print-qc';

    $pdf = Pdf::loadView($view, [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
        'is_pasif'  => true,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream('QC-Report-Pasif-' . now()->format('d-m-Y_H-i') . '.pdf');
}

/**
 * Print Packing - Pasif
 */
public function printPackingPasif(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = RealisasiPasif::whereIn('id', $ids)
        ->with(['jakartaPasif', 'pickingPasif'])
        ->get();

    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at)
            || !is_null($item->pickingPasif?->printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai untuk Packing.');
    }

    $filteredData = $filteredData
        ->sort(function ($a, $b) {
            $countA = empty($a->nama_barang) ? 0 : substr_count($a->nama_barang, '|') + 1;
            $countB = empty($b->nama_barang) ? 0 : substr_count($b->nama_barang, '|') + 1;

            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);
            if ($dateCompare != 0) {
                return $dateCompare;
            }

            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);
        })
        ->values();

    $docNumber = $this->generateRekapNumberPasif(
        optional($filteredData->first())->tgl_turun_pl ?? now(),
        optional($filteredData->first())->jenis
    );

    $view = view()->exists('order::order.print-packing-pasif')
        ? 'order::order.print-packing-pasif'
        : 'order::order.print-packing';

    $pdf = Pdf::loadView($view, [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
        'is_pasif'  => true,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream('Packing-Report-Pasif-' . now()->format('d-m-Y_H-i') . '.pdf');
}

/**
 * Print Ekspedisi - Pasif
 */
public function printEkspedisiPasif(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = RealisasiPasif::whereIn('id', $ids)
        ->with(['jakartaPasif', 'pickingPasif'])
        ->get();

    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at)
            || !is_null($item->pickingPasif?->printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai untuk Distribusi.');
    }

    $filteredData = $filteredData
        ->sort(function ($a, $b) {
            $countA = empty($a->nama_barang) ? 0 : substr_count($a->nama_barang, '|') + 1;
            $countB = empty($b->nama_barang) ? 0 : substr_count($b->nama_barang, '|') + 1;

            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);
            if ($dateCompare != 0) {
                return $dateCompare;
            }

            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);
        })
        ->values();

    $docNumber = $this->generateRekapNumberPasif(
        optional($filteredData->first())->tgl_turun_pl ?? now(),
        optional($filteredData->first())->jenis
    );

    $view = view()->exists('order::order.print-ekspedisi-pasif')
        ? 'order::order.print-ekspedisi-pasif'
        : 'order::order.print-ekspedisi';

    $pdf = Pdf::loadView($view, [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
        'is_pasif'  => true,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream('Ekspedisi-Report-Pasif-' . now()->format('d-m-Y_H-i') . '.pdf');
}
}