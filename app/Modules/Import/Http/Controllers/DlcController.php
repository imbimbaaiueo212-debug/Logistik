<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\DlcPeriode;
use App\Modules\Import\Models\DlcPesanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DlcController extends Controller
{


// =========================================================
// DLC - Index
// =========================================================
public function dlcIndex()
{
    $periodes = DlcPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->latest()
        ->paginate(20);

    return view('import::import.dlc.index', compact('periodes'));
}

// =========================================================
// DLC - Form Create
// =========================================================
public function dlcCreate()
{
    return view('import::import.dlc.create');
}

// =========================================================
// DLC - Store
// =========================================================
public function dlcStore(Request $request)
{
    // Ambil hanya item yang terisi
    $items = collect($request->input('items', []))
        ->filter(function ($item) {
            return !empty(trim($item['nama_unit'] ?? '')) && (int)($item['qty'] ?? 0) > 0;
        })
        ->values()
        ->toArray();

    // Validasi ulang setelah difilter
    $request->merge(['items' => $items]);

    $request->validate([
        'edisi'             => 'required|string|max:20',
        'judul'             => 'nullable|string|max:100',
        'periode'           => 'required|string|max:50',
        'bulan'             => 'nullable|string|max:20',
        'tahun'             => 'nullable|integer|min:2020|max:2030',
        'items'             => 'required|array|min:1',
        'items.*.nama_unit' => 'required|string|max:150',
        'items.*.qty'       => 'required|integer|min:1',
    ], [
        'items.required'             => 'Minimal harus ada 1 unit yang diisi.',
        'items.min'                  => 'Minimal harus ada 1 unit yang diisi.',
        'items.*.nama_unit.required' => 'Nama unit wajib diisi.',
        'items.*.qty.required'       => 'Qty wajib diisi.',
        'items.*.qty.min'            => 'Qty minimal 1.',
    ]);

    DB::beginTransaction();
    try {
        $periode = DlcPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => $request->judul ?: 'Majalah ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        foreach ($items as $item) {
            DlcPesanan::create([
                'dlc_periode_id' => $periode->id,
                'nama_unit'      => trim($item['nama_unit']),
                'qty'            => (int) $item['qty'],
                'no_cab'         => $item['no_cab'] ?? null,
                'alamat'         => $item['alamat'] ?? null,
                'telepon'        => $item['telepon'] ?? null,
                'keterangan'     => $item['keterangan'] ?? null,
            ]);
        }

        DB::commit();

        return redirect()
            ->route('import.dlc.show', $periode->id)
            ->with('success', '✅ Data DLC berhasil disimpan!');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}

public function dlcEdit($id)
{
    $periode = DlcPeriode::with('pesanan')->findOrFail($id);
    return view('import::import.dlc.edit', compact('periode'));
}

public function dlcUpdate(Request $request, $id)
{
    $periode = DlcPeriode::findOrFail($id);

    $validated = $request->validate([
        'edisi'     => 'required|string|max:50',
        'judul'     => 'nullable|string|max:255',
        'periode'   => 'nullable|string|max:100',
        'bulan'     => 'nullable|string|max:50',
        'tahun'     => 'nullable|string|max:10',
        'status'    => 'required|in:aktif,nonaktif',
        'no_ps'     => 'nullable|string|max:100',

        // Unit
        'units'             => 'nullable|array',
        'units.*.id'        => 'nullable|integer',
        'units.*.nama_unit' => 'required|string|max:255',
        'units.*.no_cab'    => 'nullable|string|max:50',
        'units.*.qty'       => 'required|integer|min:0',
        'units.*.alamat'    => 'nullable|string',
        'units.*.telepon'   => 'nullable|string|max:30',
        'units.*.keterangan'=> 'nullable|string',
    ]);

    // Update header
    $periode->update([
        'edisi'   => $validated['edisi'],
        'judul'   => $validated['judul'] ?? null,
        'periode' => $validated['periode'] ?? null,
        'bulan'   => $validated['bulan'] ?? null,
        'tahun'   => $validated['tahun'] ?? null,
        'status'  => $validated['status'],
        'no_ps'   => $validated['no_ps'] ?? null,
    ]);

    // Update / Create / Delete units
    $existingIds = [];

    if (!empty($validated['units'])) {
        foreach ($validated['units'] as $unitData) {
            if (!empty($unitData['id'])) {
                // Update existing
                $pesanan = DlcPesanan::where('dlc_periode_id', $periode->id)
                    ->where('id', $unitData['id'])
                    ->first();

                if ($pesanan) {
                    $pesanan->update([
                        'nama_unit'  => $unitData['nama_unit'],
                        'no_cab'     => $unitData['no_cab'] ?? null,
                        'qty'        => $unitData['qty'],
                        'alamat'     => $unitData['alamat'] ?? null,
                        'telepon'    => $unitData['telepon'] ?? null,
                        'keterangan' => $unitData['keterangan'] ?? null,
                    ]);
                    $existingIds[] = $pesanan->id;
                }
            } else {
                // Create new
                $new = DlcPesanan::create([
                    'dlc_periode_id' => $periode->id,
                    'nama_unit'      => $unitData['nama_unit'],
                    'no_cab'         => $unitData['no_cab'] ?? null,
                    'qty'            => $unitData['qty'],
                    'alamat'         => $unitData['alamat'] ?? null,
                    'telepon'        => $unitData['telepon'] ?? null,
                    'keterangan'     => $unitData['keterangan'] ?? null,
                ]);
                $existingIds[] = $new->id;
            }
        }
    }

    // Hapus unit yang tidak ada di form
    DlcPesanan::where('dlc_periode_id', $periode->id)
        ->whereNotIn('id', $existingIds)
        ->delete();

    return redirect()
        ->route('import.dlc.index')
        ->with('success', 'Data DLC berhasil diupdate.');
}

// =========================================================
// DLC - Show Detail
// =========================================================
public function dlcShow($id)
{
    $periode = DlcPeriode::with('pesanan')->findOrFail($id);
    $total   = $periode->pesanan->sum('qty');
    $rekap   = app(\App\Modules\Import\Services\DlcRekapMajalah::class)->hitung($periode);

    return view('import::import.dlc.show', compact('periode', 'total', 'rekap'));
}

// =========================================================
// DLC - Hapus
// =========================================================
public function dlcDestroy($id)
{
    $periode = DlcPeriode::findOrFail($id);
    $periode->delete(); // cascade akan hapus pesanan juga

    return redirect()
        ->route('import.dlc.index')
        ->with('success', '✅ Data DLC berhasil dihapus');
}
public function dlcUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = DlcPeriode::findOrFail($id);
    $periode->update([
        'no_ps' => $request->no_ps,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}
}
