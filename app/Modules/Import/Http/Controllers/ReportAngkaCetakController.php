<?php

namespace App\Modules\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\ManualOrder;
use App\Modules\Import\Models\BimbashopOrder;
use Maatwebsite\Excel\Facades\Excel;
use App\Modules\Master\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportAngkaCetakController extends Controller
{


/**
 * Report Angka Cetak Majalah (mirip Excel + otomatis terisi jika data ada)
 */
public function reportAngkaCetak(Request $request)
{
    $edisiManual = ManualOrder::select('product_sku')
        ->whereNotNull('product_sku')
        ->where('product_sku', '!=', '')
        ->distinct()
        ->pluck('product_sku')
        ->map(fn ($e) => strtoupper(trim($e)));

    // Edisi dari biMBA Shop: SKU "M163-JKT" / "M163-JKTP" -> edisi "M163"
    $edisiShop = BimbashopOrder::select('item_sku')
        ->where('item_sku', 'like', 'M%')
        ->distinct()
        ->pluck('item_sku')
        ->map(function ($sku) {
            return preg_match('/^(M\d+)/i', trim((string) $sku), $m) ? strtoupper($m[1]) : null;
        })
        ->filter();

    // Edisi dari Pesanan Majalah (kolom bulan, contoh: "DESEMBER (M164)")
    $edisiPesanan = collect();
    foreach (['pesanan_majalah', 'pesanan_majalah_puw1'] as $tbl) {
        $edisiPesanan = $edisiPesanan->merge(
            DB::table($tbl)->whereNotNull('bulan')->pluck('bulan')
                ->map(fn ($b) => preg_match('/\((M\d+)\)/i', (string) $b, $m) ? strtoupper($m[1]) : null)
                ->filter()
        );
    }

    $edisiList = $edisiManual
        ->merge($edisiShop)
        ->merge($edisiPesanan)
        ->unique()
        ->sort()
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
    // Sumber: order biMBA Shop SKU {EDISI}-JKT yang sudah dibayar (processing/completed)
    'A'     => ['Unit Aktif - Stokis Jakarta',     ['shop' => 'JKT']],

    // Yang belum ada datanya → biarkan 0
    // Sumber: Pesanan Majalah KORWIL + PINWIL + JABODETABEK (PUW1) untuk edisi terpilih
    'A1'    => ['PUW1 - Stokis Logistik',          ['pesanan_majalah' => true]],
    'A2'    => ['Stokis Pasif - Logistik',         ['shop_pasif' => true]], // SKU {EDISI}-SMG/SBY/SGN/YYK dari biMBA Shop
    'A3'    => ['Stokis Aktif - Logistik',         ['shop_aktif' => true]], // SKU {EDISI}-TNG1/BKS/DPK/... dari biMBA Shop (billing name stokis saja)
    'A4'    => ['Pengelola Unit Aktif Khusus',     ['shop_pengelola' => true]], // billing name "Pengelola Unit Aktif Khusus .." dari biMBA Shop

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

    // Baris per-stokis hanya RINCIAN (sudah terhitung di A2 / A3 / A4): tampil saja, tidak dijumlah ke total.
    $petaA3 = [];
    $petaA4 = [];
    foreach ($kategoriAktif as $kode => [$label, $rule]) {
        if (!isset($rule['keyword'])) {
            continue;
        }
        if (str_starts_with($kode, 'PUA')) {
            $kategoriAktif[$kode][1] = ['rinci' => 'a4', 'kode' => $kode];
            $petaA4[$kode] = $label;
        } elseif (in_array($kode, ['SMG', 'SBY', 'SGN'], true)) {
            $kategoriAktif[$kode][1] = ['rinci' => 'a2', 'kode' => $kode];
        } else {
            $kategoriAktif[$kode][1] = ['rinci' => 'a3', 'kode' => $kode];
            $petaA3[$kode] = $label;
        }
    }

    $kategoriPasif = [
        'P'  => ['Unit Pasif',                  ['pasif_import' => true]],   // total qty Unit Pasif (import) edisi terpilih
        'P1' => ['Murid InterVio',              ['dlc_import' => true]],   // total qty DLC edisi terpilih
        'P2' => ['Unit Pasif (Bacaan)',         ['bacaan_import' => true]],   // total Bacaan Unit edisi terpilih
        'P3' => ['Humas & Bundel',              ['humas_shop' => true]],   // biMBA Shop SKU {EDISI}-JKTP, billing Kantor Pusat Tanjung Duren
        'P4' => ['Spare Unit Pasif 3%',         ['spare3' => true]],   // (P + P1 + P2) x 3%, dibulatkan
    ];

    // =====================================================
    // FUNGSI HITUNG QTY
    // =====================================================
    $rinci = null;   // cache rincian A3/A4 (dihitung sekali)

    $hitungQty = function (array $rule) use ($selectedEdisi, $petaA3, $petaA4, &$rinci) {
        // Rincian per stokis (tampil saja)
        if (!empty($rule['rinci'])) {
            if ($rule['rinci'] === 'a2') {
                return $this->qtyShopStokis($selectedEdisi, [$rule['kode']]);
            }
            $rinci ??= \App\Modules\Import\Support\AngkaCetakLogistik::rincian($selectedEdisi, $petaA3, $petaA4);

            return (int) ($rinci[$rule['rinci']][$rule['kode']] ?? 0);
        }

        // Sumber Pesanan Majalah: KORWIL + PINWIL + JABODETABEK (PUW1)
        if (!empty($rule['pesanan_majalah'])) {
            return $this->qtyPesananMajalah($selectedEdisi);
        }

        // Spare Unit Pasif 3% = (Unit Pasif + DLC + Bacaan) x 3%, dibulatkan (sama dengan halaman Spare Pasif 3%)
        if (!empty($rule['spare3'])) {
            $dasar = $this->qtyUnitPasif($selectedEdisi)
                + $this->qtyDlc($selectedEdisi)
                + $this->qtyBacaan($selectedEdisi);

            return (int) round($dasar * 0.03);
        }

        // Sumber biMBA Shop: Humas & Bundel = SKU {EDISI}-JKTP dengan billing name Kantor Pusat Tanjung Duren (0026)
        if (!empty($rule['humas_shop'])) {
            return $this->qtyHumasBundel($selectedEdisi);
        }

        // Sumber Bacaan Unit (Unit Pasif > Bacaan Unit): total bacaan_unit semua unit untuk edisi terpilih
        if (!empty($rule['bacaan_import'])) {
            return (int) \App\Modules\Import\Models\BacaanPesanan::query()
                ->whereHas('periode', fn ($q) => $q->whereRaw('UPPER(TRIM(edisi)) = ?', [strtoupper(trim($selectedEdisi))]))
                ->sum('bacaan_unit');
        }

        // Sumber DLC (halaman DLC): total qty semua unit untuk edisi terpilih
        if (!empty($rule['dlc_import'])) {
            return (int) \App\Modules\Import\Models\DlcPesanan::query()
                ->whereHas('periode', fn ($q) => $q->whereRaw('UPPER(TRIM(edisi)) = ?', [strtoupper(trim($selectedEdisi))]))
                ->sum('qty');
        }

        // Sumber Unit Pasif (halaman Unit Pasif > Daftar Pasif): total qty semua unit untuk edisi terpilih
        if (!empty($rule['pasif_import'])) {
            return $this->qtyUnitPasif($selectedEdisi);
        }

        // Sumber biMBA Shop: stokis pasif logistik (SMG, SBY, SGN, YYK)
        if (!empty($rule['shop_pasif'])) {
            return $this->qtyShopStokisPasif($selectedEdisi);
        }

        // Sumber biMBA Shop: stokis aktif logistik (billing name "Stokis ...")
        if (!empty($rule['shop_aktif'])) {
            return $this->qtyShopLogistik($selectedEdisi)['stokis'];
        }

        // Sumber biMBA Shop: Pengelola Unit Aktif Khusus (billing name "Pengelola Unit Aktif Khusus ...")
        if (!empty($rule['shop_pengelola'])) {
            return $this->qtyShopLogistik($selectedEdisi)['pengelola'];
        }

        // Sumber biMBA Shop (bimbashop_orders)
        if (!empty($rule['shop'])) {
            return (int) BimbashopOrder::where('item_sku', $selectedEdisi . '-' . $rule['shop'])
                ->whereIn('status', ['processing', 'completed', 'wc-processing', 'wc-completed'])
                ->sum('item_qty');
        }

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
        $info = !empty($rule['rinci']);   // rincian: tampil saja, tidak masuk total
        $rowsAktif[] = [
            'kode'  => $kode,
            'label' => $label,
            'qty'   => $qty,
            'info'  => $info,
        ];
        if (! $info) {
            $totalAktif += $qty;
        }
    }

    // Selisih rincian dengan A3 / A4 (nama billing tidak cocok daftar stokis), supaya jumlah rincian tetap klop
    foreach ([['a3_lain', 'A3-', 'Stokis Aktif lainnya (nama billing tidak ada di daftar)'],
              ['a4_lain', 'A4-', 'Pengelola lainnya (nama billing tidak ada di daftar)']] as [$kunci, $kodeLain, $labelLain]) {
        if (!empty($rinci[$kunci])) {
            $rowsAktif[] = ['kode' => $kodeLain, 'label' => $labelLain, 'qty' => (int) $rinci[$kunci], 'info' => true];
        }
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

    /** Stokis pasif logistik: SKU {EDISI}-SMG / -SBY / -SGN / -YYK. */
    private function qtyShopStokisPasif(string $edisi): int
    {
        return $this->qtyShopStokis($edisi, \App\Modules\OrderManual\Support\KodeStokisSku::PASIF);
    }

    /**
     * Qty dari order biMBA Shop untuk SKU {EDISI}-<kode> (dipakai A2).
     * Dihitung: processing / completed, ATAU dibayar di luar Casdana (wallet / deposit / COD / other).
     * Dikecualikan: cancelled, failed, refunded.
     */
    private function qtyShopStokis(string $edisi, array $kode): int
    {
        $baris = BimbashopOrder::query()
            ->where(function ($q) use ($edisi, $kode) {
                foreach ($kode as $k) {
                    $q->orWhere('item_sku', $edisi . '-' . $k);
                }
            })
            ->get(['status', 'payment_method', 'item_qty']);

        $total = 0;
        foreach ($baris as $o) {
            $status = preg_replace('/^wc-/', '', strtolower(trim((string) $o->status)));

            if (preg_match('/cancel|fail|refund|trash/', $status)) {
                continue;
            }

            $metode      = strtolower((string) $o->payment_method);
            $dibayar     = in_array($status, ['processing', 'completed'], true);
            $luarCasdana = \App\Modules\OrderManual\Services\CetakShopMatcher::bayarTanpaCasdana($o->payment_method)
                || str_contains($metode, 'deposit');

            if ($dibayar || $luarCasdana) {
                $total += max((int) $o->item_qty, 0);
            }
        }

        return $total;
    }

    /** Humas & Bundel (P3): order biMBA Shop SKU {EDISI}-JKTP atas nama Kantor Pusat Tanjung Duren; status batal dikecualikan. */
    private function qtyHumasBundel(string $edisi): int
    {
        $baris = BimbashopOrder::query()
            ->where('item_sku', $edisi . '-JKTP')
            ->get(['status', 'item_qty', 'billing_first_name', 'billing_last_name', 'billing_company']);

        $total = 0;
        foreach ($baris as $o) {
            $status = preg_replace('/^wc-/', '', strtolower(trim((string) $o->status)));
            if (preg_match('/cancel|fail|refund|trash/', $status)) {
                continue;
            }

            $nama = strtoupper(preg_replace('/\s+/', ' ', trim(implode(' ', array_filter([
                $o->billing_first_name, $o->billing_last_name, $o->billing_company,
            ])))));

            if (preg_match('/KANTOR\s+PUSAT\s+TANJUNG\s+DUREN/', $nama)) {
                $total += max((int) $o->item_qty, 0);
            }
        }

        return $total;
    }

    private function qtyDlc(string $edisi): int
    {
        return (int) \App\Modules\Import\Models\DlcPesanan::query()
            ->whereHas('periode', fn ($q) => $q->whereRaw('UPPER(TRIM(edisi)) = ?', [strtoupper(trim($edisi))]))
            ->sum('qty');
    }

    private function qtyBacaan(string $edisi): int
    {
        return (int) \App\Modules\Import\Models\BacaanPesanan::query()
            ->whereHas('periode', fn ($q) => $q->whereRaw('UPPER(TRIM(edisi)) = ?', [strtoupper(trim($edisi))]))
            ->sum('bacaan_unit');
    }

    /** Total qty Unit Pasif satu edisi (sama dengan "Total Qty" di halaman detail Unit Pasif). */
    private function qtyUnitPasif(string $edisi): int
    {
        return (int) \App\Modules\Import\Models\PasifPesanan::query()
            ->whereHas('periode', fn ($q) => $q->whereRaw('UPPER(TRIM(edisi)) = ?', [strtoupper(trim($edisi))]))
            ->sum('qty');
    }

    /** Cache hasil qtyShopLogistik per edisi (A3 dan A4 memakai sumber yang sama). */
    private array $cacheLogistik = [];

    /**
     * Order logistik biMBA Shop satu edisi, dipisah menurut BILLING NAME:
     *   - stokis    : "Stokis Tangerang 1 2942.S", "STOKIS PEKALONGAN1", ...      -> A3
     *   - pengelola : "Pengelola Unit Aktif Khusus 1/2/3 ..."                    -> A4
     *   - PUW       : "Pengelola Unit Wilayah 01 PUW 1" -> TIDAK dihitung di sini (sudah di A1)
     *
     * Sumber order: SKU {EDISI}-LG, {EDISI}-<kode stokis aktif>, {EDISI}-PUA1..3.
     * Untuk SKU kode stokis (bukan LG) yang namanya tidak dikenali, tetap dihitung sebagai stokis
     * (perilaku lama), kecuali namanya PUW / Pengelola.
     *
     * Aturan bayar sama seperti sebelumnya: processing/completed, atau dibayar di luar Casdana
     * (wallet / COD / other / deposit). cancelled, failed, refunded, trash dikecualikan.
     *
     * @return array{stokis:int, pengelola:int}
     */
    private function qtyShopLogistik(string $edisi): array
    {
        if (isset($this->cacheLogistik[$edisi])) {
            return $this->cacheLogistik[$edisi];
        }

        $hasil = \App\Modules\Import\Support\AngkaCetakLogistik::hitung($edisi);

        return $this->cacheLogistik[$edisi] = $hasil;
    }

    /**
     * Total pesanan majalah satu edisi dari 3 sumber:
     * KORWIL (kabupaten), PINWIL (kotamadya), dan JABODETABEK (PUW1).
     * Angka per unit dibulatkan dulu baru dijumlah (selaras dengan Excel).
     */
    private function qtyPesananMajalah(string $edisi): int
    {
        $like = '%(' . $edisi . ')%';

        $periode = function ($q, string $alias) use ($like) {
            $q->where(function ($w) use ($alias, $like) {
                $w->where("$alias.bulan", 'like', $like)
                  ->orWhere("$alias.judul", 'like', $like);
            });
        };

        // KORWIL
        $q1 = DB::table('pesanan_majalah_unit as u')
            ->join('pesanan_majalah_kabupaten as k', 'k.id', '=', 'u.pesanan_majalah_kabupaten_id')
            ->join('pesanan_majalah as p', 'p.id', '=', 'k.pesanan_majalah_id');
        $periode($q1, 'p');

        // PINWIL
        $q2 = DB::table('pesanan_majalah_unit_kotamadya as u')
            ->join('pesanan_majalah_kotamadya as k', 'k.id', '=', 'u.pesanan_majalah_kotamadya_id')
            ->join('pesanan_majalah as p', 'p.id', '=', 'k.pesanan_majalah_id');
        $periode($q2, 'p');

        // JABODETABEK (PUW1)
        $q3 = DB::table('pesanan_majalah_unit_puw1 as u')
            ->join('pesanan_majalah_puw1 as p', 'p.id', '=', 'u.pesanan_majalah_puw1_id');
        $periode($q3, 'p');

        $expr = DB::raw('ROUND(u.jumlah_pesanan)');

        return (int) ($q1->sum($expr) + $q2->sum($expr) + $q3->sum($expr));
    }
}