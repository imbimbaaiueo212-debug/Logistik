<?php

namespace App\Http\Controllers;

use App\Imports\BimbashopImport;
use App\Models\ManualOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ManualOrderImport;        // ← Tambahkan ini
use App\Models\PesananMajalah;
use App\Models\PesananMajalahPuw1;
use App\Models\UnitNamaMismatch;
use App\Models\ManualRealisasi;
use App\Models\ManualPicking;
use App\Models\ManualPickingItem;
use App\Models\DlcPeriode;
use App\Models\DlcPesanan;
use App\Models\PasifPeriode;
use App\Models\BacaanPeriode;
use App\Models\BacaanPesanan;
use App\Models\SparePasif;
use App\Imports\BacaanImport;
use App\Models\PasifPesanan;
use App\Imports\PasifImport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\UnitKemitraan;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\BimbashopOrder;
use App\Models\CasdanaTransaction;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
    /**
     * Halaman utama Data biMBA Shop
     */
    public function index()
    {
        return view('import.index');   // Halaman dengan kartu pilihan
    }

   public function bimbashop(Request $request)
{
    $query = BimbashopOrder::query();

    // === Filter yang sudah ada ===
    if ($request->filled('start_date')) {
        $query->whereDate('order_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('order_date', '<=', $request->end_date);
    }
    if ($request->filled('order_id')) {
        $query->where('order_id', 'like', '%' . $request->order_id . '%');
    }
    if ($request->filled('item_sku')) {
        $query->where('item_sku', 'like', '%' . $request->item_sku . '%');
    }
    if ($request->filled('item_name')) {
        $query->where('item_name', 'like', '%' . $request->item_name . '%');
    }
    if ($request->filled('billing_name')) {
        $query->where(function($q) use ($request) {
            $q->where('billing_first_name', 'like', '%' . $request->billing_name . '%')
              ->orWhere('billing_last_name', 'like', '%' . $request->billing_name . '%');
        });
    }
    if ($request->filled('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // === Per Page ===
    $perPage = $request->get('per_page', 5);           // default 5
    $perPage = in_array($perPage, [5, 10, 25, 50, 100, 200, 500]) ? $perPage : 5
    ; // security

    $bimbashopOrders = $query
                        ->latest()
                        ->paginate($perPage)
                        ->appends($request->query());

    return view('import.bimbashop', compact('bimbashopOrders'));
}
    /**
     * Proses Import Data
     */
    public function bimbashopStore(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('import_file');
            $originalName = $file->getClientOriginalName();

            // Backup file
            $filename = time() . '_' . $originalName;
            $file->storeAs('imports/bimbashop', $filename, 'public');

            // Import dengan logging
            Log::info("Mulai import file: " . $originalName);

            Excel::import(new BimbashopImport, $file);

            Log::info("Import berhasil: " . $originalName);

            // === SYNC MANUAL ===
            try {
                $result = $this->syncManualFromBimbashopCasdana();
                Log::info("Sync Manual dari Bimbashop/Casdana: " . json_encode($result));
            } catch (\Throwable $e) {
                Log::error("Sync Manual gagal: " . $e->getMessage());
            }

            return redirect()->route('import.bimbashop')
                             ->with('success', '✅ Data biMBA Shop berhasil diimport! File: ' . $originalName);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Error validasi Excel (heading, format, dll)
            $failures = $e->failures();
            $errorMsg = 'Validasi gagal: ';
            foreach ($failures as $failure) {
                $errorMsg .= "Baris {$failure->row()} kolom {$failure->attribute()} → {$failure->errors()[0]} | ";
            }

            Log::error("Import Validation Error: " . $errorMsg);
            
            return redirect()->route('import.index')
                             ->with('error', '❌ ' . $errorMsg);

        } catch (\Exception $e) {
            Log::error("Import Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            return redirect()->route('import.index')
                             ->with('error', '❌ Gagal mengimport data: ' . $e->getMessage());
        }
    }

    public function bimbashopEdit($id)
{
    $order = BimbashopOrder::findOrFail($id);
    return view('import.bimbashop-edit', compact('order'));
}

public function bimbashopUpdate(Request $request, $id)
{
    $order = BimbashopOrder::findOrFail($id);

    $request->validate([
        'order_id'       => 'required|string|max:100',
        'order_date'     => 'required|date',
        'item_sku'       => 'required|string|max:100',
        'item_name'      => 'required|string|max:255',
        'item_price'     => 'nullable|numeric|min:0',
        'item_qty'       => 'nullable|integer|min:0',
        'status'         => 'required|in:completed,processing,on-hold,pending',
        'payment_method' => 'nullable|string',
        'order_total'    => 'nullable|numeric',
        // tambahkan field lain yang mau diedit
    ]);

    $order->update($request->except(['_token', '_method']));

    return redirect()
        ->route('import.bimbashop')
        ->with('success', '✅ Data Order #' . $order->order_id . ' berhasil diperbarui!');
}

public function bimbashopDestroy($id)
{
    $order = BimbashopOrder::findOrFail($id);
    $order->delete();

    return redirect()
        ->route('import.bimbashop')
        ->with('success', '✅ Data Order #' . $order->order_id . ' berhasil dihapus!');
}

/**
 * Halaman List Casdana
 */
public function casdana(Request $request)
{
    $query = CasdanaTransaction::query();

    // Filter
    if ($request->filled('invoice_number')) {
        $query->where('invoice_number', 'like', '%' . $request->invoice_number . '%');
    }
    if ($request->filled('customer')) {
        $query->where('customer', 'like', '%' . $request->customer . '%');
    }
    if ($request->filled('merchant')) {
        $query->where('merchant', 'like', '%' . $request->merchant . '%');
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('start_date')) {
        $query->whereDate('payment_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('payment_date', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 25);
    $perPage = in_array($perPage, [25, 50, 100, 200, 500, 1000, 20000, 30000, 40000, 50000]) ? $perPage : 25;

    $casdanaTransactions = $query
                            ->latest()
                            ->paginate($perPage)
                            ->appends($request->query());

    return view('import.casdana', compact('casdanaTransactions'));
}

/**
 * Halaman Import Casdana
 */
/**
 * Halaman Import Casdana
 */
public function casdanaStore(Request $request)
{
    $request->validate([
        'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
    ]);

    try {
        $file = $request->file('import_file');
        $originalName = $file->getClientOriginalName();

        // Backup file
        $filename = time() . '_' . $originalName;
        $file->storeAs('imports/casdana', $filename, 'public');

        Log::info("Mulai import Casdana: " . $originalName);

        // Proses Import
        Excel::import(new \App\Imports\CasdanaImport, $file);

        Log::info("Import Casdana berhasil: " . $originalName);
        // === SYNC MANUAL ===

        try {
            $result = $this->syncManualFromBimbashopCasdana();
            Log::info("Sync Manual dari Bimbashop/Casdana: " . json_encode($result));
        } catch (\Throwable $e) {
            Log::error("Sync Manual gagal: " . $e->getMessage());
        }

        return redirect()->route('import.casdana')
                         ->with('success', '✅ Data Casdana berhasil diimport! File: ' . $originalName);

    } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
        $failures = $e->failures();
        $errorMsg = 'Validasi gagal: ';
        foreach ($failures as $failure) {
            $errorMsg .= "Baris {$failure->row()} → " . implode(', ', $failure->errors()) . " | ";
        }

        Log::error("Casdana Import Validation Error: " . $errorMsg);
        
        return redirect()->route('import.casdana')
                         ->with('error', '❌ ' . $errorMsg);

    } catch (\Exception $e) {
        Log::error("Casdana Import Error: " . $e->getMessage());
        
        return redirect()->route('import.casdana')
                         ->with('error', '❌ Gagal mengimport data: ' . $e->getMessage());
    }
}

    public function casdanaedit($id)
    {
        $transaction = CasdanaTransaction::findOrFail($id);
        return view('import.casdana-edit', compact('transaction'));
    }

    public function manual(Request $request)
{
    $query = ManualOrder::query();

    if ($request->filled('order_id')) {
        $query->where('order_id', 'like', '%' . $request->order_id . '%');
    }
    if ($request->filled('customer_name')) {
        $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
    }
    if ($request->filled('product_name')) {
        $query->where('product_name', 'like', '%' . $request->product_name . '%');
    }
    if ($request->filled('product_sku')) {
        $query->where('product_sku', 'like', '%' . $request->product_sku . '%');
    }
    if ($request->filled('payment_method')) {
        $query->where('payment_method', $request->payment_method);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    // ===== FILTER GRUP (BARU) =====
    if ($request->filled('grup')) {
        $query->where('grup', $request->grup);
    }

    if ($request->filled('start_date')) {
        $query->whereDate('order_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('order_date', '<=', $request->end_date);
    }

    $perPage = $request->get('per_page', 25);
    $perPage = in_array((int) $perPage, [5, 10, 25, 50, 100, 200, 500]) ? (int) $perPage : 25;

    $manualOrders = $query
        ->orderBy('order_date', 'asc')
        ->paginate($perPage)
        ->appends($request->query());

    // Ambil list grup untuk dropdown
    $grups = ManualOrder::select('grup')
        ->whereNotNull('grup')
        ->where('grup', '!=', '')
        ->distinct()
        ->orderBy('grup')
        ->pluck('grup');

    $mismatchMap = UnitNamaMismatch::where('is_resolved', false)
        ->get()
        ->keyBy(fn ($m) => trim((string) $m->no_cab))
        ->map(fn ($m) => [
            'nama_excel'  => $m->nama_excel,
            'nama_master' => $m->nama_master,
        ])
        ->all();

    return view('import.manual.index', compact('manualOrders', 'mismatchMap', 'grups'));
}

// =========================================================
// FILTERED IDS (untuk bulk – hanya yang belum diproses)
// =========================================================
public function getManualFilteredIds(Request $request)
{
    $query = ManualOrder::query()->where('is_processed', 0);

    if ($request->filled('start_date')) {
        $query->whereDate('order_date', '>=', $request->start_date);
    }
    if ($request->filled('end_date')) {
        $query->whereDate('order_date', '<=', $request->end_date);
    }
    if ($request->filled('order_id')) {
        $query->where('order_id', 'like', '%' . $request->order_id . '%');
    }
    if ($request->filled('customer_name')) {
        $query->where('customer_name', 'like', '%' . $request->customer_name . '%');
    }
    if ($request->filled('product_name')) {
        $query->where('product_name', 'like', '%' . $request->product_name . '%');
    }
    if ($request->filled('product_sku')) {
        $query->where('product_sku', 'like', '%' . $request->product_sku . '%');
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }

    $ids = $query->pluck('id');

    return response()->json([
        'ids'   => $ids->values(),
        'count' => $ids->count(),
    ]);
}

public function getManualModalData(Request $request)
{
    $ids = $request->input('ids', []);

    if (empty($ids)) {
        return response()->json([]);
    }

    // Ambil map mismatch (sama seperti di method manual)
    $mismatchMap = UnitNamaMismatch::where('is_resolved', false)
        ->get()
        ->keyBy(fn ($m) => trim((string) $m->no_cab))
        ->map(fn ($m) => [
            'nama_excel'  => $m->nama_excel,
            'nama_master' => $m->nama_master,
        ])
        ->all();

    $data = ManualOrder::whereIn('id', $ids)
        ->get()
        ->map(function ($item) use ($mismatchMap) {
            $isManualMajalah = true;

            $jasaKurir = $item->ekspedisi;
            $service   = $item->service_pengiriman;

            if ($isManualMajalah) {
                $jasaKurir = $jasaKurir ?: 'Lion Parcel';
                $service   = $service   ?: 'REGPACK';
            }

            $statusKirim = $item->status_kirim
                ?: (($item->ship_total ?? 0) > 0 ? 'Dikirim' : 'Diambil');

            // ===== Mismatch =====
            $noCab     = trim($item->billing_last_name ?? '');
            $mismatch  = $mismatchMap[$noCab] ?? null;
            $isMismatch = $mismatch
                || str_contains($item->catatan ?? '', 'NAMA_MISMATCH')
                || str_contains($item->notes ?? '', 'NAMA_MISMATCH');

            return [
                'id'                => $item->id,
                'invoice'           => $item->order_id ?? '-',
                'to_customer'       => $item->customer_name ?? '-',
                'pesanan'           => $item->product_name ?? $item->product_sku ?? '-',
                'payment_date'      => $item->payment_date
                    ? Carbon::parse($item->payment_date)->format('d/m/Y H:i')
                    : null,
                'payment_channel'   => $item->payment_method ?? 'manual',
                'status_pembayaran' => 'MANUAL',
                'status_kirim'      => $statusKirim,
                'vendor'            => 'Manual / Majalah',
                'jasa_kurir'        => $jasaKurir,
                'service_kurir'     => $service,
                'grup'              => $item->grup ?? null,
                'is_processed'      => (bool) $item->is_processed,
                'processed_at'      => $item->processed_at
                    ? Carbon::parse($item->processed_at)->format('d/m/Y H:i')
                    : null,

                // ===== Data mismatch untuk modal =====
                'is_mismatch'       => (bool) $isMismatch,
                'nama_excel'        => $mismatch['nama_excel'] ?? null,
                'nama_master'       => $mismatch['nama_master'] ?? null,
            ];
        });

    return response()->json($data);
}

// =========================================================
// BULK ACTION (sama konsep jakarta-aktif, tanpa RealisasiAktif)
// =========================================================
public function bulkActionManual(Request $request)
{
    $action  = $request->input('action');
    $perItem = $request->input('per_item');

    if ($action !== 'processed' || empty($perItem)) {
        return redirect()->back()->with('error', 'Data tidak valid.');
    }

    $updates = json_decode($perItem, true);

    if (empty($updates)) {
        return redirect()->back()->with('error', 'Tidak ada data yang dipilih.');
    }

    $now = Carbon::now('Asia/Jakarta');
    $successCount = 0;

    foreach ($updates as $update) {
        $id = $update['id'] ?? null;
        if (!$id) {
            continue;
        }

        $order = ManualOrder::find($id);
        if (!$order || $order->is_processed) {
            continue;
        }

        $statusKirim  = $update['status_kirim'] ?? $order->status_kirim ?? 'Dikirim';
        $jasaKurir    = $update['jasa_kurir'] ?? $order->ekspedisi;
        $serviceKurir = $update['service_kurir'] ?? $order->service_pengiriman;
        $catatanBaru  = $update['catatan'] ?? null;

        // =====================================================
        // PAYMENT DATE ASLI (hanya dari Bimbashop/Casdana)
        // null = Pending
        // =====================================================
        $realPaymentDate = $order->payment_date
            ? Carbon::parse($order->payment_date)
            : null;

        // =====================================================
        // ESTIMASI (boleh pakai order_date sebagai acuan hitung)
        // =====================================================
        $baseDate = $realPaymentDate
            ?? ($order->order_date ? Carbon::parse($order->order_date) : null);

        $estimasiPrintPl   = $order->estimasi_print_pl
            ? Carbon::parse($order->estimasi_print_pl)
            : null;

        $estimasiPersiapan = $order->estimasi_persiapan
            ? Carbon::parse($order->estimasi_persiapan)
            : null;

        if ($baseDate && !$estimasiPrintPl) {
            $estimasiPrintPl = $baseDate->hour < 12
                ? $baseDate->copy()
                : $baseDate->copy()->addDay();

            while (
                $estimasiPrintPl->isSunday()
                || $this->isHolidayManual($estimasiPrintPl)
            ) {
                $estimasiPrintPl->addDay();
            }

            $estimasiPersiapan = $this->addBusinessDaysManual($estimasiPrintPl, 2);
        }

        $catatan = $order->catatan ?? '';
        if ($catatanBaru) {
            $catatan .= "\n\nDi proses bulk pada "
                . $now->format('d/m/Y H:i')
                . ': '
                . trim($catatanBaru);
        }

        // =====================================================
        // DETEKSI KATEGORI (Modul / Majalah / Sertifikat)
        // =====================================================
        $namaBarang = trim($order->product_name ?? $order->product_sku ?? '');
        $namaLower  = strtolower($namaBarang);
        $skuUpper   = strtoupper(trim($order->product_sku ?? ''));

        if (
            str_contains($namaLower, 'majalah')
            || preg_match('/\bM\d{2,4}\b/i', $namaBarang)
            || preg_match('/\bM\d{2,4}\b/', $skuUpper)
        ) {
            $kategoriOrder = 'Majalah';
            $namaStokis    = 'Manual / Majalah';
        } elseif (
            str_contains($namaLower, 'sertifikat')
            || str_contains($skuUpper, 'STA')
            || str_contains($skuUpper, 'STPB')
        ) {
            $kategoriOrder = 'Sertifikat';
            $namaStokis    = 'Manual / Sertifikat';
        } else {
            $kategoriOrder = 'Modul';
            $namaStokis    = 'Manual / Modul';
        }

        try {
            DB::transaction(function () use (
                $order,
                $now,
                $statusKirim,
                $jasaKurir,
                $serviceKurir,
                $estimasiPrintPl,
                $estimasiPersiapan,
                $realPaymentDate,
                $catatan,
                $namaBarang,
                $kategoriOrder,
                $namaStokis,
                &$successCount
            ) {
                // =================================================
                // 1. UPDATE MANUAL ORDER
                //    payment_date TIDAK diubah jika masih null
                // =================================================
                $order->update([
                    'is_processed'       => 1,
                    'processed_at'       => $now,
                    'status_kirim'       => $statusKirim,
                    'ekspedisi'          => $jasaKurir,
                    'service_pengiriman' => $serviceKurir,
                    'estimasi_print_pl'  => $estimasiPrintPl,
                    'estimasi_persiapan' => $estimasiPersiapan,
                    // payment_date tetap seperti aslinya (null = Pending)
                    'status'             => 'pending',
                    'catatan'            => $catatan,
                ]);

                // =================================================
                // 2. CEK SUDAH ADA REALISASI
                // =================================================
                if (ManualRealisasi::where('manual_order_id', $order->id)->exists()) {
                    $successCount++;
                    return;
                }

                $estimasiHari = null;
                if ($realPaymentDate && $estimasiPersiapan) {
                    $estimasiHari = $realPaymentDate
                        ->diffInDays(Carbon::parse($estimasiPersiapan));
                }

                // =================================================
                // 3. CREATE MANUAL REALISASI
                // =================================================
                $realisasi = ManualRealisasi::create([
                    'manual_order_id'    => $order->id,
                    'no_pl'              => $order->order_id,
                    'no_ps'              => $order->no_ps,
                    'tgl_turun_pl'       => $order->order_date,
                    'nama_unit'          => $order->customer_name,
                    'billing_last_name'  => $order->billing_last_name,
                    'billing_company'    => $order->billing_first_name,
                    'pengiriman'         => $jasaKurir
                        ?: ($statusKirim === 'Diambil' ? 'Diambil' : '-'),
                    'service_pengiriman' => $serviceKurir,
                    'nama_barang'        => $namaBarang ?: '-',
                    'kategori_order'     => $kategoriOrder,
                    'tgl_bayar'          => $realPaymentDate,   // null = Pending
                    'jumlah_bayar'       => $order->total ?? 0,
                    'order_weight'       => $order->order_weight ?? 0,
                    'nama_stokis'        => $namaStokis,
                    'tgl_estimasi'       => $estimasiPersiapan,
                    'estimasi_hari'      => $estimasiHari,
                    'penyebut'           => $order->customer_name,
                    'pengambil'          => $statusKirim === 'Diambil' ? 'Ambil Sendiri' : null,
                    'ket'                => $catatan,
                    'is_processed'       => true,
                    'grup'               => $order->grup,
                ]);

                // =================================================
                // 4. CREATE MANUAL PICKING
                // =================================================
                $picking = ManualPicking::create([
                    'manual_realisasi_id'      => $realisasi->id,
                    'manual_order_id'          => $order->id,
                    'no_pl'                    => $order->order_id,
                    'no_ps'                    => $order->no_ps,   // ← TAMBAHKAN
                    'kategori_order'           => $kategoriOrder,
                    'tgl_order'                => $order->order_date
                        ? Carbon::parse($order->order_date)->toDateString()
                        : now()->toDateString(),
                    'tgl_picking'              => now()->toDateString(),
                    'payment_date'             => $realPaymentDate,   // null jika belum bayar
                    'waktu_estimasi_persiapan' => $estimasiPersiapan
                        ? Carbon::parse($estimasiPersiapan)->toDateString()
                        : now()->toDateString(),
                    'jam_picking'              => now()->format('H:i:s'),
                    'id_pesan'                 => $order->order_id,
                    'vendor'                   => $namaStokis,
                    'nama_unit'                => $order->customer_name,
                    'billing_last_name'        => $order->billing_last_name,
                    'billing_company'          => $order->billing_first_name,
                    'kirim'                    => $order->shipping_address_1,
                    'no_telpon'                => $order->phone,
                    'alamat_kirim'             => $order->shipping_address_1,
                    'kab_kota_provinsi'        => $order->shipping_city,
                    'ekspedisi'                => $jasaKurir,
                    'service_pengiriman'       => $serviceKurir,
                    'pesanan'                  => $namaBarang,
                    'total'                    => $order->total ?? 0,
                    'berat'                    => $order->order_weight ?? 0,
                    'total_item'               => 1,
                    'total_qty'                => (int) ($order->qty ?? 1),
                    'status'                   => 'completed',
                    'printed_at'               => now(),
                    'created_by'               => Auth::id(),
                    'catatan'                  => 'Auto Generate dari Manual Proses',
                    'grup'                     => $order->grup,
                ]);

                // =================================================
                // 5. CREATE PICKING ITEM
                // =================================================
                ManualPickingItem::create([
                    'manual_picking_id' => $picking->id,
                    'product_id'        => null,
                    'item_name'         => $order->product_name ?? '-',
                    'item_sku'          => $order->product_sku ?? '-',
                    'item_qty'          => (int) ($order->qty ?? 1),
                    'qty_picked'        => 0,
                    'cek'               => false,
                ]);

                $successCount++;
            });
        } catch (\Throwable $e) {
            Log::error('bulkActionManual gagal untuk order #' . $id . ': ' . $e->getMessage());
        }
    }

    return redirect()
        ->route('import.manual')
        ->with('success', "{$successCount} data berhasil diproses!");
}

// =========================================================
// HELPER (sama seperti OrderController)
// =========================================================
private function addBusinessDaysManual(Carbon $startDate, int $days = 2): Carbon
{
    $date  = $startDate->copy();
    $added = 0;

    while ($added < $days) {
        $date->addDay();
        if ($date->isSunday() || $this->isHolidayManual($date)) {
            continue;
        }
        $added++;
    }

    return $date;
}

private function isHolidayManual($date): bool
{
    $holidays = [
        '2026-01-01', '2026-01-16', '2026-02-17', '2026-03-19',
        '2026-03-21', '2026-03-22', '2026-04-03', '2026-04-05',
        '2026-05-01', '2026-05-14', '2026-05-27', '2026-06-01',
        '2026-06-16', '2026-08-17', '2026-12-25',
    ];

    return in_array($date->format('Y-m-d'), $holidays);
}

    /**
     * Import Excel Manual Pemesanan
     */
    public function manualImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('import_file');
            $originalName = $file->getClientOriginalName();

            $filename = time() . '_' . $originalName;
            $file->storeAs('imports/manual', $filename, 'public');

            Log::info("Mulai import Manual Order: " . $originalName);

            Excel::import(new ManualOrderImport, $file);

            Log::info("Import Manual Order berhasil: " . $originalName);

            return redirect()->route('import.manual')
                             ->with('success', '✅ Data Manual Pemesanan berhasil diimport! File: ' . $originalName);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMsg = 'Validasi gagal: ';
            foreach ($failures as $failure) {
                $errorMsg .= "Baris {$failure->row()} → " . implode(', ', $failure->errors()) . " | ";
            }
            Log::error("Manual Import Validation Error: " . $errorMsg);
            
            return redirect()->route('import.manual')
                             ->with('error', '❌ ' . $errorMsg);

        } catch (\Exception $e) {
            Log::error("Manual Import Error: " . $e->getMessage());
            return redirect()->route('import.manual')
                             ->with('error', '❌ Gagal mengimport data: ' . $e->getMessage());
        }
    }

    // CRUD Manual
    public function manualCreate()
    {
        return view('import.manual.create');
    }

    public function manualStore(Request $request)   // Single Create
    {
        $request->validate([
            'order_date'    => 'required|date',
            'customer_name' => 'required|string|max:255',
            'product_name'  => 'required|string|max:255',
            'qty'           => 'required|integer|min:1',
            'price'         => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        $data['total'] = $request->qty * $request->price;

        ManualOrder::create($data);

        return redirect()->route('import.manual')
                         ->with('success', '✅ Data manual berhasil ditambahkan');
    }

    public function manualEdit($id)
    {
        $order = ManualOrder::findOrFail($id);
        return view('import.manual.edit', compact('order'));
    }

    public function manualUpdate(Request $request, $id)
    {
        $order = ManualOrder::findOrFail($id);

        $data = $request->all();
        if (isset($data['qty']) && isset($data['price'])) {
            $data['total'] = $data['qty'] * $data['price'];
        }

        $order->update($data);

        return redirect()->route('import.manual')
                         ->with('success', '✅ Data berhasil diubah');
    }

    public function manualDestroy($id)
    {
        ManualOrder::findOrFail($id)->delete();
        return redirect()->route('import.manual')
                         ->with('success', '✅ Data berhasil dihapus');
    }

 public function syncPesananMajalahToJakartaAktif()
{
    $created     = 0;
    $updated     = 0;
    $skipped     = 0;
    $errors      = [];
    $skippedList = [];

    $periodes = PesananMajalah::with([
        'kabupaten.units',
        'kotamadya.units',
    ])->get();

    $periodesPuw1 = PesananMajalahPuw1::with('units')->get();

    $periodesDlc = DlcPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    $periodesPasif = PasifPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    $periodesPasifManual = \App\Models\PasifManualPeriode::with('transaksis')
        ->where('status', 'aktif')
        ->get();

    $periodesBacaan = BacaanPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    DB::beginTransaction();

    try {
        // =====================================================
        // A + B. KABUPATEN & KOTAMADYA → GROUP B
        // =====================================================
        foreach ($periodes as $periode) {
            $rawName = ($periode->judul ?? '') . ' ' . ($periode->bulan ?? '');
            $edisi   = $this->extractEdisiMajalah($rawName);

            foreach ($periode->kabupaten ?? [] as $kabupaten) {
                foreach ($kabupaten->units ?? [] as $unit) {
                    $wilayah = !empty($kabupaten->nama_kabupaten)
                        ? 'KABUPATEN ' . strtoupper(trim($kabupaten->nama_kabupaten))
                        : null;

                    $result = $this->createManualOrderFromUnit(
                        $unit,
                        $edisi,
                        $wilayah,
                        $kabupaten->contact_person ?? null,
                        'B',
                        $periode->no_ps ?? null
                    );
                    $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
                }
            }

            foreach ($periode->kotamadya ?? [] as $kotamadya) {
                foreach ($kotamadya->units ?? [] as $unit) {
                    $wilayah = !empty($kotamadya->nama_kotamadya)
                        ? 'KOTAMADYA ' . strtoupper(trim($kotamadya->nama_kotamadya))
                        : null;

                    $result = $this->createManualOrderFromUnit(
                        $unit,
                        $edisi,
                        $wilayah,
                        $kotamadya->contact_person ?? null,
                        'B',
                        $periode->no_ps ?? null
                    );
                    $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
                }
            }
        }

        // =====================================================
        // C. PUW1 (Jabodetabek) → GROUP B
        // =====================================================
        foreach ($periodesPuw1 as $periode) {
            $rawName = ($periode->judul ?? '') . ' ' . ($periode->bulan ?? '');
            $edisi   = $this->extractEdisiMajalah($rawName);

            foreach ($periode->units ?? [] as $unit) {
                $wilayah = !empty($unit->kabupaten_kota)
                    ? 'JABODETABEK ' . strtoupper(trim($unit->kabupaten_kota))
                    : 'JABODETABEK';

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    $wilayah,
                    $periode->contact_person ?? null,
                    'B',
                    $periode->no_ps ?? null
                );
                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // D. DLC → GROUP A (tipe majalah)
        // =====================================================
        foreach ($periodesDlc as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                if (($item->qty ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = ($item->nama_unit ?? '-') . ' (DLC)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab ?? null,
                    'jumlah_pesanan'    => $item->qty,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon ?? null,
                    'mitra_pengelolaan' => null,
                    'kabupaten_kota'    => 'DLC',
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    'DLC',
                    null,
                    'A',
                    $periode->no_ps ?? null,
                    'majalah'
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // E. PASIF BIASA → GROUP F
        // =====================================================
        foreach ($periodesPasif as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                $qtyItem = (int) ($item->qty ?? $item->jumlah ?? 0);

                if ($qtyItem <= 0) {
                    $skipped++;
                    $skippedList[] = ($item->nama_unit ?? '-') . ' (Pasif)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab ?? null,
                    'jumlah_pesanan'    => $qtyItem,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon ?? null,
                    'mitra_pengelolaan' => null,
                    'kabupaten_kota'    => 'PASIF',
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    'PASIF',
                    null,
                    'F',
                    $periode->no_ps ?? null,
                    'majalah'
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
// E2. PASIF MANUAL → GROUP F
// order_id = id_pesan yang sudah ada (PP863900)
// =====================================================
foreach ($periodesPasifManual as $periode) {
    $edisi = strtoupper(trim($periode->edisi));

    foreach ($periode->transaksis as $item) {
        if (($item->jumlah ?? 0) <= 0) {
            $skipped++;
            $skippedList[] = ($item->nama_unit ?? '-') . ' (Pasif Manual)';
            continue;
        }

        // Kolom di DB: id_pesan = PP863900
        $idPesananExisting = trim((string) ($item->id_pesan ?? ''));

        $unit = (object) [
            'id'                => $item->id,
            'id_pesan'          => $item->id,           // primary key untuk notes ID_PESAN:...
            'custom_order_id'   => $idPesananExisting !== '' ? $idPesananExisting : null,  // PP863900
            'nama_unit'         => $item->nama_unit,
            'no_cabang'         => $item->no_cab ?? null,
            'jumlah_pesanan'    => $item->jumlah,
            'alamat_unit'       => $item->alamat ?? $item->nama_unit,
            'telepon'           => $item->telepon ?? null,
            'mitra_pengelolaan' => null,
            'kabupaten_kota'    => 'PASIF MANUAL',
        ];

        $result = $this->createManualOrderFromUnit(
            $unit,
            $edisi,
            'PASIF MANUAL',
            null,
            'F',
            $periode->no_ps ?? null,
            'majalah'
        );

        $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
    }
}

        // =====================================================
        // F. BACAAN UNIT → GROUP A (tipe bacaan, qty = 1)
        // =====================================================
        foreach ($periodesBacaan as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                if ((int) ($item->bacaan_unit ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = ($item->nama_unit ?? '-') . ' (Bacaan)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab ?? null,
                    'jumlah_pesanan'    => 1,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon ?? null,
                    'mitra_pengelolaan' => null,
                    'kabupaten_kota'    => 'BACAAN',
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    'BACAAN',
                    null,
                    'A',
                    $periode->no_ps ?? null,
                    'bacaan'
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        // =====================================================
        // G. SPARE PASIF 3% → GROUP A
        // =====================================================
        $this->syncSparePasifData();

        $spareItems = SparePasif::where('status', 'aktif')
            ->where('spare', '>', 0)
            ->get();

        foreach ($spareItems as $item) {
            $edisi = strtoupper(trim($item->edisi));
            $qty   = (int) $item->spare;

            if ($qty <= 0) {
                $skipped++;
                continue;
            }

            $unit = (object) [
                'id'                => 'spare-' . $item->id,
                'nama_unit'         => 'SPARE PASIF 3% ' . $edisi,
                'no_cabang'         => null,
                'jumlah_pesanan'    => $qty,
                'alamat_unit'       => 'Spare Pasif',
                'telepon'           => null,
                'mitra_pengelolaan' => null,
                'kabupaten_kota'    => 'SPARE',
            ];

            $result = $this->createManualOrderFromUnit(
                $unit,
                $edisi,
                'SPARE PASIF',
                null,
                'A',
                $item->no_ps ?? null,
                'spare'
            );

            $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
        }

        DB::commit();

        $message = "✅ Sync Pesanan Majalah + DLC + Pasif + Bacaan + Spare Pasif 3% ke Manual selesai.<br>"
                 . "Berhasil masuk: <strong>{$created}</strong><br>"
                 . "Berhasil di-update (no_ps / qty): <strong>{$updated}</strong><br>"
                 . "Dilewati (sudah ada / qty 0): <strong>{$skipped}</strong>";

        if (count($skippedList) > 0) {
            $uniqueNames = array_values(array_unique($skippedList));
            $message .= "<br><br><strong>Unit tidak pesan (qty 0):</strong><br>"
                     . "• " . implode('<br>• ', $uniqueNames);
        }

        if (count($errors) > 0) {
            $message .= "<br><br>❌ Error (" . count($errors) . "):<br>"
                     . "• " . implode('<br>• ', array_slice($errors, 0, 5));
            if (count($errors) > 5) {
                $message .= "<br>• ... dan " . (count($errors) - 5) . " error lainnya";
            }
        }

        return redirect()
            ->route('import.manual')
            ->with('success', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Sync Pesanan Majalah + DLC + Pasif + Bacaan gagal: ' . $e->getMessage());

        return redirect()
            ->route('import.manual')
            ->with('error', 'Gagal sync: ' . $e->getMessage());
    }
}



private function handleManualSyncResult($result, &$created, &$skipped, &$skippedList, &$errors, &$updated = null)
{
    if ($result === 'created') {
        $created++;
    } elseif ($result === 'updated') {
        if ($updated !== null) {
            $updated++;
        } else {
            $skipped++; // fallback kalau tidak pakai counter updated
        }
    } elseif ($result === 'skipped') {
        $skipped++;
    } elseif (is_array($result) && ($result['status'] ?? '') === 'skipped_qty0') {
        $skipped++;
        $skippedList[] = $result['nama'];
    } else {
        $errors[] = is_string($result) ? $result : 'Unknown error';
    }
}

/**
 * Buat 1 record Manual Order dari 1 unit
 *
 * @param  object       $unit
 * @param  string       $edisi
 * @param  string|null  $wilayah
 * @param  string|null  $contactPerson
 * @param  string       $group   A | B | F
 * @param  string|null  $noPs
 * @param  string       $tipe    majalah | bacaan | spare
 */
private function createManualOrderFromUnit(
    $unit,
    string $edisi,
    $wilayah = null,
    $contactPerson = null,
    string $group = 'B',
    $noPs = null,
    string $tipe = 'majalah'
) {
    if (($unit->jumlah_pesanan ?? 0) <= 0) {
        return [
            'status' => 'skipped_qty0',
            'nama'   => ($unit->nama_unit ?? 'Unit #' . ($unit->id ?? '?'))
                        . (!empty($unit->no_cabang) ? ' (' . trim($unit->no_cabang) . ')' : ''),
        ];
    }

    $noCab    = trim($unit->no_cabang ?? '');
    $qty      = (int) $unit->jumlah_pesanan;
    $namaUnit = trim($unit->nama_unit ?? '');
    $idPesan  = $unit->id_pesan ?? null;

    // =====================================================
    // CEK EXISTING
    // =====================================================
    if ($group === 'A') {
        $existingQuery = ManualOrder::where('product_sku', $edisi)
            ->where('grup', 'A');

        if ($tipe === 'bacaan') {
            $existingQuery->where('product_name', 'like', '%Bacaan%');
        } elseif ($tipe === 'spare') {
            $existingQuery->where(function ($q) {
                $q->where('product_name', 'like', '%Spare%')
                  ->orWhere('customer_name', 'like', 'SPARE PASIF%');
            });
        } else {
            $existingQuery->where(function ($q) {
                $q->where('product_name', 'like', '%Majalah%')
                  ->orWhere(function ($q2) {
                      $q2->where('product_name', 'not like', '%Bacaan%')
                         ->where('product_name', 'not like', '%Spare%');
                  });
            });
        }

        $existing = null;

        if ($noCab !== '') {
            $existing = (clone $existingQuery)
                ->where('billing_last_name', $noCab)
                ->first();
        }

        if (!$existing) {
            $existing = (clone $existingQuery)
                ->where('customer_name', $namaUnit)
                ->first();
        }

    } elseif ($group === 'F') {

        $existingQuery = ManualOrder::where('product_sku', $edisi)
            ->where('grup', 'F');

        $existing = null;

        // =====================================================
        // KHUSUS PASIF MANUAL
        // =====================================================
        if (str_contains(strtoupper($wilayah ?? ''), 'MANUAL')) {

            $customOrderId = trim((string) ($unit->custom_order_id ?? ''));

            // 1. Cek by order_id = PP863900
            if ($customOrderId !== '') {
                $existing = ManualOrder::where('order_id', $customOrderId)->first();
            }

            // 2. Cek by ID_PESAN di notes (record lama Fxxxx)
            if (!$existing && !empty($idPesan)) {
                $existing = (clone $existingQuery)
                    ->where(function ($q) use ($idPesan) {
                        $q->where('notes', 'like', "%ID_PESAN:{$idPesan}%")
                          ->orWhere('catatan', 'like', "%ID_PESAN:{$idPesan}%");
                    })
                    ->first();

                // Migrasi order_id Fxxxx → PP863900
                if ($existing && $customOrderId !== '' && $existing->order_id !== $customOrderId) {
                    if (!ManualOrder::where('order_id', $customOrderId)->exists()) {
                        $existing->update(['order_id' => $customOrderId]);
                    }
                }
            }

            // TIDAK ada fallback customer_name saja
            // agar tidak nabrak record Fxxxx lain dengan nama sama

        } else {
            // PASIF BIASA
            if ($noCab !== '') {
                $existing = (clone $existingQuery)
                    ->where('billing_last_name', $noCab)
                    ->first();
            }

            if (!$existing) {
                $existing = (clone $existingQuery)
                    ->where('customer_name', $namaUnit)
                    ->first();
            }
        }

    } else {
        // Group B
        $existing = null;

        if ($noCab !== '') {
            $existing = ManualOrder::where('product_sku', $edisi)
                ->where('billing_last_name', $noCab)
                ->where('grup', 'B')
                ->first();
        }

        if (!$existing) {
            $existing = ManualOrder::where('product_sku', $edisi)
                ->where('customer_name', $namaUnit)
                ->where('grup', 'B')
                ->first();
        }
    }

    // =====================================================
    // JIKA SUDAH ADA → update / skip
    // =====================================================
    if ($existing) {
        if (
            in_array($group, ['A', 'F'])
            && $existing->status === 'pending'
            && !(bool) $existing->is_processed
            && (int) $existing->qty !== $qty
        ) {
            $existing->update([
                'qty'          => $qty,
                'order_weight' => (int) round(
                    ($existing->order_weight / max((int) $existing->qty, 1)) * $qty
                ),
            ]);
            return 'updated';
        }

        $needUpdate = empty($existing->no_ps)
            || ($noPs !== null && $existing->no_ps !== $noPs);

        if ($needUpdate && $noPs !== null) {
            try {
                $existing->update(['no_ps' => $noPs]);
                $this->syncNoPsToRelated($existing, $noPs);
                return 'updated';
            } catch (\Throwable $e) {
                Log::error("Gagal update no_ps ManualOrder #{$existing->id}: " . $e->getMessage());
                return $e->getMessage();
            }
        }

        return 'skipped';
    }

    // =====================================================
    // NAMA: prioritaskan Excel dari UnitNamaMismatch
    // =====================================================
    $namaDariUnit = trim($unit->nama_unit ?? '-');
    $namaUnit     = $namaDariUnit;
    $mitra        = $unit->mitra_pengelolaan ?? null;
    $isMismatch   = false;

    if ($noCab !== '') {
        $mismatch = UnitNamaMismatch::where('no_cab', $noCab)
            ->where('is_resolved', false)
            ->latest('id')
            ->first();

        if ($mismatch && !empty($mismatch->nama_excel)) {
            $namaUnit   = trim($mismatch->nama_excel);
            $isMismatch = true;
        }

        $uk = UnitKemitraan::where('no_cab', $noCab)->first();

        if ($uk) {
            if (!empty($uk->bimba_aiueo_unit)) {
                $namaMaster = trim($uk->bimba_aiueo_unit);

                if (strcasecmp($namaUnit, $namaMaster) !== 0) {
                    $isMismatch = true;

                    UnitNamaMismatch::updateOrCreate(
                        [
                            'no_cab'  => $noCab,
                            'periode' => now()->format('Y-m'),
                            'sumber'  => 'sync_manual_majalah',
                        ],
                        [
                            'nama_excel'  => $namaUnit,
                            'nama_master' => $namaMaster,
                            'is_resolved' => false,
                        ]
                    );
                } elseif (strcasecmp($namaDariUnit, $namaMaster) === 0 && $mismatch) {
                    $namaUnit   = trim($mismatch->nama_excel);
                    $isMismatch = true;
                }
            }

            $mitra = $uk->mitra_pengelolaan ?? $mitra;
        }
    }

    // =====================================================
    // Generate / Ambil Order ID
    // =====================================================
    $customOrderId = trim((string) ($unit->custom_order_id ?? ''));

    if ($customOrderId !== '' && str_contains(strtoupper($wilayah ?? ''), 'MANUAL')) {
        $orderId = $customOrderId;

        if (ManualOrder::where('order_id', $orderId)->exists()) {
            return 'skipped';
        }
    } else {
        $prefix = match ($group) {
            'A'     => 'A',
            'F'     => 'F',
            default => 'B',
        };

        $lastId = ManualOrder::where('order_id', 'like', $prefix . '%')
            ->whereRaw("order_id REGEXP '^{$prefix}[0-9]{4}$'")
            ->orderByRaw("CAST(SUBSTRING(order_id, 2) AS UNSIGNED) DESC")
            ->value('order_id');

        $nextNumber = 1;
        if ($lastId) {
            $nextNumber = (int) substr($lastId, 1) + 1;
        }

        $orderId = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        while (ManualOrder::where('order_id', $orderId)->exists()) {
            $nextNumber++;
            $orderId = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        }
    }

    // =====================================================
    // NOTES + NAMA PRODUK
    // =====================================================
    $parts = [];
    if ($isMismatch) {
        $parts[] = 'NAMA_MISMATCH';
    }
    if (!empty($contactPerson)) {
        $parts[] = 'CP: ' . $contactPerson;
    }

    if ($group === 'A' && $tipe === 'bacaan') {
        $parts[] = 'Sumber: Bacaan Unit';
    } elseif ($group === 'A' && $tipe === 'spare') {
        $parts[] = 'Sumber: Spare Pasif 3%';
    } elseif ($group === 'A') {
        $parts[] = 'Sumber: DLC';
    } elseif ($group === 'F') {
        if (str_contains(strtoupper($wilayah ?? ''), 'MANUAL')) {
            $parts[] = 'Sumber: Pasif Manual';
            if (!empty($idPesan)) {
                $parts[] = 'ID_PESAN:' . $idPesan;
            }
        } else {
            $parts[] = 'Sumber: Pasif';
        }
    }

    $notesText = implode(' | ', $parts);

    $productName = match ($tipe) {
        'bacaan' => 'Bacaan Unit ' . $edisi,
        'spare'  => 'Spare Pasif 3% ' . $edisi,
        default  => 'Majalah Sahabat biMBA ' . $edisi,
    };

    // =====================================================
    // HITUNG BERAT
    // =====================================================
    $beratSatuanKg = 0.070;

    $product = Product::where('label', $edisi)
        ->whereNotNull('berat_satuan')
        ->where('berat_satuan', '>', 0)
        ->first();

    if ($product) {
        $beratSatuanKg = (float) $product->berat_satuan;
    }

    $orderWeightGram = (int) round($beratSatuanKg * 1000 * $qty);

    // =====================================================
    // Kurir & Status
    // =====================================================
    if (in_array($group, ['A', 'F'])) {
        $statusKirim = null;
        $ekspedisi   = null;
        $service     = null;
    } else {
        $statusKirim = 'Dikirim';
        $ekspedisi   = 'Lion Parcel';
        $service     = 'REGPACK';
    }

    try {
        ManualOrder::create([
            'order_id'            => $orderId,
            'order_date'          => now(),
            'customer_name'       => $namaUnit,
            'phone'               => $unit->telepon ?? null,

            'product_sku'         => $edisi,
            'product_name'        => $productName,
            'qty'                 => $qty,
            'price'               => 0,
            'total'               => 0,

            'ship_total'          => 0,
            'order_weight'        => $orderWeightGram,
            'discount_total'      => 0,
            'refunded_total'      => 0,

            'payment_method'      => 'manual',
            'status'              => 'pending',
            'grup'                => $group,

            'billing_first_name'  => $mitra,
            'billing_last_name'   => $noCab ?: null,

            'shipping_first_name' => $namaUnit,
            'shipping_last_name'  => $noCab ?: null,
            'shipping_address_1'  => $unit->alamat_unit ?? $namaUnit,
            'shipping_address_2'  => null,
            'shipping_city'       => $wilayah,

            'status_kirim'        => $statusKirim,
            'ekspedisi'           => $ekspedisi,
            'service_pengiriman'  => $service,
            'is_processed'        => false,
            'payment_date'        => null,

            'no_ps'               => $noPs,

            'notes'               => $notesText,
            'catatan'             => $notesText,
        ]);

        return 'created';

    } catch (\Throwable $e) {
        Log::error("Gagal create ManualOrder dari unit " . ($unit->id ?? '?') . ": " . $e->getMessage());
        return $e->getMessage();
    }
}

private function extractEdisiMajalah(?string $text): string
{
    if (empty($text)) {
        return 'Majalah';
    }

    if (preg_match('/\bM\s*(\d{2,4})\b/i', $text, $m)) {
        return 'M' . $m[1];
    }

    if (preg_match('/(?:edisi|bulan|juli|agustus|september|oktober|november|desember|januari|februari|maret|april|mei|juni)\s*[^\d]*(\d{3})\b/i', $text, $m)) {
        return 'M' . $m[1];
    }

    return 'Majalah';
}

// =========================================================
// MENU MANUAL PRINTED (Rekap Aktual Manual)
// =========================================================
public function manualPrinted(Request $request)
{
    $query = ManualRealisasi::query();

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

    if ($request->filled('kategori')) {
        $query->where('kategori_order', $request->kategori);
    }

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

    // Assign rekap_number per tanggal (sama konsep jakarta)
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

    // Map mismatch untuk ditampilkan di view
    $mismatchMap = UnitNamaMismatch::where('is_resolved', false)
        ->get()
        ->keyBy(fn ($m) => trim((string) $m->no_cab))
        ->map(fn ($m) => [
            'nama_excel'  => $m->nama_excel,
            'nama_master' => $m->nama_master,
        ])
        ->all();

    return view('import.manual-printed', [
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

    $pdf = Pdf::loadView('import.manual-printed-pdf', [
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

    return view('import.manual-picking-list', [
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

    $pdf = Pdf::loadView('import.manual-picking-list-pdf', [
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
 * Sync Manual Order dari Bimbashop + Casdana
 * - Ganti order_id Manual yang auto menjadi order_id asli
 * - Isi payment_date jika Casdana SETTLED/SUCCESS
 * - Matching: billing_last_name (no_cab) + product_sku (edisi majalah)
 */
public function syncManualFromBimbashopCasdana()
{
    $updated = 0;
    $skippedNoCasdana = 0;
    $skippedNoMatch   = 0;

    // =====================================================
    // 1. Ambil semua order Bimbashop yang punya item Majalah
    // =====================================================
    $majalahOrders = BimbashopOrder::where(function ($q) {
            $q->where('item_sku', 'like', 'M%')
              ->orWhere('item_name', 'like', '%Majalah%')
              ->orWhere('item_name', 'like', '%Sahabat biMBA%');
        })
        ->get()
        ->groupBy('order_id');

    foreach ($majalahOrders as $orderId => $items) {

        // =================================================
        // 2. Cari Casdana (buang prefix "ID" jika ada)
        // =================================================
        $casdana = CasdanaTransaction::where(function ($q) use ($orderId) {
                $q->where('invoice_number', $orderId)
                  ->orWhere('invoice_number', 'ID' . $orderId)
                  ->orWhere('invoice_number', 'like', '%' . $orderId . '%');
            })
            ->latest('id')
            ->first();

        $paymentDate = null;
        $isPaid = false;

        if ($casdana) {
            $status = strtoupper(trim($casdana->status ?? ''));
            if (in_array($status, ['SUCCESS', 'SETTLED'])) {
                $paymentDate = $casdana->payment_date;
                $isPaid = true;
            }
        } else {
            $skippedNoCasdana++;
        }

        // =================================================
        // 3. Ambil daftar no_cab + edisi majalah dari order ini
        // =================================================
        $matches = [];

        foreach ($items as $item) {
            $noCab = trim($item->billing_last_name ?? '');
            if ($noCab === '') continue;

            // Normalisasi no_cab (buang leading zero untuk matching fleksibel)
            $noCabNormalized = ltrim($noCab, '0') ?: '0';

            // Ekstrak edisi dari SKU (M159-BDG1 → M159)
            $sku = strtoupper(trim($item->item_sku ?? ''));
            $edisi = null;

            if (preg_match('/\b(M\d{2,4})\b/i', $sku, $m)) {
                $edisi = strtoupper($m[1]);
            } elseif (preg_match('/\b(M\d{2,4})\b/i', $item->item_name ?? '', $m)) {
                $edisi = strtoupper($m[1]);
            }

            if (!$edisi) continue;

            $matches[] = [
                'no_cab'  => $noCab,
                'no_cab_n' => $noCabNormalized,
                'edisi'   => $edisi,
            ];
        }

        if (empty($matches)) {
            $skippedNoMatch++;
            continue;
        }

        // =================================================
        // 4. Cari ManualOrder yang cocok
        // =================================================
        foreach ($matches as $match) {

            $manualOrders = ManualOrder::where(function ($q) use ($match) {
                    $q->where('billing_last_name', $match['no_cab'])
                      ->orWhere('billing_last_name', $match['no_cab_n'])
                      ->orWhereRaw("TRIM(LEADING '0' FROM billing_last_name) = ?", [$match['no_cab_n']]);
                })
                ->where(function ($q) use ($match) {
                    $q->where('product_sku', $match['edisi'])
                      ->orWhere('product_sku', 'like', $match['edisi'] . '%')
                      ->orWhere('product_name', 'like', '%' . $match['edisi'] . '%');
                })
                // Hanya yang masih pakai order_id otomatis (6 digit) atau belum punya payment
                ->where(function ($q) {
                    $q->whereRaw("order_id REGEXP '^[0-9]{6}$'")
                      ->orWhereNull('payment_date');
                })
                ->get();

            foreach ($manualOrders as $manual) {

                // Skip jika sudah sama order_id-nya
                if ($manual->order_id == $orderId && $manual->payment_date) {
                    continue;
                }

                DB::transaction(function () use ($manual, $orderId, $paymentDate, $isPaid, &$updated) {

                    $oldOrderId = $manual->order_id;

                    // 1. Update ManualOrder
                    $manual->update([
                        'order_id'     => $orderId,
                        'payment_date' => $isPaid ? $paymentDate : $manual->payment_date,
                        'status'       => $isPaid ? 'completed' : $manual->status,
                        'catatan'      => ($manual->catatan ?? '') 
                            . "\n[SYNC] order_id diubah dari {$oldOrderId} → {$orderId} pada " 
                            . now()->format('d/m/Y H:i'),
                    ]);

                    // 2. Update ManualRealisasi
                    ManualRealisasi::where('manual_order_id', $manual->id)
                        ->update([
                            'no_pl'     => $orderId,
                            'tgl_bayar' => $isPaid ? $paymentDate : DB::raw('tgl_bayar'),
                        ]);

                    // 3. Update ManualPicking
                    ManualPicking::where('manual_order_id', $manual->id)
                        ->update([
                            'no_pl'        => $orderId,
                            'id_pesan'     => $orderId,
                            'payment_date' => $isPaid ? $paymentDate : DB::raw('payment_date'),
                        ]);

                    $updated++;
                });
            }
        }
    }

    return [
        'updated'            => $updated,
        'skipped_no_casdana' => $skippedNoCasdana,
        'skipped_no_match'   => $skippedNoMatch,
    ];
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
        $pdf = Pdf::loadView('import.manual-print-qc', [
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
        $pdf = Pdf::loadView('import.manual-print-pemesanan', [
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
        $pdf = Pdf::loadView('import.manual-print-packing', [
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
        $pdf = Pdf::loadView('import.manual-print-ekspedisi', [
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
 * Update no_ps ke ManualOrder + Realisasi + Picking terkait
 */
private function syncNoPsToRelated(ManualOrder $order, ?string $noPs = null): void
{
    $noPs = $noPs !== null ? $noPs : $order->no_ps;

    // 1. Update ManualOrder sendiri (jika beda)
    if ($order->no_ps !== $noPs) {
        $order->update(['no_ps' => $noPs]);
    }

    // 2. Update semua Realisasi dari order ini
    ManualRealisasi::where('manual_order_id', $order->id)
        ->update(['no_ps' => $noPs]);

    // 3. Update semua Picking dari order ini
    ManualPicking::where('manual_order_id', $order->id)
        ->update(['no_ps' => $noPs]);
}

/**
 * Backfill no_ps dari ManualOrder → Realisasi & Picking
 * untuk data yang sudah diproses
 */
public function syncNoPsManualExisting()
{
    $updated = 0;

    ManualOrder::whereNotNull('no_ps')
        ->where('no_ps', '!=', '')
        ->chunkById(200, function ($orders) use (&$updated) {
            foreach ($orders as $order) {
                $this->syncNoPsToRelated($order, $order->no_ps);
                $updated++;
            }
        });

    return redirect()
        ->route('import.manual')
        ->with('success', "✅ no_ps tersinkron ke Realisasi/Picking: {$updated} order");
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

// =========================================================
// DLC - Index
// =========================================================
public function dlcIndex()
{
    $periodes = DlcPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->latest()
        ->paginate(20);

    return view('import.dlc.index', compact('periodes'));
}

// =========================================================
// DLC - Form Create
// =========================================================
public function dlcCreate()
{
    return view('import.dlc.create');
}

// =========================================================
// DLC - Store
// =========================================================
public function dlcStore(Request $request)
{
    // Ambil hanya item yang terisi
    $items = collect($request->input('items', []))
        ->filter(function ($item) {
            return !empty(trim($item['nama_unit'] ?? '')) && (int)($item['qty'] ?? 0) > 0;
        })
        ->values()
        ->toArray();

    // Validasi ulang setelah difilter
    $request->merge(['items' => $items]);

    $request->validate([
        'edisi'             => 'required|string|max:20',
        'judul'             => 'nullable|string|max:100',
        'periode'           => 'required|string|max:50',
        'bulan'             => 'nullable|string|max:20',
        'tahun'             => 'nullable|integer|min:2020|max:2030',
        'items'             => 'required|array|min:1',
        'items.*.nama_unit' => 'required|string|max:150',
        'items.*.qty'       => 'required|integer|min:1',
    ], [
        'items.required'             => 'Minimal harus ada 1 unit yang diisi.',
        'items.min'                  => 'Minimal harus ada 1 unit yang diisi.',
        'items.*.nama_unit.required' => 'Nama unit wajib diisi.',
        'items.*.qty.required'       => 'Qty wajib diisi.',
        'items.*.qty.min'            => 'Qty minimal 1.',
    ]);

    DB::beginTransaction();
    try {
        $periode = DlcPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => $request->judul ?: 'Majalah ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        foreach ($items as $item) {
            DlcPesanan::create([
                'dlc_periode_id' => $periode->id,
                'nama_unit'      => trim($item['nama_unit']),
                'qty'            => (int) $item['qty'],
                'no_cab'         => $item['no_cab'] ?? null,
                'alamat'         => $item['alamat'] ?? null,
                'telepon'        => $item['telepon'] ?? null,
                'keterangan'     => $item['keterangan'] ?? null,
            ]);
        }

        DB::commit();

        return redirect()
            ->route('import.dlc.show', $periode->id)
            ->with('success', '✅ Data DLC berhasil disimpan!');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}

public function dlcEdit($id)
{
    $periode = DlcPeriode::with('pesanan')->findOrFail($id);
    return view('import.dlc.edit', compact('periode'));
}

public function dlcUpdate(Request $request, $id)
{
    $periode = DlcPeriode::findOrFail($id);

    $validated = $request->validate([
        'edisi'     => 'required|string|max:50',
        'judul'     => 'nullable|string|max:255',
        'periode'   => 'nullable|string|max:100',
        'bulan'     => 'nullable|string|max:50',
        'tahun'     => 'nullable|string|max:10',
        'status'    => 'required|in:aktif,nonaktif',
        'no_ps'     => 'nullable|string|max:100',

        // Unit
        'units'             => 'nullable|array',
        'units.*.id'        => 'nullable|integer',
        'units.*.nama_unit' => 'required|string|max:255',
        'units.*.no_cab'    => 'nullable|string|max:50',
        'units.*.qty'       => 'required|integer|min:0',
        'units.*.alamat'    => 'nullable|string',
        'units.*.telepon'   => 'nullable|string|max:30',
        'units.*.keterangan'=> 'nullable|string',
    ]);

    // Update header
    $periode->update([
        'edisi'   => $validated['edisi'],
        'judul'   => $validated['judul'] ?? null,
        'periode' => $validated['periode'] ?? null,
        'bulan'   => $validated['bulan'] ?? null,
        'tahun'   => $validated['tahun'] ?? null,
        'status'  => $validated['status'],
        'no_ps'   => $validated['no_ps'] ?? null,
    ]);

    // Update / Create / Delete units
    $existingIds = [];

    if (!empty($validated['units'])) {
        foreach ($validated['units'] as $unitData) {
            if (!empty($unitData['id'])) {
                // Update existing
                $pesanan = DlcPesanan::where('dlc_periode_id', $periode->id)
                    ->where('id', $unitData['id'])
                    ->first();

                if ($pesanan) {
                    $pesanan->update([
                        'nama_unit'  => $unitData['nama_unit'],
                        'no_cab'     => $unitData['no_cab'] ?? null,
                        'qty'        => $unitData['qty'],
                        'alamat'     => $unitData['alamat'] ?? null,
                        'telepon'    => $unitData['telepon'] ?? null,
                        'keterangan' => $unitData['keterangan'] ?? null,
                    ]);
                    $existingIds[] = $pesanan->id;
                }
            } else {
                // Create new
                $new = DlcPesanan::create([
                    'dlc_periode_id' => $periode->id,
                    'nama_unit'      => $unitData['nama_unit'],
                    'no_cab'         => $unitData['no_cab'] ?? null,
                    'qty'            => $unitData['qty'],
                    'alamat'         => $unitData['alamat'] ?? null,
                    'telepon'        => $unitData['telepon'] ?? null,
                    'keterangan'     => $unitData['keterangan'] ?? null,
                ]);
                $existingIds[] = $new->id;
            }
        }
    }

    // Hapus unit yang tidak ada di form
    DlcPesanan::where('dlc_periode_id', $periode->id)
        ->whereNotIn('id', $existingIds)
        ->delete();

    return redirect()
        ->route('import.dlc.index')
        ->with('success', 'Data DLC berhasil diupdate.');
}

// =========================================================
// DLC - Show Detail
// =========================================================
public function dlcShow($id)
{
    $periode = DlcPeriode::with('pesanan')->findOrFail($id);
    $total   = $periode->pesanan->sum('qty');

    return view('import.dlc.show', compact('periode', 'total'));
}

// =========================================================
// DLC - Hapus
// =========================================================
public function dlcDestroy($id)
{
    $periode = DlcPeriode::findOrFail($id);
    $periode->delete(); // cascade akan hapus pesanan juga

    return redirect()
        ->route('import.dlc.index')
        ->with('success', '✅ Data DLC berhasil dihapus');
}
public function dlcUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = DlcPeriode::findOrFail($id);
    $periode->update([
        'no_ps' => $request->no_ps,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

// =========================================================
// PASIF - MENU
// =========================================================
public function pasifMenu()
{
    return view('import.pasif.menu');
}

// =========================================================
// PASIF - UNIT PASIF (Majalah / Qty)
// =========================================================
public function pasifIndex()
{
    $periodes = PasifPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->latest()
        ->paginate(20);

    return view('import.pasif.index', compact('periodes'));
}

public function pasifCreate()
{
    return view('import.pasif.create');
}

public function pasifStore(Request $request)
{
    $request->validate([
        'import_file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        'edisi'       => 'required|string|max:20',
        'periode'     => 'nullable|string|max:50',
        'bulan'       => 'nullable|string|max:20',
        'tahun'       => 'nullable|string|max:10',
        'no_ps'       => 'nullable|string|max:100',
    ]);

    try {
        $file = $request->file('import_file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . $originalName;
        $file->storeAs('imports/pasif', $filename, 'public');

        DB::beginTransaction();

        // 1. Periode Unit Pasif (Qty)
        $pasifPeriode = PasifPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => 'Majalah Sahabat biMBA ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'no_ps'      => $request->no_ps,
            'grup'       => 'F',
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        // 2. Periode Bacaan Unit (database terpisah, no_ps sendiri)
        $bacaanPeriode = BacaanPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => 'Bacaan Unit ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'no_ps'      => null,
            'grup'       => 'F',
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        // 3. Import ke kedua tabel
        Excel::import(
            new \App\Imports\PasifImport($pasifPeriode->id, $bacaanPeriode->id),
            $file
        );

        DB::commit();

        return redirect()
            ->route('import.pasif.show', $pasifPeriode->id)
            ->with('success', '✅ Data Unit Pasif & Bacaan Unit berhasil diimport! File: ' . $originalName);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Pasif Import Error: " . $e->getMessage());
        return back()->with('error', '❌ Gagal import: ' . $e->getMessage());
    }
}

public function pasifShow($id)
{
    $periode = PasifPeriode::with('pesanan')->findOrFail($id);
    $total   = $periode->pesanan->sum('qty');

    return view('import.pasif.show', compact('periode', 'total'));
}

public function pasifDestroy($id)
{
    PasifPeriode::findOrFail($id)->delete();

    return redirect()
        ->route('import.pasif.list')
        ->with('success', '✅ Data Unit Pasif berhasil dihapus');
}

public function pasifUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = PasifPeriode::findOrFail($id);
    $periode->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

public function syncPasifToManual()
{
    $created = 0;
    $updated = 0;
    $skipped = 0;
    $errors  = [];
    $skippedList = [];

    $periodes = PasifPeriode::with('pesanan')
        ->where('status', 'aktif')
        ->get();

    DB::beginTransaction();

    try {
        foreach ($periodes as $periode) {
            $edisi = strtoupper(trim($periode->edisi));

            foreach ($periode->pesanan as $item) {
                if (($item->qty ?? 0) <= 0) {
                    $skipped++;
                    $skippedList[] = $item->nama_unit . ' (Pasif)';
                    continue;
                }

                $unit = (object) [
                    'id'                => $item->id,
                    'nama_unit'         => $item->nama_unit,
                    'no_cabang'         => $item->no_cab,
                    'jumlah_pesanan'    => $item->qty,
                    'alamat_unit'       => $item->alamat ?? $item->nama_unit,
                    'telepon'           => $item->telepon,
                    'mitra_pengelolaan' => null,
                    'kabupaten_kota'    => 'PASIF',
                ];

                $result = $this->createManualOrderFromUnit(
                    $unit,
                    $edisi,
                    'PASIF',
                    null,
                    'F',
                    $periode->no_ps
                );

                $this->handleManualSyncResult($result, $created, $skipped, $skippedList, $errors, $updated);
            }
        }

        DB::commit();

        $message = "✅ Sync Unit Pasif ke Manual selesai.<br>"
                 . "Berhasil masuk: <strong>{$created}</strong><br>"
                 . "Berhasil di-update: <strong>{$updated}</strong><br>"
                 . "Dilewati: <strong>{$skipped}</strong>";

        return redirect()
            ->route('import.manual')
            ->with('success', $message);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Sync Pasif gagal: ' . $e->getMessage());

        return redirect()
            ->route('import.pasif.list')
            ->with('error', 'Gagal sync: ' . $e->getMessage());
    }
}

// =========================================================
// PASIF - BACAAN UNIT (database terpisah)
// =========================================================
public function pasifBacaan()
{
    $periodes = BacaanPeriode::withCount('pesanan')
        ->withSum('pesanan', 'bacaan_unit')
        ->latest()
        ->paginate(20);

    return view('import.pasif.bacaan', compact('periodes'));
}

public function pasifBacaanShow($id)
{
    $periode     = BacaanPeriode::with('pesanan')->findOrFail($id);
    $totalBacaan = $periode->pesanan->sum('bacaan_unit');

    return view('import.pasif.bacaan-show', compact('periode', 'totalBacaan'));
}

public function pasifBacaanUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = BacaanPeriode::findOrFail($id);
    $periode->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

public function pasifBacaanDestroy($id)
{
    BacaanPeriode::findOrFail($id)->delete();

    return redirect()
        ->route('import.pasif.bacaan')
        ->with('success', '✅ Data Bacaan Unit berhasil dihapus');
}

// =========================================================
// PASIF - REKAP TOTAL
// =========================================================
public function pasifRekap()
{
    $periodes = PasifPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->withSum('pesanan', 'bacaan_unit')
        ->latest()
        ->paginate(20);

    return view('import.pasif.rekap', compact('periodes'));
}

public function pasifRekapShow($id)
{
    $periode      = PasifPeriode::with('pesanan')->findOrFail($id);
    $totalBacaan  = $periode->pesanan->sum('bacaan_unit');
    $totalMajalah = $periode->pesanan->sum('qty');

    return view('import.pasif.rekap-show', compact('periode', 'totalBacaan', 'totalMajalah'));
}



public function sparePasif()
{
    // ===== Auto generate / refresh =====
    $this->syncSparePasifData();

    $data = SparePasif::where('status', 'aktif')
        ->orderBy('edisi')
        ->get();

    return view('import.pasif.spare', compact('data'));
}

/**
 * Detail per edisi
 */
public function sparePasifShow($edisi)
{
    $edisi = strtoupper(trim($edisi));

    $item = SparePasif::where('edisi', $edisi)
        ->where('status', 'aktif')
        ->firstOrFail();

    return view('import.pasif.spare-show', [
        'edisi'       => $item->edisi,
        'dlc'         => $item->dlc_total,
        'pasif'       => $item->pasif_total,
        'bacaan'      => $item->bacaan_total,
        'grand_total' => $item->grand_total,
        'spare_raw'   => $item->spare_raw,
        'spare'       => $item->spare,
        'lembar'      => $item->lembar,
        'grup'        => $item->grup,
        'no_ps'       => $item->no_ps,
        'item'        => $item, // kalau view butuh full model
    ]);
}

/**
 * Logic hitung + simpan (dipakai di list & bisa dipanggil ulang)
 */
private function syncSparePasifData(): void
{
    $edisiList = collect()
        ->merge(DlcPeriode::where('status', 'aktif')->pluck('edisi'))
        ->merge(PasifPeriode::where('status', 'aktif')->pluck('edisi'))
        ->merge(BacaanPeriode::where('status', 'aktif')->pluck('edisi'))
        ->map(fn ($e) => strtoupper(trim($e)))
        ->filter()
        ->unique()
        ->sort()
        ->values();

    foreach ($edisiList as $edisi) {
        $dlcTotal = (int) DlcPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')
              ->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('qty');

        $pasifTotal = (int) PasifPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')
              ->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('qty');

        $bacaanTotal = (int) BacaanPesanan::whereHas('periode', function ($q) use ($edisi) {
            $q->where('status', 'aktif')
              ->whereRaw('UPPER(TRIM(edisi)) = ?', [$edisi]);
        })->sum('bacaan_unit');

        $grandTotal = $dlcTotal + $pasifTotal + $bacaanTotal;
        $spareRaw   = $grandTotal * 0.03;
        $spare      = (int) round($spareRaw);
        $lembar     = $spare <= 0 ? 0 : (int) ceil($spare / 200);

        SparePasif::updateOrCreate(
            [
                'edisi'  => $edisi,
                'status' => 'aktif',
            ],
            [
                'judul'         => 'Spare Pasif 3% ' . $edisi,
                'dlc_total'     => $dlcTotal,
                'pasif_total'   => $pasifTotal,
                'bacaan_total'  => $bacaanTotal,
                'grand_total'   => $grandTotal,
                'spare_raw'     => round($spareRaw, 3),
                'spare'         => $spare,
                'lembar'        => $lembar,
                'grup'          => 'A',          // selalu grup A
                'created_by'    => Auth::id(),
            ]
        );
    }
}

/**
 * Update No PS Spare Pasif (opsional)
 */
public function sparePasifUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $item = SparePasif::findOrFail($id);
    $item->update(['no_ps' => $request->no_ps]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $item->no_ps,
    ]);
}

// =========================================================
// PASIF MANUAL - CREATE & STORE
// =========================================================

public function pasifManualCreate()
{
    return view('import.pasif.manual-create');
}

public function pasifManualStore(Request $request)
{
    $items = collect($request->input('items', []))
        ->filter(function ($item) {
            return !empty(trim($item['nama_unit'] ?? '')) && (int)($item['jumlah'] ?? 0) > 0;
        })
        ->values()
        ->toArray();

    $request->merge(['items' => $items]);

    $request->validate([
        'edisi'                 => 'required|string|max:20',
        'judul'                 => 'nullable|string|max:150',
        'periode'               => 'nullable|string|max:50',
        'bulan'                 => 'nullable|string|max:20',
        'tahun'                 => 'nullable|string|max:10',
        'no_ps'                 => 'nullable|string|max:100',
        'items'                 => 'required|array|min:1',
        'items.*.nama_unit'     => 'required|string|max:150',
        'items.*.jumlah'        => 'required|integer|min:1',
        'items.*.id_pesan'      => 'nullable|string|max:50',
        'items.*.kode_pesan'    => 'nullable|string|max:50',
        'items.*.tgl_pesan'     => 'nullable|date',
        'items.*.minggu'        => 'nullable|string|max:20',
        'items.*.label'         => 'nullable|string|max:30',
        'items.*.pesanan'       => 'nullable|string|max:150',
        'items.*.note'          => 'nullable|string',
        'items.*.keterangan'    => 'nullable|string|max:255',
        'items.*.no_cab'        => 'nullable|string|max:50',
        'items.*.alamat'        => 'nullable|string',
        'items.*.telepon'       => 'nullable|string|max:30',
    ], [
        'items.required'            => 'Minimal harus ada 1 unit yang diisi.',
        'items.min'                 => 'Minimal harus ada 1 unit yang diisi.',
        'items.*.nama_unit.required'=> 'Nama unit wajib diisi.',
        'items.*.jumlah.required'   => 'Jumlah wajib diisi.',
        'items.*.jumlah.min'        => 'Jumlah minimal 1.',
    ]);

    DB::beginTransaction();
    try {
        // 1. Buat Header Periode Manual
        $periode = \App\Models\PasifManualPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => $request->judul ?: 'Majalah Edisi ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'no_ps'      => $request->no_ps,
            'grup'       => 'F',
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        // 2. Simpan detail transaksi
        foreach ($items as $index => $item) {
            \App\Models\PasifManualTransaksi::create([
                'pasif_manual_periode_id' => $periode->id,
                'no'                      => $index + 1,
                'id_pesan'                => $item['id_pesan'] ?? null,
                'kode_pesan'              => $item['kode_pesan'] ?? null,
                'tgl_pesan'               => $item['tgl_pesan'] ?? null,
                'minggu'                  => $item['minggu'] ?? null,
                'nama_unit'               => trim($item['nama_unit']),
                'label'                   => $item['label'] ?? ('M' . strtoupper($request->edisi)),
                'jumlah'                  => (int) $item['jumlah'],
                'pesanan'                 => $item['pesanan'] ?? ('Majalah Edisi ' . strtoupper($request->edisi)),
                'note'                    => $item['note'] ?? null,
                'keterangan'              => $item['keterangan'] ?? null,
                'no_cab'                  => $item['no_cab'] ?? null,
                'alamat'                  => $item['alamat'] ?? null,
                'telepon'                 => $item['telepon'] ?? null,
            ]);
        }

        DB::commit();

        return redirect()
            ->route('import.pasif.manual.show', $periode->id)
            ->with('success', '✅ Data Pasif Manual berhasil disimpan! Total unit: ' . count($items));
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Pasif Manual Store Error: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}

public function pasifManualShow($id)
{
    $periode = \App\Models\PasifManualPeriode::with('transaksis')->findOrFail($id);
    $total   = $periode->transaksis->sum('jumlah');

    return view('import.pasif.manual-show', compact('periode', 'total'));
}

public function pasifManualIndex()
{
    $periodes = \App\Models\PasifManualPeriode::withCount('transaksis')
        ->withSum('transaksis', 'jumlah')
        ->latest()
        ->paginate(20);

    return view('import.pasif.manual-index', compact('periodes'));
}

public function pasifManualDestroy($id)
{
    $periode = \App\Models\PasifManualPeriode::findOrFail($id);
    $periode->delete();

    return redirect()
        ->route('import.pasif.manual.index')
        ->with('success', '✅ Data Pasif Manual berhasil dihapus');
}
}
