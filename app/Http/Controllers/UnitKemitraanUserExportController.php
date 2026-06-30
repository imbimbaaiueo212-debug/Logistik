<?php

namespace App\Http\Controllers;

use App\Models\UnitKemitraan;
use App\Models\UserExportBimbaShop;
use App\Imports\UnitKemitraanImport;
use App\Imports\UserExportBimbaShopImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UnitKemitraanUserExportController extends Controller
{
    /**
     * Halaman Index Gabungan (Unit Kemitraan + Matching User Export)
     */
    public function index(Request $request)
{
    $query = UnitKemitraan::query();

    // Filter biasa
    if ($request->filled('no_cab')) {
        $query->where('no_cab', 'like', '%' . $request->no_cab . '%');
    }
    if ($request->filled('nama_mitra')) {
        $query->where('nama_mitra', 'like', '%' . $request->nama_mitra . '%');
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('provinsi')) {
        $query->where('provinsi', 'like', '%' . $request->provinsi . '%');
    }
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('no_cab', 'like', "%{$search}%")
              ->orWhere('nama_mitra', 'like', "%{$search}%")
              ->orWhere('bimba_aiueo_unit', 'like', "%{$search}%")
              ->orWhere('alamat_unit', 'like', "%{$search}%");
        });
    }

    // Filter Matching First Name (Dropdown)
    if ($request->filled('matching_status')) {
        $status = $request->matching_status;
        if ($status === 'ditemukan') {
            $query->whereExists(function($sub) {
                $sub->selectRaw(1)
                    ->from('user_export_bimba_shop')
                    ->whereColumn('user_export_bimba_shop.first_name', 'like', DB::raw("CONCAT('%', unit_kemitraan.no_cab, '%')"));
            });
        } elseif ($status === 'tidak_ditemukan') {
            $query->whereNotExists(function($sub) {
                $sub->selectRaw(1)
                    ->from('user_export_bimba_shop')
                    ->whereColumn('user_export_bimba_shop.first_name', 'like', DB::raw("CONCAT('%', unit_kemitraan.no_cab, '%')"));
            });
        }
    }

    $unitKemitraans = $query->latest()->paginate(20)->appends($request->all());

    $userExports = UserExportBimbaShop::all(['ID', 'user_login', 'user_email', 'display_name', 'first_name', 'last_name']);

    return view('unit_kemitraan_user.index', compact('unitKemitraans', 'userExports'));
}
    /**
     * Import Unit Kemitraan
     */
    public function importUnit(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv|max:51200',
        ]);

        try {
            set_time_limit(900);
            ini_set('memory_limit', '1024M');

            Excel::import(new UnitKemitraanImport, $request->file('import_file'));

            return redirect()->route('unit-kemitraan-user.index')
                             ->with('success', '✅ Import Unit Kemitraan berhasil!');
        } catch (\Exception $e) {
            Log::error('Import Unit Error: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Import User Export biMBA Shop
     */
    public function importUserExport(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            Excel::import(new UserExportBimbaShopImport, $request->file('file'));

            return redirect()->route('unit-kemitraan-user.index')
                             ->with('success', '✅ Import User Export berhasil!');
        } catch (\Exception $e) {
            Log::error('Import User Export Error: ' . $e->getMessage());
            return redirect()->back()->with('error', '❌ Gagal: ' . $e->getMessage());
        }
    }

    // CRUD Unit Kemitraan (bisa ditambah sesuai kebutuhan)
    public function create() { return view('unit_kemitraan.create'); }
    public function store(Request $request) { /* ... */ }
    // edit, update, destroy, show bisa ditambahkan
}