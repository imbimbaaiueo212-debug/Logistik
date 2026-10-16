<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\JakartaAktif;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Order\Models\RealisasiAktif;
use App\Modules\Picking\Models\Picking;          // Tambahkan
use App\Modules\Picking\Models\PickingItem;      // Tambahkan
use App\Modules\Master\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Modules\Order\Http\Controllers\Concerns\AktifPickingHelpers;

class RealisasiAktifController extends Controller
{
    use AktifPickingHelpers;

// ====================== MENU PRINT (Sudah Diproses) ======================
public function jakartaPrinted(Request $request)
{
    $query = RealisasiAktif::query();

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
        'picking',
        'product',
    ])
    ->orderBy('tgl_turun_pl')
    ->orderBy('created_at')
    ->paginate($perPage)
    ->appends($request->query());

$allData = (clone $query)
    ->with([
        'picking',
        'product',
    ])
    ->orderBy('tgl_turun_pl')
    ->orderBy('created_at')
    ->get();

    // =====================================================
    // ASSIGN REKAP NUMBER BERDASARKAN SELURUH DATA
    // =====================================================
    if ($allData->isNotEmpty()) {

        foreach ($allData->groupBy(function ($item) {
            return Carbon::parse($item->tgl_turun_pl)->toDateString();
        }) as $tanggal => $rows) {

            $rekapNumber = $this->generateRekapNumber($tanggal);

            RealisasiAktif::whereIn('id', $rows->pluck('id'))
                ->whereNull('rekap_number')
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

    return view('order::order.jakarta-printed', [
        'data'        => $data,
        'groupedData' => $groupedData,
    ]);
}


/**
 * Hapus data dari Realisasi Aktif (Jakarta Printed)
 */
public function deleteRealisasi($id)
{
    $item = RealisasiAktif::findOrFail($id);   // ← Ganti ke RealisasiAktif

    $item->delete();

    return redirect()->route('order.jakarta-printed')
                     ->with('success', '✅ Data berhasil dihapus dari Realisasi Aktif!');
}
public function printRealisasiPdf(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data beserta relasi
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with(['jakartaAktif'])
        ->get();

    // Hanya yang Picking sudah selesai
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang siap dicetak (Picking List belum selesai).');
    }

    // Tandai sudah print
    if ($request->boolean('mark_printed')) {
        RealisasiAktif::whereIn('id', $filteredData->pluck('id'))
            ->whereNull('printed_at')
            ->update([
                'printed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    // Refresh data
    $filteredData = RealisasiAktif::whereIn('id', $filteredData->pluck('id'))
        ->with(['jakartaAktif'])
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Urutkan data
    |--------------------------------------------------------------------------
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // tanggal
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // nomor order
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    // Nomor dokumen
    $firstDate = optional($filteredData->first())->tgl_turun_pl;

    $docNumber = $this->generateRekapNumber($firstDate);

    // Generate PDF
    $pdf = PDF::loadView('order::order.jakarta-printed-pdf', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'            => 'sans-serif',
        'isHtml5ParserEnabled'   => true,
        'isRemoteEnabled'        => true,
    ]);

    return $pdf->stream(
        'RA-Pricing-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}


private function generateRekapNumber($tanggal = null)
{
    if (!$tanggal) {
        $tanggal = Carbon::now('Asia/Jakarta')->toDateString();
    }

    $tanggal = Carbon::parse($tanggal)->toDateString();

    // Cek apakah sudah ada rekap_number untuk tanggal tersebut
    $existing = RealisasiAktif::whereDate('tgl_turun_pl', $tanggal)
        ->whereNotNull('rekap_number')
        ->value('rekap_number');

    if ($existing) {
        return $existing;
    }

    // Ambil nomor tertinggi yang pernah ada
    $lastNumber = RealisasiAktif::whereNotNull('rekap_number')
        ->max(DB::raw("CAST(REPLACE(rekap_number, '#', '') AS UNSIGNED)"));

    $next = ($lastNumber ?? 0) + 1;

    return '#' . str_pad($next, 4, '0', STR_PAD_LEFT);
}

/**
 * Update rekap_number secara per tanggal
 */
public function assignRekapNumber($ids)
{
    if (empty($ids)) {
        return 0;
    }

    $data = RealisasiAktif::whereIn('id', $ids)
        ->whereNull('rekap_number')
        ->orderBy('tgl_turun_pl')
        ->get();

    $updatedCount = 0;

    foreach ($data->groupBy(fn($row) => Carbon::parse($row->tgl_turun_pl)->toDateString()) as $tanggal => $rows) {
        
        $rekapNumber = $this->generateRekapNumber($tanggal);

        $affected = RealisasiAktif::whereIn('id', $rows->pluck('id'))
            ->whereNull('rekap_number')
            ->update([
                'rekap_number' => $rekapNumber,
                'updated_at'   => now(),
            ]);

        $updatedCount += $affected;
    }

    return $updatedCount;
}

// Contoh di method bulk atau print rekap
public function assignRekapBulk(Request $request)
{
    $ids = $request->input('ids'); // array of realisasi ids

    $updated = $this->assignRekapNumber($ids);

    return redirect()->back()
        ->with('success', "$updated rekap number berhasil ditambahkan.");
}

public function printSingleRealisasi($id)
{
    $item = RealisasiAktif::findOrFail($id);

    if (!$item->printed_at) {
        $item->update(['printed_at' => now()]);
    }

    $data = collect([$item]); // agar view PDF tetap bisa pakai @foreach

    $pdf = PDF::loadView('order::order.jakarta-printed-pdf', compact('data'))
               ->setPaper('A5', 'landscape')
               ->setOptions([
                   'defaultFont' => 'sans-serif',
                   'isHtml5ParserEnabled' => true,
                   'isRemoteEnabled' => true,
               ]);

    return $pdf->stream('Realisasi-' . $item->no_pl . '.pdf');
}
// Tambahkan method ini
public function markPickingPrinted($id)
{
    $item = RealisasiAktif::findOrFail($id);

    $item->update([
        'picking_printed_at' => now()
    ]);

    return response()->json([
        'success' => true
    ]);
}
/**
 * Print Picking List - Multi Item dari BimbashopOrder
 */
/**
 * Print Picking List - Menggunakan PickingItem (Sudah difilter per kategori)
 */
public function printPickingList($id)
{
    $main = RealisasiAktif::with([
        'picking.pickingItems.product',
        'jakartaAktif.items.product',
        'jakartaAktif',
    ])->findOrFail($id);

    // Jika picking belum ada → generate otomatis
    if (!$main->picking) {
        $jakarta = $main->jakartaAktif;

        if (!$jakarta) {
            return back()->with('error', 'Data Jakarta Aktif tidak ditemukan.');
        }

        // Filter item sesuai kategori realisasi
        $routeKey = match ($main->kategori_order) {
            'Modul'      => 'order.modul',
            'Majalah'    => 'order.majalah',
            'Sertifikat' => 'order.sertifikat',
            default      => 'order.jakarta-aktif',
        };

        $items = $this->getFilteredItems($jakarta, $routeKey);

        if ($items->isEmpty()) {
            $items = $jakarta->items()->with('product')->get();
        }

        if ($items->isEmpty()) {
            return back()->with('error', 'Tidak ada item untuk dibuatkan Picking List.');
        }

        $this->createPicking($main, $items);
        $main->load(['picking.pickingItems.product']);
    }

    if (!$main->picking) {
        return back()->with('error', 'Picking belum dibuat dan gagal digenerate.');
    }

    // Tandai sudah dicetak
    if (is_null($main->picking_printed_at)) {
        $main->update(['picking_printed_at' => now()]);
    }

    $items = $main->picking
        ->pickingItems
        ->sortBy('item_sku')
        ->values();

    return view('order::order.picking-list', [
        'item'              => $main,
        'picking'           => $main->picking,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
        'jakarta_aktif'     => $main->jakartaAktif,
    ]);
}

/**
 * Print Picking List PDF - Menggunakan PickingItem
 */
public function printPickingListPdf($id)
{
    $main = RealisasiAktif::with([
        'picking.pickingItems',
        'jakartaAktif'                          // ← relasi ditambahkan
    ])->findOrFail($id);

    // Tandai sudah dicetak
    if (!$main->picking_printed_at) {
        $main->update(['picking_printed_at' => now()]);
    }

    $picking = $main->picking;

    if (!$picking) {
        abort(404, 'Picking data not found');
    }

    $items = $picking->pickingItems()
                ->orderBy('item_sku')
                ->get()
                ->transform(function ($item) {
                    // Rapikan nama produk
                    $item->item_name = preg_replace('/\s+/', ' ', trim($item->item_name));
                    return $item;
                });

    $pdf = Pdf::loadView('order::order.picking-list-pdf', [
        'item'              => $main,
        'picking'           => $picking,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
        'jakarta_aktif'     => $main->jakartaAktif,   // ← dikirim ke view PDF
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

    $filename = 'Picking_List_' . ($main->no_pl ?? 'unknown') . 
                '_' . ($main->kategori_order ?? '') . 
                '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->stream($filename);
}

/**
 * Print QC - Hanya data yang picking list sudah selesai
 */
public function printQC(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking List sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk QC.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal Turun PL
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order::order.print-qc', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream(
        'QC-Report-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

/**
 * Print Pemesanan (RA Picking)
 */
public function printPemesanan(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking selesai
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah kategori / item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            // Hitung jumlah item dari nama_barang
            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    // Group per tanggal
    $groupedData = $filteredData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order::order.print-pemesanan', [
        'data'        => $filteredData,
        'groupedData' => $groupedData,
        'docNumber'   => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'           => 'sans-serif',
        'isHtml5ParserEnabled'  => true,
        'isRemoteEnabled'       => true,
    ]);

    return $pdf->stream(
        'RA-Pemesanan-Picking-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

/**
 * Print Packing - Hanya data yang picking list sudah selesai
 */
public function printPacking(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking List sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk Packing.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal Turun PL
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order::order.print-packing', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream(
        'Packing-Report-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

/**
 * Print Distribusi / Ekspedisi - Hanya data yang picking list sudah selesai
 */
public function printEkspedisi(Request $request)
{
    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    // Ambil data
    $data = RealisasiAktif::whereIn('id', $ids)
        ->with('jakartaAktif')
        ->get();

    // Hanya yang Picking List sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk Distribusi.');
    }

    /*
    |--------------------------------------------------------------------------
    | Urutan:
    | 1. Jumlah item paling sedikit
    | 2. Tanggal Turun PL
    | 3. Nomor PL
    |--------------------------------------------------------------------------
    */
    $filteredData = $filteredData
        ->sort(function ($a, $b) {

            $countA = empty($a->nama_barang)
                ? 0
                : substr_count($a->nama_barang, '|') + 1;

            $countB = empty($b->nama_barang)
                ? 0
                : substr_count($b->nama_barang, '|') + 1;

            // Jumlah item
            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            // Tanggal Turun PL
            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            // Nomor PL
            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);

        })
        ->values();

    $docNumber = $this->generateRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    $pdf = PDF::loadView('order::order.print-ekspedisi', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
    ]);

    return $pdf->stream(
        'Ekspedisi-Report-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

public function printTandaTerima($id)
{
    $item = RealisasiAktif::findOrFail($id);

    $pdf = Pdf::loadView('order::order.tanda-terima-pdf', [
        'no_pl'     => $item->no_pl,
        'nama_unit' => $item->nama_unit,
    ])
    ->setPaper('a6', 'portrait')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
    ]);

    return $pdf->stream('Tanda-Terima-' . ($item->no_pl ?? $item->id) . '.pdf');
}
}
