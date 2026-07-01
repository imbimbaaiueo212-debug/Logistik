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

    // ==========================
    // Filter No Cabang
    // ==========================
    if ($request->filled('no_cab')) {
        $query->where('no_cab', 'like', '%' . trim($request->no_cab) . '%');
    }

    // ==========================
    // Filter No Induk Mitra
    // ==========================
    if ($request->filled('no_induk_mitra')) {
        $query->where('no_induk_mitra', 'like', '%' . trim($request->no_induk_mitra) . '%');
    }

    // ==========================
    // Filter Status
    // ==========================
    if ($request->filled('status') && $request->status !== 'all') {
        $query->where('status', $request->status);
    }

    // ==========================
    // Filter Provinsi
    // ==========================
    if ($request->filled('provinsi')) {
        $query->where('provinsi', 'like', '%' . trim($request->provinsi) . '%');
    }

    // ==========================
    // Filter Status Pengelolaan
    // ==========================
    if ($request->filled('status_pengelolaan')
        && $request->status_pengelolaan !== 'all') {

        if ($request->status_pengelolaan === 'Aktif') {
            $query->where('status_pengelolaan', 'Unit Aktif');
        } elseif ($request->status_pengelolaan === 'Pasif') {
            $query->where('status_pengelolaan', 'Unit Pasif');
        } else {
            $query->where('status_pengelolaan', $request->status_pengelolaan);
        }
    }

    // ==========================
    // Filter Mitra Pengelolaan
    // ==========================
    if ($request->filled('mitra_pengelolaan')
        && $request->mitra_pengelolaan !== 'all') {

        $query->where('mitra_pengelolaan', $request->mitra_pengelolaan);
    }

    // ==========================
    // Filter Matching User Export
    // ==========================
    if ($request->filled('matching_status')) {

        if ($request->matching_status === 'ditemukan') {

            $query->whereExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('user_export_bimba_shop')
                    ->whereColumn(
                        'user_export_bimba_shop.billing_last_name',
                        'like',
                        DB::raw("CONCAT('%', unit_kemitraan.no_cab, '%')")
                    );
            });

        } elseif ($request->matching_status === 'tidak_ditemukan') {

            $query->whereNotExists(function ($sub) {
                $sub->selectRaw(1)
                    ->from('user_export_bimba_shop')
                    ->whereColumn(
                        'user_export_bimba_shop.billing_last_name',
                        'like',
                        DB::raw("CONCAT('%', unit_kemitraan.no_cab, '%')")
                    );
            });

        }
    }

    // ==========================
    // Search
    // ==========================
    if ($request->filled('search')) {

        $search = trim($request->search);

        $query->where(function ($q) use ($search) {
            $q->where('no_cab', 'like', "%{$search}%")
              ->orWhere('nama_mitra', 'like', "%{$search}%")
              ->orWhere('bimba_aiueo_unit', 'like', "%{$search}%")
              ->orWhere('alamat_unit', 'like', "%{$search}%")
              ->orWhere('provinsi', 'like', "%{$search}%");
        });
    }

    $unitKemitraans = $query
        ->orderBy('id_record', 'desc')
        ->paginate(20)
        ->appends($request->query());

    $userExports = UserExportBimbaShop::select(
        'ID',
        'user_login',
        'user_email',
        'display_name',
        'first_name',
        'last_name'
    )->get();

    return view('unit_kemitraan_user.index', compact(
        'unitKemitraans',
        'userExports'
    ));
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