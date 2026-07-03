<?php

namespace App\Http\Controllers;

use App\Models\Packing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PackingController extends Controller
{
    public function index()
    {
        return view('packing.index');
    }

    public function jakartaAktif()
    {
        $data = Packing::with('picking')   // atau with('qcOutgoing')
            ->orderBy('tgl_estimasi')
            ->paginate(20);

        return view('packing.jakarta-aktif', compact('data'));
    }

    /**
     * Update Data Packing (Inline Form)
     */
    public function update(Request $request, $id)
{
    $packing = Packing::findOrFail($id);

    // ============================
    // Jika sudah selesai, tidak boleh diedit lagi
    // ============================
    if ($packing->status_packing === 'selesai') {
        return back()->with('error', 'Data packing sudah selesai dan tidak dapat diedit lagi.');
    }

    // ============================
    // Validasi
    // ============================
    $validated = $request->validate([
        'tgl_packing'        => 'nullable|date',
        'status_packing'     => 'required|in:belum,proses,pending,selesai',
        'nama_packer'        => 'nullable|string|max:100',
        'berat_aktual'       => 'nullable|integer|min:0',
        'koli'               => 'nullable|integer|min:1',
        'keterangan_packing' => 'nullable|string|max:255',
    ]);

    // ============================
    // Update Data
    // ============================
    $packing->fill([
        'tgl_packing'        => $validated['tgl_packing'],
        'status_packing'     => $validated['status_packing'],
        'nama_packer'        => $validated['nama_packer'] ?? null,
        'berat_aktual'       => $validated['berat_aktual'] ?? null,
        'koli'               => $validated['koli'] ?? null,
        'keterangan_packing' => $validated['keterangan_packing'] ?? null,
        'packing_by'         => Auth::id(),
        'packing_at'         => now(),
    ]);

    $packing->save();

    return redirect()->back()->with('success', '✅ Data Packing berhasil disimpan.');
}
}