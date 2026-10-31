<?php

namespace App\Modules\Picking\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\RealisasiLogistikAktif;
use App\Modules\Order\Models\RealisasiLogistikPasif;
use App\Modules\Picking\Http\Controllers\Concerns\MenungguPrintRA;
use App\Modules\Picking\Http\Controllers\Concerns\MenungguBarang;
use App\Modules\Picking\Services\KetersediaanBarangService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Persiapan Logistik Aktif & Logistik Pasif.
 *
 * Data dibaca langsung dari Realisasi (RA) di Penjualan dan tampil begitu masuk
 * Rekap Aktual, tetapi TERKUNCI sampai RA-nya di-print (printed_at terisi).
 * Alur tampilan & aksi sama dengan Persiapan Jakarta Aktif: Cek (waktu terima) -> Status -> PIC.
 */
class PickingLogistikController extends Controller
{
    use MenungguPrintRA, MenungguBarang;

    private const CONFIG = [
        'aktif' => [
            'model' => RealisasiLogistikAktif::class,
            'judul' => 'Picking List - Logistik Aktif',
            'sub'   => 'Daftar Persiapan Logistik Aktif (terkunci sampai RA Logistik Aktif di-print)',
        ],
        'pasif' => [
            'model' => RealisasiLogistikPasif::class,
            'judul' => 'Picking List - Logistik Pasif',
            'sub'   => 'Daftar Persiapan Logistik Pasif (terkunci sampai RA Logistik Pasif di-print)',
        ],
    ];

    public function aktif(Request $request)
    {
        return $this->daftar($request, 'aktif');
    }

    public function pasif(Request $request)
    {
        return $this->daftar($request, 'pasif');
    }

    private function daftar(Request $request, string $jenis)
    {
        $cfg   = self::CONFIG[$jenis];
        $model = $cfg['model'];

        // Semua RA tampil; yang belum di-print terkunci (ra_terkunci)
        $query = $model::query();

        if ($request->filled('kategori')) {
            $query->where('kategori_order', $request->kategori);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('no_pl', 'like', "%{$search}%")
                  ->orWhere('nama_unit', 'like', "%{$search}%")
                  ->orWhere('nama_barang', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nama_unit')) {
            $query->where('nama_unit', 'like', '%' . $request->nama_unit . '%');
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tgl_turun_pl', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('tgl_turun_pl', '<=', $request->end_date);
        }

        $data = $query->orderByDesc('printed_at')
                      ->paginate(20)
                      ->appends($request->query());

        // Samakan nama field dengan Persiapan Jakarta Aktif agar view bisa dipakai bersama
        $ketersediaan = app(KetersediaanBarangService::class);

        $data->setCollection($data->getCollection()->map(function ($r) use ($ketersediaan) {
            return (object) [
                'id'                       => $r->id,
                'no_pl'                    => $r->no_pl,
                'nama_unit'                => $r->nama_unit,
                'billing_last_name'        => $r->billing_last_name,
                'billing_company'          => $r->billing_company,
                'pesanan'                  => $r->nama_barang ?: $r->kategori_order,
                'payment_date'             => $r->tgl_bayar,
                'waktu_estimasi_persiapan' => $r->tgl_estimasi,
                'ekspedisi'                => $r->pengiriman,
                'service_pengiriman'       => $r->service_pengiriman,
                'tgl_terima'               => $r->tgl_terima,
                'status_persiapan'         => $r->status_persiapan,
                'tgl_picking'              => $r->tgl_picking,
                'pic'                      => $r->pic,
                'nama_stokis'              => $r->nama_stokis,
                'penyebut'                 => $r->penyebut,
                'pengambil'                => $r->pengambil,
                'ket'                      => $r->ket,
                'ra_terkunci'              => $this->raBelumPrint($r),
                'barang_belum_datang'      => $ketersediaan->belumDatang($r),
            ];
        }));

        return view('picking::picking.persiapan', [
            'data'            => $data,
            'judul'           => $cfg['judul'],
            'sub'             => $cfg['sub'],
            'routeIndex'      => 'picking.logistik.' . $jenis,
            'routeChecklist'  => 'picking.logistik.' . $jenis . '.checklist',
            'routePic'        => 'picking.logistik.' . $jenis . '.pic',
            'routeStatus'     => 'picking.logistik.' . $jenis . '.status',
            'relItems'        => null,
            'extraCols'       => true,
            'showEdit'        => false,
            'emptyText'       => 'Belum ada Persiapan Logistik. Data masuk setelah order diproses ke Rekap Aktual.',
        ]);
    }

    // ==================== UPDATE ====================

    private function cari(Request $request, string $jenis)
    {
        $model = self::CONFIG[$jenis]['model'];

        return $model::findOrFail($request->id);
    }

    public function updateChecklist(Request $request, string $jenis)
    {
        try {
            $row     = $this->cari($request, $jenis);

            if ($this->raBelumPrint($row)) {
                return $this->responTerkunci();
            }

            $checked = $request->boolean('checked');

            if ($checked && ($belum = $this->barangBelumDatang($row))) {
                return $this->responBarangBelumDatang($belum);
            }

            if ($checked) {
                $row->tgl_terima  = now();
                $row->tgl_picking = now()->toDateString();
            } else {
                $row->tgl_terima        = null;
                $row->tgl_picking       = null;
                $row->status_persiapan  = 'Belum';
            }

            $row->save();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function updatePic(Request $request, string $jenis)
    {
        $request->validate([
            'id'  => 'required|integer',
            'pic' => 'nullable|in:Asep,Arif,Rama,Riky',
        ]);

        $row = $this->cari($request, $jenis);

        if ($this->raBelumPrint($row)) {
            return $this->responTerkunci();
        }

        if ($belum = $this->barangBelumDatang($row)) {
            return $this->responBarangBelumDatang($belum);
        }

        $row->pic = $request->pic;
        $row->save();

        return response()->json(['success' => true]);
    }

    public function updateStatus(Request $request, string $jenis)
    {
        $request->validate([
            'id'               => 'required|integer',
            'status_persiapan' => 'required|in:Belum,Sudah',
        ]);

        $row = $this->cari($request, $jenis);

        if ($this->raBelumPrint($row)) {
            return $this->responTerkunci();
        }

        if ($request->status_persiapan === 'Sudah' && ($belum = $this->barangBelumDatang($row))) {
            return $this->responBarangBelumDatang($belum);
        }

        $row->status_persiapan = $request->status_persiapan;

        if (! $row->tgl_picking) {
            $row->tgl_picking = now()->toDateString();
        }

        $row->save();

        return response()->json(['success' => true]);
    }
}
