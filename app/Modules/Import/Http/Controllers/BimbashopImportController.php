<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Imports\BimbashopImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Services\ShopMatcher;
use Illuminate\Support\Facades\Log;
use App\Modules\Import\Http\Controllers\Concerns\ManualOrderSyncHelpers;

class BimbashopImportController extends Controller
{
    use ManualOrderSyncHelpers;


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
    $perPage = in_array((int) $perPage, [5, 10, 25, 50, 100, 200, 500]) ? (int) $perPage : 5; // security

    $bimbashopOrders = $query
                        ->latest()
                        ->paginate($perPage)
                        ->appends($request->query());

    // Pilihan filter Payment Method diambil langsung dari data order (nilai unik kolom payment_method)
    $paymentMethods = BimbashopOrder::query()
        ->whereNotNull('payment_method')
        ->where('payment_method', '!=', '')
        ->distinct()
        ->orderBy('payment_method')
        ->pluck('payment_method');

    return view('import::import.bimbashop', compact('bimbashopOrders', 'paymentMethods'));
}
    /**
     * Proses Import Data
     */
    public function bimbashopStore(Request $request)
    {
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
                        $fail('File harus bertipe xlsx, xls, atau csv.');
                    }
                },
            ],
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

            // === SYNC STATUS: order yang Casdana-nya sudah SUCCESS -> processing ===
            // (import ulang file Shop mengembalikan status ke on-hold, jadi harus dijalankan lagi di sini)
            $infoStatus = '';
            try {
                $r = app(\App\Modules\Import\Services\CasdanaStatusSync::class)->sync();
                $infoStatus = " {$r['order_diubah']} order menjadi Processing (Casdana sukses).";
            } catch (\Throwable $e) {
                Log::error('Sync status Shop dari Casdana gagal: ' . $e->getMessage());
            }

            // === SYNC MANUAL ===
            $info = '';
            try {
                $result = app(ShopMatcher::class)->syncManual();
                Log::info("Pencocokan Manual dengan biMBA Shop: " . json_encode($result));
                $info = " Pencocokan Manual: {$result['cocok']} cocok, {$result['menunggu']} masih menunggu biMBA Shop.";
            } catch (\Throwable $e) {
                Log::error("Pencocokan Manual gagal: " . $e->getMessage());
            }

            return redirect()->route('import.bimbashop')
                             ->with('success', 'Data biMBA Shop berhasil diimport! File: ' . $originalName . '.' . $infoStatus . $info);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            // Error validasi Excel (heading, format, dll)
            $failures = $e->failures();
            $errorMsg = 'Validasi gagal: ';
            foreach ($failures as $failure) {
                $errorMsg .= "Baris {$failure->row()} kolom {$failure->attribute()} → {$failure->errors()[0]} | ";
            }

            Log::error("Import Validation Error: " . $errorMsg);
            
            return redirect()->route('import.bimbashop')
                             ->with('error', '❌ ' . $errorMsg);

        } catch (\Exception $e) {
            Log::error("Import Error: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            return redirect()->route('import.bimbashop')
                             ->with('error', '❌ Gagal mengimport data: ' . $e->getMessage());
        }
    }

    public function bimbashopEdit($id)
{
    $order = BimbashopOrder::findOrFail($id);
    return view('import::import.bimbashop-edit', compact('order'));
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
}