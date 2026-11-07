<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Services\ShopMatcher;
use App\Modules\Kalender\Models\KalenderSpareManual;
use App\Modules\Kalender\Support\KodeKalender;
use App\Modules\Master\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Kalender > Unit Pasif > Spare Pasif 3%.
 * Berdiri sendiri: dibuat manual, tidak ikut import Kalender dan tidak dihitung otomatis.
 * ID Order dikosongkan (nanti diambil dari biMBA Shop), ID Manual dibuat sendiri / otomatis.
 */
class KalenderSparePasifController extends Controller
{
    public function index(Request $request)
    {
        $daftarEdisi = KalenderSpareManual::whereNotNull('edisi')->distinct()->orderBy('edisi')->pluck('edisi');

        $query = KalenderSpareManual::query();

        if ($request->filled('edisi')) {
            $query->where('edisi', KodeKalender::normalize($request->edisi));
        }
        if ($request->filled('q')) {
            $k = '%' . trim($request->q) . '%';
            $query->where(fn ($w) => $w->where('nama', 'like', $k)->orWhere('id_manual', 'like', $k)->orWhere('order_id', 'like', $k)->orWhere('sku', 'like', $k));
        }

        $totalQty = (int) (clone $query)->sum('qty');
        $totalTrx = (clone $query)->count();
        $rows     = $query->orderByDesc('tgl_order')->orderByDesc('id')->paginate(50)->withQueryString();

        return view('kalender::pasif.spare', [
            'rows'        => $rows,
            'daftarEdisi' => $daftarEdisi,
            'totalQty'    => $totalQty,
            'totalTrx'    => $totalTrx,
            'idUsulan'    => $this->idBerikut(),
            'opsiSku'     => $this->opsiSku(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $data['id_manual']  = $data['id_manual'] ?: $this->idBerikut();
        $data['created_by'] = Auth::id();

        KalenderSpareManual::create($data);

        return redirect()->route('kalender.pasif.spare')->with('success', "Spare Pasif 3% {$data['id_manual']} ditambahkan.");
    }

    public function edit($id)
    {
        return view('kalender::pasif.spare-edit', [
            'item'    => KalenderSpareManual::findOrFail($id),
            'opsiSku' => $this->opsiSku(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $item = KalenderSpareManual::findOrFail($id);
        $data = $this->validasi($request, $item->id);

        $data['id_manual'] = $data['id_manual'] ?: $item->id_manual;
        $item->update($data);

        return redirect()->route('kalender.pasif.spare')->with('success', "{$item->id_manual} diperbarui.");
    }

    public function destroy($id)
    {
        $item = KalenderSpareManual::findOrFail($id);
        $kode = $item->id_manual;
        $item->delete();

        return redirect()->route('kalender.pasif.spare')->with('success', "{$kode} dihapus.");
    }

    // ------------------------------------------------------------------ helper

    private function validasi(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'id_manual' => ['nullable', 'string', 'max:50', 'unique:kalender_spare_manual,id_manual' . ($ignoreId ? ',' . $ignoreId : '')],
            'tgl_order' => ['required', 'date'],
            'nama'      => ['required', 'string', 'max:255'],
            'sku'       => ['required', 'string', 'max:100'],
            'qty'       => ['required', 'integer', 'min:1'],
            'tahap'     => ['required', 'integer', 'min:1', 'max:20'],
        ], [
            'id_manual.unique' => 'ID Manual sudah dipakai.',
        ]);

        // Edisi diturunkan dari SKU (KLD27 => KLD27, atau tahun 2027 => KLD27) agar masuk ke Report Angka Cetak
        $tahun = ShopMatcher::tahunFrom($data['sku']);
        $edisi = $tahun ? KodeKalender::dariTahun($tahun) : null;

        if (! $edisi) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sku' => 'SKU harus memuat edisi kalender, contoh KLD27.',
            ]);
        }

        $data['sku']      = strtoupper(trim($data['sku']));
        $data['id_manual'] = isset($data['id_manual']) ? strtoupper(trim($data['id_manual'])) : null;
        $data['edisi']    = $edisi;

        return $data;
    }

    /** ID Manual berikutnya: KSP0001, KSP0002, ... */
    private function idBerikut(): string
    {
        $maks = KalenderSpareManual::where('id_manual', 'like', 'KSP%')
            ->pluck('id_manual')
            ->map(fn ($v) => (int) preg_replace('/\D/', '', $v))
            ->max() ?? 0;

        return 'KSP' . str_pad((string) ($maks + 1), 4, '0', STR_PAD_LEFT);
    }

    /** Saran SKU kalender dari master Produk (tetap boleh mengetik sendiri). */
    private function opsiSku(): array
    {
        return Product::query()
            ->where(function ($q) {
                $q->where('sku', 'like', 'KLD%')->orWhere('sku', 'like', 'KAL%')
                  ->orWhere('label', 'like', 'KLD%')->orWhere('name', 'like', '%kalender%');
            })
            ->get(['sku', 'label', 'name'])
            ->map(fn ($p) => ['sku' => $p->label ?: $p->sku, 'name' => $p->name])
            ->unique('sku')
            ->values()
            ->all();
    }
}
