<?php

namespace App\Http\Controllers;

use App\Imports\UserExportBimbaShopImport;
use App\Models\UserExportBimbaShop;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class UserExportController extends Controller
{
   public function index(Request $request)
{
    $search = $request->get('search');
    $perPage = $request->get('per_page', 50); // default 50

    $users = UserExportBimbaShop::query()
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('ID', 'like', "%{$search}%")
                  ->orWhere('user_login', 'like', "%{$search}%")
                  ->orWhere('user_email', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhere('billing_phone', 'like', "%{$search}%")
                  ->orWhere('billing_first_name', 'like', "%{$search}%")
                  ->orWhere('billing_last_name', 'like', "%{$search}%");
            });
        })
        ->latest('ID')
        ->paginate($perPage)
        ->appends(['search' => $search, 'per_page' => $perPage]);

    return view('user-export.index', compact('users', 'search', 'perPage'));
}

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            ini_set('max_execution_time', 300);
            ini_set('memory_limit', '512M');

            Excel::import(new UserExportBimbaShopImport, $request->file('file'));

            return redirect()->route('user.export')
                ->with('success', '✅ Data berhasil diimport!');

        } catch (\Exception $e) {
            Log::error('Import Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', '❌ Gagal import: ' . $e->getMessage());
        }
    }
}