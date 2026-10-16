<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\ManualOrder;
use App\Modules\Master\Models\UnitNamaMismatch;
use App\Modules\Import\Models\ManualRealisasi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ManualRealisasiController extends Controller
{


// =========================================================
// MENU MANUAL PRINTED (Rekap Aktual Manual)
// =========================================================
public function manualPrinted(Request $request)
{
    $query = ManualRealisasi::query();

    // ===== KHUSUS MAJALAH SAJA =====
    // Paksa hanya tampilkan kategori Majalah
    $query->where('kategori_order', 'Majalah');

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

    // Parameter kategori diabaikan (sudah dipaksa Majalah)
    // if ($request->filled('kategori')) { ... }

    $perPage = $request->get('per_page', 30);

    $data = (clone $query)
        ->with(['picking', 'manualOrder'])
        ->orderBy('tgl_turun_pl')
        ->orderBy('created_at')
        ->paginate($perPage)
        ->appends($request->query());

    $allData = (clone $query)
        ->with(['picking', 'manualOrder'])
        ->orderBy('tgl_turun_pl')
        ->orderBy('created_at')
        ->get();

    // Assign rekap_number per tanggal
    if ($allData->isNotEmpty()) {
        foreach ($allData->groupBy(function ($item) {
            return Carbon::parse($item->tgl_turun_pl)->toDateString();
        }) as $tanggal => $rows) {

            $rekapNumber = $this->generateManualRekapNumber($tanggal);

            ManualRealisasi::whereIn('id', $rows->pluck('id'))
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

    $groupedData = $allData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    $mismatchMap = UnitNamaMismatch::where('is_resolved', false)
        ->get()
        ->keyBy(fn ($m) => trim((string) $m->no_cab))
        ->map(fn ($m) => [
            'nama_excel'  => $m->nama_excel,
            'nama_master' => $m->nama_master,
        ])
        ->all();

    return view('import::import.manual-printed', [
        'data'         => $data,
        'groupedData'  => $groupedData,
        'mismatchMap'  => $mismatchMap,
    ]);
}

// =========================================================
// HAPUS REALISASI MANUAL
// =========================================================
public function deleteManualRealisasi($id)
{
    $item = ManualRealisasi::findOrFail($id);
    $item->delete();

    return redirect()
        ->route('import.manual-printed')
        ->with('success', '✅ Data berhasil dihapus dari Manual Realisasi!');
}

// =========================================================
// PRINT REKAP PDF
// =========================================================
public function printManualRealisasiPdf(Request $request)
{
    // ===== TAMBAHKAN INI =====
    ini_set('memory_limit', '1024M');   // atau '2048M' jika data sangat banyak
    set_time_limit(300);
    // ========================

    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = ManualRealisasi::whereIn('id', $ids)
        ->with(['manualOrder', 'picking'])
        ->get();

    // Hanya yang picking sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang siap dicetak (Picking belum dibuat).');
    }

    // Tandai sudah print
    if ($request->boolean('mark_printed')) {
        ManualRealisasi::whereIn('id', $filteredData->pluck('id'))
            ->whereNull('printed_at')
            ->update([
                'printed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    // Ambil ulang data yang sudah difilter + sorting
    $filteredData = ManualRealisasi::whereIn('id', $filteredData->pluck('id'))
        ->with(['manualOrder', 'picking'])
        ->get()
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
    $docNumber = $this->generateManualRekapNumber($firstDate);

    $pdf = Pdf::loadView('import::import.manual-printed-pdf', [
        'data'      => $filteredData,
        'docNumber' => $docNumber,
    ])
    ->setPaper('A4', 'landscape')
    ->setOptions([
        'defaultFont'          => 'sans-serif',
        'isHtml5ParserEnabled' => true,
        'isRemoteEnabled'      => true,
        'isPhpEnabled'         => true,          // kadang membantu
        'dpi'                  => 96,            // kurangi sedikit beban
    ]);

    return $pdf->stream(
        'Manual-RA-' . now()->format('d-m-Y_H-i') . '.pdf'
    );
}

public function printManualPickingList($id)
{
    $main = ManualRealisasi::with([
        'picking',
        'picking.pickingItems',
        'manualOrder',
    ])->findOrFail($id);

    if (!$main->picking_printed_at) {
        $main->update(['picking_printed_at' => now()]);
    }

    if (!$main->picking) {
        return back()->with('error', 'Picking belum dibuat.');
    }

    $items = $main->picking
        ->pickingItems
        ->sortBy('item_sku')
        ->values();

    // =====================================================
    // HITUNG JUMLAH LEMBAR (qty ÷ 200)
    // =====================================================
    $maxQtyPerSheet = 200;

    // Coba beberapa kemungkinan nama field qty
    $totalQty = $items->sum(function ($item) {
        return (float) (
            $item->item_qty 
            ?? $item->qty 
            ?? $item->quantity 
            ?? 0
        );
    });

    $jumlahLembar = $totalQty <= 0 
        ? 1 
        : (int) ceil($totalQty / $maxQtyPerSheet);

    // Debug sementara (bisa dihapus nanti)
    // dd($totalQty, $jumlahLembar);

    return view('import::import.manual-picking-list', [
        'item'              => $main,
        'picking'           => $main->picking,
        'data'              => $items,
        'jumlah_lembar'     => $jumlahLembar,   // ← PASTIKAN INI ADA
        'total_qty'         => $totalQty,
        'max_qty_per_sheet' => $maxQtyPerSheet,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
    ]);
}

// =========================================================
// PRINT PICKING LIST PDF
// =========================================================
public function printManualPickingListPdf(Request $request, $id)
{
    $main = ManualRealisasi::with([
        'picking.pickingItems',
        'manualOrder',
    ])->findOrFail($id);

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
            $item->item_name = preg_replace('/\s+/', ' ', trim($item->item_name));
            return $item;
        });

    // =====================================================
    // HITUNG JUMLAH LEMBAR (qty ÷ 200)
    // =====================================================
    $maxQtyPerSheet = 200;

    $totalQty = $items->sum(function ($item) {
        return (float) ($item->item_qty ?? $item->qty ?? 0);
    });

    $jumlahLembar = $totalQty <= 0
        ? 1
        : (int) ceil($totalQty / $maxQtyPerSheet);

    // =====================================================
    // ORIENTASI (portrait / landscape)
    // =====================================================
    $orientation = strtolower($request->get('orientation', 'portrait'));
    if (!in_array($orientation, ['portrait', 'landscape'])) {
        $orientation = 'portrait';
    }

    $pdf = Pdf::loadView('import::import.manual-picking-list-pdf', [
        'item'              => $main,
        'picking'           => $picking,
        'data'              => $items,
        'no_pl'             => $main->no_pl,
        'tgl_order'         => $main->tgl_turun_pl,
        'billing_last_name' => $main->billing_last_name,
        'billing_company'   => $main->billing_company,
        'kategori_order'    => $main->kategori_order,
        'jumlah_lembar'     => $jumlahLembar,
        'total_qty'         => $totalQty,
        'orientation'       => $orientation,
    ]);

    $pdf->setPaper('A5', $orientation);

    $pdf->setOptions([
        'margin-top'           => 8,
        'margin-right'         => 6,
        'margin-bottom'        => 18,
        'margin-left'          => 6,
        'isHtml5ParserEnabled' => true,
        'isPhpEnabled'         => true,
    ]);

    $filename = 'Manual_Picking_' . ($main->no_pl ?? 'unknown')
        . '_' . ($main->kategori_order ?? '')
        . '_' . now()->format('Ymd_His') . '.pdf';

    return $pdf->stream($filename);
}

// =========================================================
// GENERATE REKAP NUMBER MANUAL
// =========================================================
private function generateManualRekapNumber($tanggal = null)
{
    if (!$tanggal) {
        $tanggal = Carbon::now('Asia/Jakarta')->toDateString();
    }

    $tanggal = Carbon::parse($tanggal)->toDateString();

    $existing = ManualRealisasi::whereDate('tgl_turun_pl', $tanggal)
        ->whereNotNull('rekap_number')
        ->value('rekap_number');

    if ($existing) {
        return $existing;
    }

    $lastNumber = ManualRealisasi::whereNotNull('rekap_number')
        ->max(DB::raw("CAST(REPLACE(rekap_number, '#M', '') AS UNSIGNED)"));

    // Format beda dari JKT: #M0001
    $next = ($lastNumber ?? 0) + 1;

    return '#M' . str_pad($next, 4, '0', STR_PAD_LEFT);
}
/**
 * Print QC Manual - Hanya data yang picking list sudah selesai
 */
public function printManualQC(Request $request)
{
    // ===== Tingkatkan memory & timeout =====
    ini_set('memory_limit', '1024M');
    set_time_limit(300);
    // ======================================

    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = ManualRealisasi::whereIn('id', $ids)
        ->with(['manualOrder', 'picking'])
        ->get();

    // Hanya yang Picking List sudah dicetak
    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk QC.');
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

    // Optional: batasi jika terlalu banyak
    if ($filteredData->count() > 300) {
        $filteredData = $filteredData->take(300);
    }

    $docNumber = $this->generateManualRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    try {
        $pdf = Pdf::loadView('import::import.manual-print-qc', [
            'data'      => $filteredData,
            'docNumber' => $docNumber,
        ])
        ->setPaper('A4', 'landscape')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'isPhpEnabled'         => true,   // ← wajib untuk nomor halaman
            'dpi'                  => 96,
        ]);

        return $pdf->stream('Manual-QC-Report-' . now()->format('d-m-Y_H-i') . '.pdf');
    } catch (\Throwable $e) {
        Log::error('printManualQC error: ' . $e->getMessage());
        return back()->with('error', 'Gagal generate PDF QC: ' . $e->getMessage());
    }
}

/**
 * Print Pemesanan (RA Picking) Manual
 */
public function printManualPemesanan(Request $request)
{
    // ===== PENTING: Tingkatkan memory =====
    ini_set('memory_limit', '1024M');
    set_time_limit(300);
    // ======================================

    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = ManualRealisasi::whereIn('id', $ids)
        ->with(['manualOrder', 'picking'])
        ->get();

    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
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

    // Batasi jika terlalu banyak (opsional, untuk cegah crash)
    if ($filteredData->count() > 300) {
        $filteredData = $filteredData->take(300);
    }

    $groupedData = $filteredData->groupBy(function ($item) {
        return Carbon::parse($item->tgl_turun_pl)->toDateString();
    });

    $docNumber = $this->generateManualRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    try {
        $pdf = Pdf::loadView('import::import.manual-print-pemesanan', [
            'data'        => $filteredData,
            'groupedData' => $groupedData,
            'docNumber'   => $docNumber,
        ])
        ->setPaper('A4', 'landscape')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'isPhpEnabled'         => true,
            'dpi'                  => 96,
        ]);

        return $pdf->stream('Manual-RA-Pemesanan-Picking-' . now()->format('d-m-Y_H-i') . '.pdf');
    } catch (\Throwable $e) {
        Log::error('printManualPemesanan error: ' . $e->getMessage());
        return back()->with('error', 'Gagal generate PDF: ' . $e->getMessage());
    }
}

