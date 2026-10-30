<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kalender\Models\KalenderDlcPeriode;
use App\Modules\Kalender\Models\KalenderDlcPesanan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class KalenderDlcController extends Controller
{


// =========================================================
// DLC - Index
// =========================================================
public function dlcIndex()
{
    $periodes = KalenderDlcPeriode::withCount('pesanan')
        ->withSum('pesanan', 'qty')
        ->latest()
        ->paginate(20);

    return view('kalender::dlc.index', compact('periodes'));
}

// =========================================================
// DLC - Form Create
// =========================================================
public function dlcCreate()
{
    return view('kalender::dlc.create');
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
        $periode = KalenderDlcPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => $request->judul ?: 'Kalender ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        foreach ($items as $item) {
            KalenderDlcPesanan::create([
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
            ->route('kalender.dlc.show', $periode->id)
            ->with('success', 'Data DLC berhasil disimpan!');
    } catch (\Throwable $e) {
        DB::rollBack();
        return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}

public function dlcEdit($id)
{
    $periode = KalenderDlcPeriode::with('pesanan')->findOrFail($id);
    return view('kalender::dlc.edit', compact('periode'));
}

public function dlcUpdate(Request $request, $id)
{
    $periode = KalenderDlcPeriode::findOrFail($id);

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
                $pesanan = KalenderDlcPesanan::where('dlc_periode_id', $periode->id)
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
                $new = KalenderDlcPesanan::create([
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
    KalenderDlcPesanan::where('dlc_periode_id', $periode->id)
        ->whereNotIn('id', $existingIds)
        ->delete();

    return redirect()
        ->route('kalender.dlc.index')
        ->with('success', 'Data DLC berhasil diupdate.');
}

// =========================================================
// DLC - Show Detail
// =========================================================
public function dlcShow($id)
{
    $periode = KalenderDlcPeriode::with('pesanan')->findOrFail($id);
    $total   = $periode->pesanan->sum('qty');

    return view('kalender::dlc.show', compact('periode', 'total'));
}

// =========================================================
// DLC - Hapus
// =========================================================
public function dlcDestroy($id)
{
    $periode = KalenderDlcPeriode::findOrFail($id);
    $periode->delete(); // cascade akan hapus pesanan juga

    return redirect()
        ->route('kalender.dlc.index')
        ->with('success', 'Data DLC berhasil dihapus');
}
public function dlcUpdateNoPs(Request $request, $id)
{
    $request->validate([
        'no_ps' => 'nullable|string|max:100',
    ]);

    $periode = KalenderDlcPeriode::findOrFail($id);
    $periode->update([
        'no_ps' => $request->no_ps,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'No PS berhasil diupdate',
        'no_ps'   => $periode->no_ps,
    ]);
}

// =========================================================
// DLC - Update Qty satu unit (modal di halaman detail)
// =========================================================
public function dlcUpdateQty(Request $request, $id)
{
    $data = $request->validate([
        'qty' => 'required|integer|min:0',
    ]);

    $pesanan = KalenderDlcPesanan::findOrFail($id);
    $pesanan->update(['qty' => (int) $data['qty']]);

    return response()->json([
        'success' => true,
        'message' => 'Qty berhasil diupdate',
        'qty'     => $pesanan->qty,
    ]);
}
}
