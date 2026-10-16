<?php

namespace App\Modules\Order\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Order\Models\LogistikAktif;
use App\Modules\Order\Models\RealisasiLogistikAktif;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Rekap Aktual Logistik (khusus order LG).
 * Sumber data: logistik_aktif -> realisasi_logistik_aktif. Tidak bercampur dengan Jakarta.
 */
class RealisasiLogistikAktifController extends Controller
{
    // ====================== REKAP AKTUAL LOGISTIK ======================
    public function logistikPrinted(Request $request)
    {
        $this->samakanNamaUnit();

        $query = RealisasiLogistikAktif::query();

        if ($request->filled('id_pesan')) {
            $query->where('no_pl', 'like', '%' . $request->id_pesan . '%');
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

        $allData = $query
            ->with('product')
            ->orderBy('tgl_turun_pl')
            ->orderBy('created_at')
            ->get();

        $groupedData = $allData->groupBy(function ($item) {
            return Carbon::parse($item->tgl_turun_pl ?? $item->created_at)->toDateString();
        });

        // Satu nomor rekap untuk semua order di tanggal yang sama
        foreach ($groupedData as $tanggal => $rows) {
            $rekapNumber = $this->generateRekapNumber($tanggal);

            RealisasiLogistikAktif::whereIn('id', $rows->pluck('id'))
                ->whereNull('rekap_number')
                ->update([
                    'rekap_number' => $rekapNumber,
                    'updated_at'   => now(),
                ]);

            foreach ($rows as $row) {
                $row->rekap_number = $rekapNumber;
            }
        }

        return view('order::order.logistik-printed', [
            'groupedData' => $groupedData,
        ]);
    }

    public function deleteRealisasi($id)
    {
        RealisasiLogistikAktif::findOrFail($id)->delete();

        return redirect()->route('order.logistik-printed')
            ->with('success', 'Data berhasil dihapus dari Rekap Aktual Logistik.');
    }

    // ====================== PICKING LIST ======================
    public function printPickingList($id)
    {
        $this->samakanNamaUnit([$id]);

        $main = RealisasiLogistikAktif::with('logistikAktif')->findOrFail($id);

        $logistik = $main->logistikAktif;

        if (! $logistik) {
            return back()->with('error', 'Data Logistik Aktif tidak ditemukan.');
        }

        $items = $this->itemPicking($logistik);

        if ($items->isEmpty()) {
            return back()->with('error', 'Tidak ada item untuk dibuatkan Picking List.');
        }

        // Tandai picking sudah dicetak
        if (is_null($main->picking_printed_at)) {
            $main->update(['picking_printed_at' => now()]);
        }

        return view('order::order.logistik-picking-list', $this->dataPicking($main, $logistik, $items));
    }

    public function printPickingListPdf($id)
    {
        $this->samakanNamaUnit([$id]);

        $main = RealisasiLogistikAktif::with('logistikAktif')->findOrFail($id);

        $logistik = $main->logistikAktif;

        if (! $logistik) {
            abort(404, 'Data Logistik Aktif tidak ditemukan');
        }

        $items = $this->itemPicking($logistik);

        if ($items->isEmpty()) {
            abort(404, 'Item picking tidak ditemukan');
        }

        if (is_null($main->picking_printed_at)) {
            $main->update(['picking_printed_at' => now()]);
        }

        $pdf = Pdf::loadView('order::order.logistik-picking-list-pdf', $this->dataPicking($main, $logistik, $items));

        $pdf->setPaper('A5', 'portrait');
        $pdf->setOptions([
            'margin-top'           => 8,
            'margin-right'         => 6,
            'margin-bottom'        => 18,
            'margin-left'          => 6,
            'isHtml5ParserEnabled' => true,
            'isPhpEnabled'         => true,
        ]);

        return $pdf->stream('Picking_List_LG_' . ($main->no_pl ?? 'unknown') . '_' . now()->format('Ymd_His') . '.pdf');
    }

    public function printTandaTerima($id)
    {
        $this->samakanNamaUnit([$id]);

        $item = RealisasiLogistikAktif::findOrFail($id);

        $pdf = Pdf::loadView('order::order.tanda-terima-pdf', [
            'no_pl'     => $item->no_pl,
            'nama_unit' => $item->nama_unit,
        ])
        ->setPaper('a6', 'portrait')
        ->setOptions([
            'defaultFont'          => 'sans-serif',
            'isHtml5ParserEnabled' => true,
        ]);

        return $pdf->stream('Tanda-Terima-' . ($item->no_pl ?? $item->id) . '.pdf');
    }

    // ====================== CETAK RA (per tanggal) ======================
    public function printRealisasiPdf(Request $request)
    {
        return $this->cetakRa($request, 'order::order.jakarta-printed-pdf', 'RA Pricing', 'RA-Logistik-Pricing', true);
    }

    public function printPemesanan(Request $request)
    {
        return $this->cetakRa($request, 'order::order.print-pemesanan', 'RA Picking', 'RA-Logistik-Picking');
    }

    public function printQC(Request $request)
    {
        return $this->cetakRa($request, 'order::order.print-qc', 'QC', 'QC-Logistik-Report');
    }

    public function printPacking(Request $request)
    {
        return $this->cetakRa($request, 'order::order.print-packing', 'Packing', 'Packing-Logistik-Report');
    }

    public function printEkspedisi(Request $request)
    {
        return $this->cetakRa($request, 'order::order.print-ekspedisi', 'Distribusi', 'Ekspedisi-Logistik-Report');
    }

    // ====================== HELPER ======================

    /**
     * Samakan nama unit di Rekap Aktual (dan Picking List, RA, QC, Packing, Distribusi)
     * dengan nama unit di Logistik Aktif. Dipanggil sebelum tampil/cetak, sehingga data
     * lama ikut terkoreksi dan perubahan nama di Logistik Aktif selalu terbawa.
     */
    private function samakanNamaUnit(?array $ids = null): void
    {
        $query = RealisasiLogistikAktif::query()->with('logistikAktif:id,nama_unit');

        if ($ids !== null) {
            $query->whereIn('id', $ids);
        }

        $query->get()->each(function ($row) {
            $nama = trim((string) optional($row->logistikAktif)->nama_unit);

            if ($nama !== '' && ($row->nama_unit !== $nama || $row->penyebut !== $nama)) {
                $row->forceFill(['nama_unit' => $nama, 'penyebut' => $nama])->saveQuietly();
            }
        });
    }

    /**
     * Cetak RA (PDF A4 landscape). Hanya data yang Picking List-nya sudah dicetak.
     * View print-* dipakai bersama dengan Jakarta Aktif (hanya membaca kolom realisasi).
     */
    private function cetakRa(Request $request, string $view, string $untuk, string $prefixFile, bool $tandaiCetak = false)
    {
        $ids = array_filter(explode(',', $request->get('ids', '')));

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada data yang dipilih.');
        }

        $this->samakanNamaUnit($ids);

        $data = RealisasiLogistikAktif::whereIn('id', $ids)
            ->whereNotNull('picking_printed_at')
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', "Belum ada data yang Picking List-nya selesai dicetak untuk {$untuk}.");
        }

