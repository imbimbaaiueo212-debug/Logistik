<?php

namespace App\Http\Controllers;

use App\Imports\StokisMitraImport;
use App\Models\StokisMitra;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class StokisMitraController extends Controller
{
    public function index(Request $request)
{
    $search = $request->get('search');
    $perPage = $request->get('per_page', 50);   // default 50

    $stokis = StokisMitra::query()
        ->when($search, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('no_cab', 'like', "%{$search}%")
                  ->orWhere('nama_stokis_db_kemitraan', 'like', "%{$search}%")
                  ->orWhere('nama_stokis_db_bimbashop', 'like', "%{$search}%")
                  ->orWhere('nama_mitra', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('no_hp', 'like', "%{$search}%")
                  ->orWhere('ops_stokist', 'like', "%{$search}%");
            });
        })
        ->orderBy('no_cab')
        ->paginate($perPage)
        ->appends(['search' => $search, 'per_page' => $perPage]);

    return view('stokis.index', compact('stokis', 'search', 'perPage'));
}

    public function import(Request $request)
{
    Excel::import(new StokisMitraImport, $request->file('file'));
    return redirect()->route('stokis.index')
                ->with('success', '✅ Data berhasil diimport!');

}
}