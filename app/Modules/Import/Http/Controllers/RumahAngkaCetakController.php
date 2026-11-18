<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Support\AngkaCetakLogistik;
use Illuminate\Http\Request;

/**
 * Rumah Angka Cetak Majalah: rincian per order di balik satu kode (A, A1, A2, A3, ...).
 * Tahap 1: baru A3 (Stokis Aktif - Logistik) supaya bisa dicocokkan dengan Excel.
 */
class RumahAngkaCetakController extends Controller
{
    private const KODE_TERSEDIA = ['A3' => 'Stokis Aktif - Logistik'];

    public function index(Request $request)
    {
        $edisiList = BimbashopOrder::query()
            ->where('item_sku', 'like', 'M%')
            ->distinct()->pluck('item_sku')
            ->map(fn ($sku) => preg_match('/^(M\d+)/i', trim((string) $sku), $m) ? strtoupper($m[1]) : null)
            ->filter()->unique()->sort()->values();

        $edisi = strtoupper(trim((string) $request->get('edisi', $edisiList->last() ?? '')));
        $kode  = strtoupper(trim((string) $request->get('kode', 'A3')));
        $tampil = in_array($request->get('tampil'), ['dihitung', 'tidak', 'semua'], true) ? $request->get('tampil') : 'dihitung';
        $excel  = (int) preg_replace('/\D/', '', (string) $request->get('excel', '45225'));

        if ($edisi === '' || ! isset(self::KODE_TERSEDIA[$kode])) {
            return view('import::import.rumah-angka-cetak', [
                'edisiList' => $edisiList, 'edisi' => $edisi ?: null, 'kode' => $kode,
                'kodeTersedia' => self::KODE_TERSEDIA, 'data' => null, 'tampil' => $tampil, 'excel' => $excel,
            ]);
        }

        $orders = AngkaCetakLogistik::query($edisi)->orderBy('order_date')->orderBy('order_id')->get();

        // Duplikat: order_id + SKU yang muncul lebih dari sekali (sering penyebab selisih)
        $hitungKey = $orders->groupBy(fn ($o) => $o->order_id . '|' . strtoupper(trim((string) $o->item_sku)))
            ->map->count();

        $kasdana = AngkaCetakLogistik::kasdanaMap($orders->pluck('order_id'));

        $rows = $orders->map(function ($o) use ($hitungKey, $kasdana) {
            $k = AngkaCetakLogistik::klasifikasi($o, $kasdana);
            $key = $o->order_id . '|' . strtoupper(trim((string) $o->item_sku));

            return $k + [
                'id' => $o->id, 'order_id' => $o->order_id, 'tanggal' => optional($o->order_date)->format('d-m-Y'),
                'sku' => strtoupper(trim((string) $o->item_sku)), 'metode' => $o->payment_method,
                'duplikat' => ($hitungKey[$key] ?? 1) > 1,
            ];
        });

        $a3Baris = $rows->where('grup', 'stokis');      // baris yang dihitung ke A3
        $total = (int) $a3Baris->sum('qty');

        // Tampilan: order dengan nama billing sama digabung jadi satu baris (qty dijumlah)
        $a3 = $a3Baris->groupBy('nama')->map(function ($g) {
            $f = $g->first();

            return array_merge($f, [
                'order_id' => $g->pluck('order_id')->unique()->implode(', '),
                'tanggal'  => $g->pluck('tanggal')->unique()->implode(', '),
                'status'   => $g->pluck('status')->unique()->implode(', '),
                'qty'      => (int) $g->sum('qty'),
                'alasan'   => $g->pluck('alasan')->unique()->implode('; '),
                'duplikat' => $g->contains('duplikat', true),
                'lewat_casdana_bukan' => $g->contains('lewat_casdana_bukan', true),
            ]);
        })->values();

        // Ringkasan per kode SKU stokis
        $perStokis = $a3->groupBy('kode')->map(fn ($g, $kd) => [
            'kode' => $kd, 'order' => $g->count(), 'qty' => (int) $g->sum('qty'),
            'dup'  => (int) $g->where('duplikat', true)->sum('qty'),
        ])->sortBy('kode')->values();

        // Petunjuk penyebab selisih
        $petunjuk = [
            'order LG dibayar di luar Casdana (wallet/COD/other), tidak lewat Kasdana' =>
                (int) $a3->where('lewat_casdana_bukan', true)->sum('qty'),
            'order_id + SKU muncul lebih dari satu kali (kemungkinan dobel import)' =>
                (int) $a3->where('duplikat', true)->sum('qty'),
            'LG stokis yang TIDAK ada / belum lunas di Kasdana (tidak dihitung)' =>
                (int) $rows->where('grup', 'belum_kasdana')->sum('qty'),
            'LG yang nama billing-nya bukan stokis (tidak dihitung)' =>
                (int) $rows->where('grup', 'bukan_stokis')->sum('qty'),
            'LG dari stokis pasif (tidak dihitung)' =>
                (int) $rows->where('grup', 'stokis_pasif')->sum('qty'),
        ];

        $tidakDihitung = $rows->where('dihitung', false);
        $tampilRows = match ($tampil) {
            'tidak' => $tidakDihitung->values(),
            'semua' => $rows->values(),
            default => $a3->values(),
        };

        if ($request->get('export') === 'csv') {
            return response()->streamDownload(function () use ($tampilRows) {
                $f = fopen('php://output', 'w');
                fwrite($f, "\xEF\xBB\xBF");
                fputcsv($f, ['order_id', 'tanggal', 'sku', 'kode', 'nama_billing', 'status', 'metode_bayar', 'qty', 'dihitung_A3', 'duplikat', 'alasan']);
                foreach ($tampilRows as $r) {
                    fputcsv($f, [$r['order_id'], $r['tanggal'], $r['sku'], $r['kode'], $r['nama'], $r['status'],
                        $r['metode'], $r['qty'], $r['grup'] === 'stokis' ? 'ya' : 'tidak', $r['duplikat'] ? 'ya' : '', $r['alasan']]);
                }
                fclose($f);
            }, "rumah-{$kode}-{$edisi}.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return view('import::import.rumah-angka-cetak', [
            'edisiList' => $edisiList, 'edisi' => $edisi, 'kode' => $kode, 'kodeTersedia' => self::KODE_TERSEDIA,
            'tampil' => $tampil, 'excel' => $excel,
            'data' => [
                'total' => $total, 'selisih' => $excel ? $total - $excel : null,
                'jumlah_order' => $a3->count(), 'perStokis' => $perStokis, 'petunjuk' => $petunjuk,
                'tidak_dihitung_qty' => (int) $tidakDihitung->sum('qty'),
                'rows' => $tampilRows,
            ],
        ]);
    }
}