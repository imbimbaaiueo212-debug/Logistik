<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\ManualOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Master\Models\Product;
use Illuminate\Http\Request;

class ReportAngkaCetakController extends Controller
{


/**
 * Report Angka Cetak Majalah (mirip Excel + otomatis terisi jika data ada)
 */
public function reportAngkaCetak(Request $request)
{
    $edisiList = ManualOrder::select('product_sku')
        ->whereNotNull('product_sku')
        ->where('product_sku', '!=', '')
        ->distinct()
        ->orderBy('product_sku')
        ->pluck('product_sku')
        ->map(fn ($e) => strtoupper(trim($e)))
        ->unique()
        ->values();

    $selectedEdisi = strtoupper(trim($request->get('edisi', $edisiList->last() ?? '')));

    if (empty($selectedEdisi)) {
        return view('import::import.report-angka-cetak', [
            'edisiList'     => $edisiList,
            'selectedEdisi' => null,
            'report'        => null,
        ]);
    }

    $kategoriAktif = [
    // Yang sudah ada datanya saat ini
    'A'     => ['Unit Aktif - Stokis Jakarta',     ['group' => 'B']],          // semua Group B masuk sini dulu

    // Yang belum ada datanya → biarkan 0
    'A1'    => ['PUW1 - Stokis Logistik',          ['keyword' => 'PUW1']],
    'A2'    => ['Stokis Pasif - Logistik',         ['keyword' => 'STOKIS PASIF']],
    'A3'    => ['Stokis Aktif - Logistik',         ['keyword' => 'STOKIS AKTIF']], // jangan pakai group B lagi
    'A4'    => ['Pengelola Unit Aktif Khusus',     ['keyword' => 'PUA']],

    // Stokis per wilayah (masih 0 sampai ada datanya)
    'TNG1'  => ['Tangerang 1',                     ['keyword' => 'TNG1']],
    'BKS1'  => ['Bekasi 1',                        ['keyword' => 'BKS1']],
    'SRG1'  => ['Serang 1',                        ['keyword' => 'SRG1']],
    'TNG2'  => ['Tangerang 2',                     ['keyword' => 'TNG2']],
    'BGR1'  => ['Bogor 1',                         ['keyword' => 'BGR1']],
    'BGRT'  => ['Bogor 2',                         ['keyword' => 'BGRT']],
    'DPK1'  => ['Depok 1',                         ['keyword' => 'DPK1']],
    'SKB1'  => ['Sukabumi 1',                      ['keyword' => 'SKB1']],
    'KNG'   => ['Kuningan 1',                      ['keyword' => 'KNG']],
    'IDM'   => ['Indramayu 1',                     ['keyword' => 'IDM']],
    'SKB2'  => ['Sukabumi 2',                      ['keyword' => 'SKB2']],
    'SNG'   => ['Subang 1',                        ['keyword' => 'SNG']],
    'KWG1'  => ['Karawang 1',                      ['keyword' => 'KWG1']],
    'PWK'   => ['Purwakarta 1',                    ['keyword' => 'PWK']],
    'BDG1'  => ['Bandung 1',                       ['keyword' => 'BDG1']],
    'BDG2'  => ['Bandung 2',                       ['keyword' => 'BDG2']],
    'CIL1'  => ['Cilincing 1',                     ['keyword' => 'CIL1']],
    'SRG2'  => ['Serang 2',                        ['keyword' => 'SRG2']],
    'DPR1'  => ['Denpasar 1',                      ['keyword' => 'DPR1']],
    'KWG2'  => ['Karawang 2',                      ['keyword' => 'KWG2']],
    'BGR3'  => ['Bogor 3',                         ['keyword' => 'BGR3']],
    'YYK1'  => ['Yogyakarta 1',                    ['keyword' => 'YYK1']],
    'CRB1'  => ['Cirebon 1',                       ['keyword' => 'CRB1']],
    'PKL1'  => ['Pekalongan 1',                    ['keyword' => 'PKL1']],
    'TNG3'  => ['Tangerang 3',                     ['keyword' => 'TNG3']],
    'SMG'   => ['Stock Central Semarang',          ['keyword' => 'SMG']],
    'SBY'   => ['Stock Central Surabaya',          ['keyword' => 'SBY']],
    'SGN'   => ['Stock Central Sragen',            ['keyword' => 'SGN']],
    'PUA1'  => ['Pengelola Unit Aktif Khusus 1',   ['keyword' => 'PUA1']],
    'PUA2'  => ['Pengelola Unit Aktif Khusus 2',   ['keyword' => 'PUA2']],
    'PUA3'  => ['Pengelola Unit Aktif Khusus 3',   ['keyword' => 'PUA3']],
];

    $kategoriPasif = [
        'P'  => ['Unit Pasif',                  ['group' => 'F']],
        'P1' => ['Murid InterVio',              ['keyword' => 'INTERVIO']],
        'P2' => ['Unit Pasif (Bacaan)',         ['group' => 'A', 'product' => 'Bacaan']],
        'P3' => ['Humas & Bundel',              ['keyword' => 'HUMAS']],
        'P4' => ['Spare Unit Pasif 3%',         ['group' => 'A', 'product' => 'Spare']],
    ];

    // =====================================================
    // FUNGSI HITUNG QTY
    // =====================================================
    $hitungQty = function (array $rule) use ($selectedEdisi) {
        $query = ManualOrder::where('product_sku', $selectedEdisi);

        // Filter berdasarkan group
        if (!empty($rule['group'])) {
            $query->where('grup', $rule['group']);
        }

        // Filter berdasarkan keyword (cari di customer_name / notes / order_id)
        if (!empty($rule['keyword'])) {
            $kw = $rule['keyword'];
            $query->where(function ($q) use ($kw) {
                $q->where('customer_name', 'like', "%{$kw}%")
                  ->orWhere('notes', 'like', "%{$kw}%")
                  ->orWhere('catatan', 'like', "%{$kw}%")
                  ->orWhere('order_id', 'like', "%{$kw}%")
                  ->orWhere('billing_last_name', 'like', "%{$kw}%");
            });
        }

        // Filter berdasarkan product_name
        if (!empty($rule['product'])) {
            $query->where('product_name', 'like', '%' . $rule['product'] . '%');
        }

        return (int) $query->sum('qty');
    };

    // =====================================================
    // HITUNG SEMUA KATEGORI
    // =====================================================
    $rowsAktif = [];
    $totalAktif = 0;

    foreach ($kategoriAktif as $kode => $item) {
        [$label, $rule] = $item;
        $qty = $hitungQty($rule);
        $rowsAktif[] = [
            'kode'  => $kode,
            'label' => $label,
            'qty'   => $qty,
        ];
        $totalAktif += $qty;
    }

    $rowsPasif = [];
    $totalPasif = 0;

    foreach ($kategoriPasif as $kode => $item) {
        [$label, $rule] = $item;
        $qty = $hitungQty($rule);
        $rowsPasif[] = [
            'kode'  => $kode,
            'label' => $label,
            'qty'   => $qty,
        ];
        $totalPasif += $qty;
    }

    $grandTotal = $totalAktif + $totalPasif;

    $report = [
        'edisi'         => $selectedEdisi,
        'rows_aktif'    => $rowsAktif,
        'total_aktif'   => $totalAktif,
        'rows_pasif'    => $rowsPasif,
        'total_pasif'   => $totalPasif,
        'grand_total'   => $grandTotal,
    ];

    return view('import::import.report-angka-cetak', compact(
        'edisiList',
        'selectedEdisi',
        'report'
    ));
}
}
