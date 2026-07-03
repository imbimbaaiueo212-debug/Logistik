<?php

namespace App\Http\Controllers;

use App\Imports\BimbashopImport;
use App\Models\ManualOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ManualOrderImport;        // ← Tambahkan ini
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

    // === MANUAL PEMESANAN ===
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
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }

        $perPage = $request->get('per_page', 25);
        $perPage = in_array($perPage, [5,10,25,50,100,200,500]) ? $perPage : 25;

        $manualOrders = $query->latest()->paginate($perPage)->appends($request->query());

        return view('import.manual.index', compact('manualOrders'));
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
}
