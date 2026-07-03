<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Picking;
use App\Models\QcOutgoing;
use Illuminate\Support\Facades\Auth;

class QcOutgoingController extends Controller
{
    public function index()
    {
        return view('qc-outgoing.index');
    }

    public function jakartaAktif()
{
    $data = QcOutgoing::with(['picking'])   // pastikan relasi picking di-load
        ->orderByDesc('created_at')
        ->paginate(20);

    return view('qc-outgoing.jakarta-aktif', compact('data'));
}

    public function qcStore(Request $request)
{
    $validated = $request->validate([
        'picking_id' => 'required|exists:qc_outgoings,picking_id',
        'status_qc'  => 'required|in:Pending,Lolos,Reject,Revisi',
        'pic_qc'     => 'nullable|string',
        'keterangan' => 'nullable|string',
    ]);

    $qc = QcOutgoing::where('picking_id', $validated['picking_id'])
        ->firstOrFail();

    // Jika sudah lolos, tidak boleh diubah lagi
    if ($qc->status_qc === 'Lolos') {
        return back()->with('warning', 'Data QC yang sudah Lolos tidak dapat diubah.');
    }

    $update = [
        'status_qc'  => $validated['status_qc'],
        'keterangan' => $validated['keterangan'],
        'tgl_qc'     => now(),
    ];

    // PIC hanya boleh diisi sekali
    if (empty($qc->pic_qc) && !empty($validated['pic_qc'])) {

        $kodePic = explode(' - ', $validated['pic_qc'])[0];

        $update['pic_qc']  = $validated['pic_qc'];
        $update['kode_qc'] =  $kodePic;
    }

    $qc->update($update);

    return back()->with('success', 'QC berhasil disimpan.');
}
}