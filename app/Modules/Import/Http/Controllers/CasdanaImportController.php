<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Modules\Import\Models\CasdanaTransaction;
use Illuminate\Support\Facades\Log;
use App\Modules\Import\Http\Controllers\Concerns\ManualOrderSyncHelpers;

class CasdanaImportController extends Controller
{
    use ManualOrderSyncHelpers;


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

    return view('import::import.casdana', compact('casdanaTransactions'));
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
        Excel::import(new \App\Modules\Import\Imports\CasdanaImport, $file);

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
        return view('import::import.casdana-edit', compact('transaction'));
    }
}
