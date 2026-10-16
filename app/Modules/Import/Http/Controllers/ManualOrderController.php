<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\ManualOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Import\Imports\ManualOrderImport;        // ← Tambahkan ini
use App\Modules\Master\Models\UnitNamaMismatch;
use App\Modules\Import\Models\ManualRealisasi;
use App\Modules\Picking\Models\ManualPicking;
use App\Modules\Picking\Models\ManualPickingItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Modules\Import\Http\Controllers\Concerns\ManualOrderSyncHelpers;

class ManualOrderController extends Controller
{
    use ManualOrderSyncHelpers;


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

    return view('import::import.manual.index', compact('manualOrders', 'mismatchMap', 'grups'));
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
        return view('import::import.manual.create');
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
        return view('import::import.manual.edit', compact('order'));
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
}
