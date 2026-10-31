<?php

namespace App\Modules\Picking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Picking\Services\KetersediaanBarangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KetersediaanBarangController extends Controller
{
    public function index(KetersediaanBarangService $service)
    {
        return view('picking::ketersediaan.index', ['daftar' => $service->daftar()]);
    }

    public function store(Request $request, KetersediaanBarangService $service)
    {
        $data = $request->validate([
            'jenis' => 'required|in:Majalah,Kalender',
            'kode'  => 'required|string|max:20',
        ]);

        $kode = strtoupper(trim($data['kode']));
        $ok = $data['jenis'] === 'Majalah'
            ? preg_match('/^M\d{2,4}$/', $kode)
            : preg_match('/^20\d{2}$/', $kode);
        if (! $ok) {
            return back()->withErrors(['kode' => $data['jenis'] === 'Majalah' ? 'Format edisi Majalah: M160' : 'Format tahun Kalender: 2027'])->withInput();
        }

        if (\App\Modules\Picking\Models\KetersediaanBarang::where('jenis', $data['jenis'])->where('kode', $kode)->exists()) {
            return back()->with('success', "{$data['jenis']} {$kode} sudah terdaftar.");
        }

        $service->tandai($data['jenis'], $kode, 'belum_datang', Auth::id());

        return back()->with('success', "{$data['jenis']} {$kode} didaftarkan sebagai belum datang.");
    }

    public function update(Request $request, KetersediaanBarangService $service)
    {
        $data = $request->validate([
            'jenis'  => 'required|in:Majalah,Kalender',
            'kode'   => 'required|string|max:20',
            'status' => 'required|in:datang,belum_datang',
        ]);

        $service->tandai($data['jenis'], $data['kode'], $data['status'], Auth::id());

        $label = $data['jenis'] . ' ' . strtoupper($data['kode']);

        return back()->with('success', $data['status'] === 'datang'
            ? "{$label} ditandai sudah datang. Picking terkait sekarang bisa diproses."
            : "{$label} dikembalikan ke status belum datang.");
    }
}