        if ($tandaiCetak && $request->boolean('mark_printed')) {
            RealisasiLogistikAktif::whereIn('id', $data->pluck('id'))
                ->whereNull('printed_at')
                ->update(['printed_at' => now(), 'updated_at' => now()]);
        }

        $data = RealisasiLogistikAktif::whereIn('id', $data->pluck('id'))
            ->with(['jakartaAktif', 'product'])
            ->get();

        // Urut: jumlah item paling sedikit, tanggal, lalu nomor order
        $data = $data->sort(function ($a, $b) {
            $countA = empty($a->nama_barang) ? 0 : substr_count($a->nama_barang, '|') + 1;
            $countB = empty($b->nama_barang) ? 0 : substr_count($b->nama_barang, '|') + 1;

            if ($countA != $countB) {
                return $countA <=> $countB;
            }

            $dateCompare = strtotime($a->tgl_turun_pl) <=> strtotime($b->tgl_turun_pl);

            if ($dateCompare != 0) {
                return $dateCompare;
            }

            return ($a->no_pl ?? 0) <=> ($b->no_pl ?? 0);
        })->values();

        $pdf = Pdf::loadView($view, [
            'data'        => $data,
            'groupedData' => $data->groupBy(fn ($i) => Carbon::parse($i->tgl_turun_pl ?? $i->created_at)->toDateString()),
            'docNumber'   => $this->generateRekapNumber(optional($data->first())->tgl_turun_pl ?? now()),
        ])
        ->setPaper('A4', 'landscape')
        ->setOptions([
            'defaultFont'          => 'sans-serif',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true,
        ]);

        return $pdf->stream($prefixFile . '-' . now()->format('d-m-Y_H-i') . '.pdf');
    }

    /**
     * Item picking diambil langsung dari item order LG (tanpa tabel picking Jakarta).
     */
    private function itemPicking(LogistikAktif $logistik)
    {
        return $logistik->items()
            ->with('product')
            ->get()
            ->unique('sku')
            ->sortBy('sku')
            ->values()
            ->map(function ($i) {
                $nama = $i->product?->nama_produk ?? $i->nama_produk ?? $i->label ?? '-';

                return (object) [
                    'item_name' => preg_replace('/\s+/', ' ', trim($nama)),
                    'item_sku'  => $i->sku,
                    'item_qty'  => (int) $i->qty,
                    'product'   => $i->product,
                ];
            });
    }

    private function dataPicking(RealisasiLogistikAktif $main, LogistikAktif $logistik, $items): array
    {
        return [
            'item'              => $main,
            'data'              => $items,
            'no_pl'             => $main->no_pl,
            'tgl_order'         => $main->tgl_turun_pl,
            'billing_last_name' => $main->billing_last_name,
            'billing_company'   => $main->billing_company,
            'kategori_order'    => $main->kategori_order,
            'jakarta_aktif'     => $logistik, // nama variabel mengikuti view Jakarta (isinya order LG)
        ];
    }

    private function generateRekapNumber($tanggal = null)
    {
        $tanggal = Carbon::parse($tanggal ?: Carbon::now('Asia/Jakarta'))->toDateString();

        $existing = RealisasiLogistikAktif::whereDate('tgl_turun_pl', $tanggal)
            ->whereNotNull('rekap_number')
            ->value('rekap_number');

        if ($existing) {
            return $existing;
        }

        $lastNumber = RealisasiLogistikAktif::withTrashed()
            ->whereNotNull('rekap_number')
            ->max(DB::raw("CAST(REPLACE(rekap_number, '#', '') AS UNSIGNED)"));

        return '#' . str_pad(($lastNumber ?? 0) + 1, 4, '0', STR_PAD_LEFT);
    }
}
