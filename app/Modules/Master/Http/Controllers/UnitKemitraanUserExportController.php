<?php

namespace App\Modules\Master\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Modules\Master\Models\UnitKemitraan;
use App\Modules\Master\Models\UserExportBimbaShop;
use App\Modules\Master\Models\MatchingUserExport;
use App\Modules\Master\Imports\UnitKemitraanImport;
use App\Modules\Master\Imports\UserExportBimbaShopImport;
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

    // ---- Pencocokan No Cab <-> billing_last_name (nol di depan diabaikan) ----
    // Dibuat TANPA subquery berkorelasi: daftar No Cab dari biMBA Shop diambil sekali,
    // lalu dipakai sebagai daftar IN. Cara lama (EXISTS per baris unit) memindai tabel
    // biMBA Shop untuk setiap unit sehingga bisa memakan puluhan detik.
    $normCab     = fn ($v) => ltrim(trim((string) $v), '0');
    $unitCabExpr = "TRIM(LEADING '0' FROM TRIM(unit_kemitraan.no_cab))";
    $inList      = fn (array $v) => implode(',', array_fill(0, count($v), '?'));

    // ==========================
    // Pencarian Gabungan (satu kolom)
    // No Cab, NIM, nama mitra, nama unit, email, no HP/telp, alamat
    // (data Mitra dari unit_kemitraan + data Unit dari akun biMBA Shop)
    // ==========================
    $cari = trim((string) ($request->input('q') ?? $request->input('no_cab')));
    if ($cari !== '') {
        $like = '%' . $cari . '%';

        $query->where(function ($q) use ($like, $normCab, $unitCabExpr, $inList) {
            // --- sisi MITRA / unit_kemitraan ---
            $q->where('no_cab', 'like', $like)
              ->orWhere('no_induk_mitra', 'like', $like)
              ->orWhere('nama_mitra', 'like', $like)
              ->orWhere('bimba_aiueo_unit', 'like', $like)
              ->orWhere('email', 'like', $like)
              ->orWhere('email_mitra', 'like', $like)
              ->orWhere('no_hp', 'like', $like)
              ->orWhere('no_hp_mitra', 'like', $like)
              ->orWhere('alamat_mitra', 'like', $like);

            // --- sisi UNIT / akun biMBA Shop (dicocokkan lewat No Cab = billing_last_name) ---
            // Cari dulu akun shop yang cocok (tabel kecil), lalu ambil No Cab-nya.
            $shopCabs = UserExportBimbaShop::query()
                ->whereRaw("TRIM(billing_last_name) <> ''")
                ->where(function ($w) use ($like) {
                    $w->where('user_email', 'like', $like)
                      ->orWhere('billing_email', 'like', $like)
                      ->orWhere('billing_phone', 'like', $like)
                      ->orWhere('billing_address_1', 'like', $like)
                      ->orWhere('billing_city', 'like', $like)
                      ->orWhere('display_name', 'like', $like)
                      ->orWhere('first_name', 'like', $like);
                })
                ->pluck('billing_last_name')
                ->map($normCab)->filter()->unique()->values()->all();

            if (! empty($shopCabs)) {
                $q->orWhereRaw("{$unitCabExpr} IN ({$inList($shopCabs)})", $shopCabs);
            }
        });
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

        $allCabs = UserExportBimbaShop::query()
            ->whereRaw("TRIM(billing_last_name) <> ''")
            ->pluck('billing_last_name')
            ->map($normCab)->filter()->unique()->values()->all();

        if ($request->matching_status === 'ditemukan') {

            if (empty($allCabs)) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereRaw("{$unitCabExpr} IN ({$inList($allCabs)})", $allCabs);
            }

        } elseif ($request->matching_status === 'tidak_ditemukan') {

            if (! empty($allCabs)) {
                $query->where(function ($w) use ($unitCabExpr, $inList, $allCabs) {
                    $w->whereNull('unit_kemitraan.no_cab')
                      ->orWhereRaw("{$unitCabExpr} NOT IN ({$inList($allCabs)})", $allCabs);
                });
            }
        }
    }

    $unitKemitraans = $query
        ->orderBy('id_record', 'desc')
        ->paginate(50)
        ->appends($request->query());

    // Pilihan filter Jenis Unit diambil dari data (kolom status di unit_kemitraan), bukan daftar tetap
    $jenisList = UnitKemitraan::query()
        ->whereNotNull('status')
        ->whereRaw("TRIM(status) <> ''")
        ->distinct()
        ->orderBy('status')
        ->pluck('status');

    $userExports = UserExportBimbaShop::select(
        'ID',
        'user_login',
        'user_email',
        'display_name',
        'first_name',
        'last_name',
        'billing_last_name',
        'billing_email',
        'billing_phone',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_state',
        'billing_postcode'
    )->get();

    // Kunci pencocokan = No Cab (billing_last_name) persis, nol di depan diabaikan.
    // Tidak memakai pencarian sebagian, jadi 4909 tidak cocok dengan 14909 / 49091.
    $userByCab = $userExports
        ->filter(fn ($u) => trim((string) $u->billing_last_name) !== '')
        ->groupBy(fn ($u) => ltrim(trim((string) $u->billing_last_name), '0'));

    return view('master::unit_kemitraan_user.index', compact(
        'unitKemitraans',
        'userExports',
        'userByCab',
        'jenisList'
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

    /**
 * Generate Matching antara Unit Kemitraan dan User Export
 */
public function generateMatch(Request $request)
{
    try {
        set_time_limit(600);
        ini_set('memory_limit', '1024M');

        $matchesCreated = 0;
        $skipped = 0;

        $units = UnitKemitraan::whereDoesntHave('matchingUserExport')
            ->select('id_record', 'no_cab')
            ->get();

        foreach ($units as $unit) {
            $noCab = trim($unit->no_cab ?? '');
            if (empty($noCab)) {
                $skipped++;
                continue;
            }

            $userExports = UserExportBimbaShop::whereRaw("TRIM(LEADING '0' FROM TRIM(billing_last_name)) = ?", [ltrim($noCab, '0') ?: '0'])
                ->select('ID', 'billing_last_name')
                ->get();

            foreach ($userExports as $user) {
                $exists = MatchingUserExport::where('unit_kemitraan_id', $unit->id_record)
                            ->where('user_export_id', $user->ID)
                            ->exists();

                if (!$exists) {
                    MatchingUserExport::create([
                        'unit_kemitraan_id' => $unit->id_record,
                        'user_export_id'    => $user->ID,
                        'no_cab'            => $noCab,
                        'billing_last_name' => trim($user->billing_last_name ?? ''),
                        'status'            => true,
                    ]);
                    $matchesCreated++;
                }
            }
        }

        $message = "✅ Generate Match selesai! {$matchesCreated} matching baru dibuat.";
        if ($skipped > 0) $message .= " {$skipped} unit dilewati (no_cab kosong).";

        return redirect()->route('unit-kemitraan-user.index')->with('success', $message);

    } catch (\Exception $e) {
        Log::error('Generate Match Error: ' . $e->getMessage() . ' | Line: ' . $e->getLine());
        return redirect()->back()->with('error', '❌ Gagal generate match: ' . $e->getMessage());
    }
}

    // CRUD Unit Kemitraan (bisa ditambah sesuai kebutuhan)
    public function create() { return view('master::unit_kemitraan.create'); }
    public function store(Request $request) { /* ... */ }
    // edit, update, destroy, show bisa ditambahkan    
}