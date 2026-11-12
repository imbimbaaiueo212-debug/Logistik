<?php

namespace App\Modules\PengeluaranLogistik\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Master\Models\Product;
use App\Modules\PengeluaranLogistik\Models\TransferGudangManual;
use Illuminate\Http\Request;

/**
 * Transfer gudang: input manual, hanya qty (tanpa nominal uang).
 * Datanya ikut dihitung di Report Pengeluaran Logistik.
 */
class TransferGudangManualController extends Controller
{
    public function index(Request $request)
    {
        $q = TransferGudangManual::query();

        if ($request->filled('dari')) {
            $q->whereDate('tanggal', '>=', $request->get('dari'));
        }
        if ($request->filled('sampai')) {
            $q->whereDate('tanggal', '<=', $request->get('sampai'));
        }
        if ($request->filled('tujuan')) {
            $q->where('tujuan', 'like', '%' . $request->get('tujuan') . '%');
        }

        $data = $q->orderByDesc('tanggal')->orderByDesc('id')->paginate(50)->appends($request->query());

        $produk = Product::orderBy('name')->get(['id', 'name', 'label', 'kategori']);

        $tujuanLama = TransferGudangManual::query()->distinct()->orderBy('tujuan')->pluck('tujuan');

        return view('pengeluaran-logistik::transfer', compact('data', 'produk', 'tujuanLama'));
    }

    public function store(Request $request)
    {
        $v = $request->validate([
            'tanggal'    => ['required', 'date'],
            'dari'       => ['nullable', 'string', 'max:100'],
            'tujuan'     => ['required', 'string', 'max:150'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty'        => ['required', 'integer', 'min:1'],
            'catatan'    => ['nullable', 'string', 'max:1000'],
        ]);

        $p = Product::findOrFail($v['product_id']);

        TransferGudangManual::create([
            'tanggal'     => $v['tanggal'],
            'dari'        => trim((string) ($v['dari'] ?? '')) ?: 'Logistik',
            'tujuan'      => trim($v['tujuan']),
            'product_id'  => $p->id,
            'nama_barang' => $p->name,
            'sku'         => $p->label,
            'kategori'    => trim((string) $p->kategori) ?: null,
            'qty'         => $v['qty'],
            'catatan'     => $v['catatan'] ?? null,
            'dibuat_oleh' => $request->user()?->id,
        ]);

        return redirect()->route('pengeluaran-logistik.transfer.index')
            ->with('success', 'Transfer gudang disimpan.');
    }

    public function destroy($id)
    {
        TransferGudangManual::findOrFail($id)->delete();

        return redirect()->route('pengeluaran-logistik.transfer.index')
            ->with('success', 'Transfer gudang dihapus.');
    }
}
