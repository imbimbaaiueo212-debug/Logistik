<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KalenderPasifManualController extends Controller
{


// =========================================================
// PASIF MANUAL - CREATE & STORE
// =========================================================

public function pasifManualCreate()
{
    return view('kalender::pasif.manual-create');
}

public function pasifManualStore(Request $request)
{
    $items = collect($request->input('items', []))
        ->filter(function ($item) {
            return !empty(trim($item['nama_unit'] ?? '')) && (int)($item['jumlah'] ?? 0) > 0;
        })
        ->values()
        ->toArray();

    $request->merge(['items' => $items]);

    $request->validate([
        'edisi'                      => 'required|string|max:20',
        'judul'                      => 'nullable|string|max:150',
        'periode'                    => 'nullable|string|max:50',
        'bulan'                      => 'nullable|string|max:20',
        'tahun'                      => 'nullable|string|max:10',
        'no_ps'                      => 'nullable|string|max:100',
        'items'                      => 'required|array|min:1',
        'items.*.nama_unit'          => 'required|string|max:150',
        'items.*.jumlah'             => 'required|integer|min:1',
        'items.*.id_pesan'           => 'nullable|string|max:50',
        'items.*.kode_pesan'         => 'nullable|string|max:50',
        'items.*.tgl_pesan'          => 'nullable|date',
        'items.*.minggu'             => 'nullable|string|max:20',
        'items.*.label'              => 'nullable|string|max:30',
        'items.*.pesanan'            => 'nullable|string|max:150',
        'items.*.note'               => 'nullable|string',
        'items.*.keterangan'         => 'nullable|string|max:255',
        'items.*.no_cab'             => 'nullable|string|max:50',
        'items.*.alamat'             => 'nullable|string',
        'items.*.telepon'            => 'nullable|string|max:30',
        'items.*.ekspedisi'          => 'nullable|string|max:100',
        'items.*.service_pengiriman' => 'nullable|string|max:50',
    ], [
        'items.required'             => 'Minimal harus ada 1 unit yang diisi.',
        'items.min'                  => 'Minimal harus ada 1 unit yang diisi.',
        'items.*.nama_unit.required' => 'Nama unit wajib diisi.',
        'items.*.jumlah.required'    => 'Jumlah wajib diisi.',
        'items.*.jumlah.min'         => 'Jumlah minimal 1.',
    ]);

    DB::beginTransaction();
    try {
        $periode = \App\Modules\Kalender\Models\KalenderPasifManualPeriode::create([
            'edisi'      => strtoupper(trim($request->edisi)),
            'judul'      => $request->judul ?: 'Kalender ' . strtoupper($request->edisi),
            'periode'    => $request->periode,
            'bulan'      => $request->bulan,
            'tahun'      => $request->tahun,
            'no_ps'      => $request->no_ps,
            'grup'       => 'F',
            'status'     => 'aktif',
            'created_by' => Auth::id(),
        ]);

        foreach ($items as $index => $item) {
            \App\Modules\Kalender\Models\KalenderPasifManualTransaksi::create([
                'pasif_manual_periode_id' => $periode->id,
                'no'                      => $index + 1,
                'id_pesan'                => $item['id_pesan'] ?? null,
                'kode_pesan'              => $item['kode_pesan'] ?? null,
                'tgl_pesan'               => $item['tgl_pesan'] ?? null,
                'minggu'                  => $item['minggu'] ?? null,
                'nama_unit'               => trim($item['nama_unit']),
                'label'                   => $item['label'] ?? (strtoupper($request->edisi)),
                'jumlah'                  => (int) $item['jumlah'],
                'pesanan'                 => $item['pesanan'] ?? ('Kalender ' . strtoupper($request->edisi)),
                'note'                    => $item['note'] ?? null,
                'keterangan'              => $item['keterangan'] ?? null,
                'no_cab'                  => $item['no_cab'] ?? null,
                'alamat'                  => $item['alamat'] ?? null,
                'telepon'                 => $item['telepon'] ?? null,
                'ekspedisi'               => $item['ekspedisi'] ?? null,
                'service_pengiriman'      => $item['service_pengiriman'] ?? null,
            ]);
        }

        DB::commit();

        return redirect()
            ->route('kalender.pasif.manual.show', $periode->id)
            ->with('success', 'Data Pasif Manual berhasil disimpan! Total unit: ' . count($items));
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Pasif Manual Store Error: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
    }
}

public function pasifManualShow($id)
{
    $periode = \App\Modules\Kalender\Models\KalenderPasifManualPeriode::with('transaksis')->findOrFail($id);
    $total   = $periode->transaksis->sum('jumlah');

    return view('kalender::pasif.manual-show', compact('periode', 'total'));
}

public function pasifManualIndex()
{
    $periodes = \App\Modules\Kalender\Models\KalenderPasifManualPeriode::withCount('transaksis')
        ->withSum('transaksis', 'jumlah')
        ->latest()
        ->paginate(20);

    return view('kalender::pasif.manual-index', compact('periodes'));
}

public function pasifManualDestroy($id)
{
    $periode = \App\Modules\Kalender\Models\KalenderPasifManualPeriode::findOrFail($id);
    $periode->delete();

    return redirect()
        ->route('kalender.pasif.manual.index')
        ->with('success', 'Data Pasif Manual berhasil dihapus');
}
public function pasifManualEdit($id)
{
    $periode = \App\Modules\Kalender\Models\KalenderPasifManualPeriode::with('transaksis')->findOrFail($id);

    return view('kalender::pasif.manual-edit', compact('periode'));
}

public function pasifManualUpdate(Request $request, $id)
{
    $periode = \App\Modules\Kalender\Models\KalenderPasifManualPeriode::findOrFail($id);

    $items = collect($request->input('items', []))
        ->filter(function ($item) {
            return !empty(trim($item['nama_unit'] ?? '')) && (int)($item['jumlah'] ?? 0) > 0;
        })
        ->values()
        ->toArray();

    $request->merge(['items' => $items]);

    $request->validate([
        'edisi'                      => 'required|string|max:20',
        'judul'                      => 'nullable|string|max:150',
        'periode'                    => 'nullable|string|max:50',
        'bulan'                      => 'nullable|string|max:20',
        'tahun'                      => 'nullable|string|max:10',
        'no_ps'                      => 'nullable|string|max:100',
        'status'                     => 'nullable|in:aktif,nonaktif',
        'items'                      => 'required|array|min:1',
        'items.*.nama_unit'          => 'required|string|max:150',
        'items.*.jumlah'             => 'required|integer|min:1',
        'items.*.id_pesan'           => 'nullable|string|max:50',
        'items.*.kode_pesan'         => 'nullable|string|max:50',
        'items.*.tgl_pesan'          => 'nullable|date',
        'items.*.minggu'             => 'nullable|string|max:20',
        'items.*.label'              => 'nullable|string|max:30',
        'items.*.pesanan'            => 'nullable|string|max:150',
        'items.*.note'               => 'nullable|string',
        'items.*.keterangan'         => 'nullable|string|max:255',
        'items.*.no_cab'             => 'nullable|string|max:50',
        'items.*.alamat'             => 'nullable|string',
        'items.*.telepon'            => 'nullable|string|max:30',
        'items.*.ekspedisi'          => 'nullable|string|max:100',
        'items.*.service_pengiriman' => 'nullable|string|max:50',
    ], [
        'items.required'             => 'Minimal harus ada 1 unit yang diisi.',
        'items.min'                  => 'Minimal harus ada 1 unit yang diisi.',
        'items.*.nama_unit.required' => 'Nama unit wajib diisi.',
        'items.*.jumlah.required'    => 'Jumlah wajib diisi.',
        'items.*.jumlah.min'         => 'Jumlah minimal 1.',
    ]);

    DB::beginTransaction();
    try {
        $periode->update([
            'edisi'   => strtoupper(trim($request->edisi)),
            'judul'   => $request->judul ?: 'Kalender ' . strtoupper($request->edisi),
            'periode' => $request->periode,
            'bulan'   => $request->bulan,
            'tahun'   => $request->tahun,
            'no_ps'   => $request->no_ps,
            'status'  => $request->status ?? $periode->status,
        ]);

        // Hapus transaksi lama, buat ulang
        $periode->transaksis()->delete();

        foreach ($items as $index => $item) {
            \App\Modules\Kalender\Models\KalenderPasifManualTransaksi::create([
                'pasif_manual_periode_id' => $periode->id,
                'no'                      => $index + 1,
                'id_pesan'                => $item['id_pesan'] ?? null,
                'kode_pesan'              => $item['kode_pesan'] ?? null,
                'tgl_pesan'               => $item['tgl_pesan'] ?? null,
                'minggu'                  => $item['minggu'] ?? null,
                'nama_unit'               => trim($item['nama_unit']),
                'label'                   => $item['label'] ?? (strtoupper($request->edisi)),
                'jumlah'                  => (int) $item['jumlah'],
                'pesanan'                 => $item['pesanan'] ?? ('Kalender ' . strtoupper($request->edisi)),
                'note'                    => $item['note'] ?? null,
                'keterangan'              => $item['keterangan'] ?? null,
                'no_cab'                  => $item['no_cab'] ?? null,
                'alamat'                  => $item['alamat'] ?? null,
                'telepon'                 => $item['telepon'] ?? null,
                'ekspedisi'               => $item['ekspedisi'] ?? null,
                'service_pengiriman'      => $item['service_pengiriman'] ?? null,
            ]);
        }

        DB::commit();

        return redirect()
            ->route('kalender.pasif.manual.show', $periode->id)
            ->with('success', 'Data Pasif Manual berhasil diupdate! Total unit: ' . count($items));
    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('Pasif Manual Update Error: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Gagal update: ' . $e->getMessage());
    }
}
}