/**
 * Print Packing Manual - Hanya data yang picking list sudah selesai
 */
public function printManualPacking(Request $request)
{
    // ===== Tingkatkan memory & timeout =====
    ini_set('memory_limit', '1024M');
    set_time_limit(300);
    // ======================================

    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = ManualRealisasi::whereIn('id', $ids)
        ->with(['manualOrder', 'picking'])
        ->get();

    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk Packing.');
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

    // Optional: batasi jika terlalu banyak
    if ($filteredData->count() > 300) {
        $filteredData = $filteredData->take(300);
    }

    $docNumber = $this->generateManualRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    try {
        $pdf = Pdf::loadView('import::import.manual-print-packing', [
            'data'      => $filteredData,
            'docNumber' => $docNumber,
        ])
        ->setPaper('A4', 'landscape')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'isPhpEnabled'         => true,   // ← wajib untuk nomor halaman
            'dpi'                  => 96,
        ]);

        return $pdf->stream('Manual-Packing-Report-' . now()->format('d-m-Y_H-i') . '.pdf');
    } catch (\Throwable $e) {
        Log::error('printManualPacking error: ' . $e->getMessage());
        return back()->with('error', 'Gagal generate PDF Packing: ' . $e->getMessage());
    }
}

/**
 * Print Distribusi / Ekspedisi Manual - Hanya data yang picking list sudah selesai
 */
