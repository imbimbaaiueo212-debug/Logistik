<?php

namespace App\Http\Controllers;

use App\Models\StokisMitra;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StokisPasifController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->get('search');
        $perPage = (int) $request->get('per_page', 50);

        $stokis = StokisMitra::query()
            ->where('status', 'pasif')
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

        return view('stokis_pasif.index', compact('stokis', 'search', 'perPage'));
    }

    public function create()
    {
        return view('stokis_pasif.create', ['stokis' => new StokisMitra()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $stokis = new StokisMitra();
        $stokis->fill(Arr::except($validated, ['tanggal_pasif']));
        $stokis->status = 'pasif';
        $stokis->tanggal_pasif = $validated['tanggal_pasif'] ?? now();
        $stokis->save();

        return redirect()->route('stokis-pasif.index')
            ->with('success', 'Stokis pasif ' . $stokis->no_cab . ' berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $stokis = StokisMitra::where('status', 'pasif')->findOrFail($id);

        return view('stokis_pasif.edit', compact('stokis'));
    }

    public function update(Request $request, $id)
    {
        $stokis = StokisMitra::where('status', 'pasif')->findOrFail($id);

        $validated = $request->validate($this->rules($stokis->id));

        $stokis->fill(Arr::except($validated, ['tanggal_pasif']));
        $stokis->tanggal_pasif = $validated['tanggal_pasif'] ?? $stokis->tanggal_pasif;
        $stokis->save();

        return redirect()->route('stokis-pasif.index')
            ->with('success', 'Data stokis pasif berhasil diperbarui.');
    }

    public function aktifkan($id)
    {
        $stokis = StokisMitra::where('status', 'pasif')->findOrFail($id);

        $stokis->status = 'aktif';
        $stokis->tanggal_pasif = null;
        $stokis->save();

        return redirect()->route('stokis-pasif.index')
            ->with('success', 'Stokis ' . $stokis->no_cab . ' diaktifkan kembali.');
    }

    public function destroy($id)
    {
        $stokis = StokisMitra::where('status', 'pasif')->findOrFail($id);
        $stokis->delete();

        return redirect()->route('stokis-pasif.index')
            ->with('success', 'Data stokis pasif berhasil dihapus.');
    }

    /** Aturan validasi (sama dengan Stokis Aktif, ditambah tanggal_pasif) */
    private function rules($ignoreId = null): array
    {
        return [
            'no_cab' => 'required|string|max:20|unique:stokis_mitra,no_cab' . ($ignoreId ? ',' . $ignoreId : ''),
            'nama_stokis_db_kemitraan' => 'nullable|string|max:255',
            'nama_stokis_db_bimbashop' => 'nullable|string|max:255',
            'no_induk_mitra' => 'nullable|string|max:255',
            'nama_mitra' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'no_hp' => 'nullable|string|max:30',
            'related_form_pembukaan_unit_aktif' => 'nullable|string',
            'related_formulir_kerjasama_english' => 'nullable|string',
            'db_kemitraan_db_bimbashop' => 'nullable|string|max:255',
            'related_unit_bimba_aiueo' => 'nullable|string',
            'related_formulir_kerjasama_mk_mm' => 'nullable|string',
            'related_pengajuan_perubahan' => 'nullable|string',
            'item_sku' => 'nullable|string',
            'ops_stokist' => 'nullable|string|max:255',
            'tanggal_pasif' => 'nullable|date',
        ];
    }
}