public function printManualEkspedisi(Request $request)
{
    // ===== Tingkatkan memory & timeout =====
    ini_set('memory_limit', '1024M');
    set_time_limit(300);
    // ======================================

    $ids = array_filter(explode(',', $request->get('ids', '')));

    if (empty($ids)) {
        return back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $data = ManualRealisasi::whereIn('id', $ids)
        ->with(['manualOrder', 'picking'])
        ->get();

    $filteredData = $data->filter(function ($item) {
        return !is_null($item->picking_printed_at);
    });

    if ($filteredData->isEmpty()) {
        return back()->with('error', 'Belum ada data yang Picking List-nya selesai dicetak untuk Distribusi.');
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

    // Optional: batasi jika terlalu banyak
    if ($filteredData->count() > 300) {
        $filteredData = $filteredData->take(300);
    }

    $docNumber = $this->generateManualRekapNumber(
        optional($filteredData->first())->tgl_turun_pl ?? now()
    );

    try {
        $pdf = Pdf::loadView('import::import.manual-print-ekspedisi', [
            'data'      => $filteredData,
            'docNumber' => $docNumber,
        ])
        ->setPaper('A4', 'landscape')
        ->setOptions([
            'defaultFont'          => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
            'isPhpEnabled'         => true,   // ← wajib untuk nomor halaman
            'dpi'                  => 96,
        ]);

        return $pdf->stream('Manual-Ekspedisi-Report-' . now()->format('d-m-Y_H-i') . '.pdf');
    } catch (\Throwable $e) {
        Log::error('printManualEkspedisi error: ' . $e->getMessage());
        return back()->with('error', 'Gagal generate PDF Ekspedisi: ' . $e->getMessage());
    }
}

/**
 * Update catatan Manual Realisasi (dari halaman index)
 */
public function updateManualCatatan(Request $request, $id)
{
    $request->validate([
        'catatan' => 'nullable|string|max:2000',
    ]);

    $realisasi = ManualRealisasi::with('manualOrder')->findOrFail($id);

    $catatan = trim($request->catatan ?? '');

    DB::transaction(function () use ($realisasi, $catatan) {
        // Update ket di ManualRealisasi
        $realisasi->update([
            'ket' => $catatan ?: null,
        ]);

        // Update juga di ManualOrder (sumber utama)
        if ($realisasi->manualOrder) {
            $realisasi->manualOrder->update([
                'catatan' => $catatan ?: null,
            ]);
        }
    });

    return response()->json([
        'success' => true,
        'message' => 'Catatan berhasil diperbarui',
        'catatan' => $catatan,
    ]);
}
}
