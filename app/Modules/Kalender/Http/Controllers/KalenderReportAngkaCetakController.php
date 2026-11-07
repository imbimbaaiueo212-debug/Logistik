<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Models\CasdanaTransaction;
use App\Modules\Import\Services\ShopMatcher;
use App\Modules\Kalender\Models\KalenderBacaanPesanan;
use App\Modules\Kalender\Models\KalenderDlcPesanan;
use App\Modules\Kalender\Models\KalenderPasifManualTransaksi;
use App\Modules\Kalender\Models\KalenderPasifPesanan;
use App\Modules\Kalender\Models\KalenderPuwUnit;
use App\Modules\Kalender\Models\KalenderSpareManual;
use App\Modules\Kalender\Support\KodeKalender;
use Illuminate\Http\Request;

/**
 * Report Angka Cetak Kalender, mengikuti susunan Excel "Report Angka Cetak".
 *
 *  Kode A (Unit Aktif)  = order Kalender di biMBA Shop yang SUDAH BAYAR (ada di Casdana: SUCCESS / SETTLED).
 *  Kode P (Unit Pasif)  = tanpa pembayaran Casdana (souvenir). PUW masuk P7, jadi A1 (PUW) selalu 0.
 *  Baris yang menunya belum ada tetap tampil dengan nilai 0; begitu menunya ada, angkanya otomatis terisi.
 *
 * Korwil / Pinwil / PUW1 lama tidak dipakai lagi.
 */
class KalenderReportAngkaCetakController extends Controller
{
    /** Kode stokis (token SKU) per kelompok, sesuai Excel. */
    private const STOKIS_PASIF = [
        'SMG' => 'Stokis Semarang', 'SBY' => 'Stokis Surabaya', 'SGN' => 'Stokis Sragen',
    ];

    private const STOKIS_AKTIF = [
        'TNG1' => 'Stokis Tangerang 1', 'BKS1' => 'Stokis Bekasi 1', 'SRG1' => 'Stokis Serang 1',
        'TNG2' => 'Stokis Tangerang 2', 'BGR1' => 'Stokis Bogor 1', 'BGRT' => 'Stokis Bogor 2',
        'DPK1' => 'Stokis Depok 1', 'SKB1' => 'Stokis Sukabumi 1', 'KNG' => 'Stokis Kuningan 1',
        'IDM' => 'Stokis Indramayu 1', 'SKB2' => 'Stokis Sukabumi 2', 'SNG' => 'Stokis Subang 1',
        'KWG1' => 'Stokis Karawang 1', 'PWK' => 'Stokis Purwakarta 1', 'BDG1' => 'Stokis Bandung 1',
        'BDG2' => 'Stokis Bandung 2', 'CIL1' => 'Stokis Cilincing 1', 'SRG2' => 'Stokis Serang 2',
        'DPR1' => 'Stokis Denpasar 1', 'KWG2' => 'Stokis Karawang 2', 'BGR3' => 'Stokis Bogor 3',
        'YYK1' => 'Stokis Yogyakarta 1', 'CRB1' => 'Stokis Cirebon 1',
        // ada di sync Jakarta / report Majalah tetapi tidak di Excel: dimasukkan agar tidak salah masuk ke Stokis Jakarta
        'TNG3' => 'Stokis Tangerang 3', 'PKL1' => 'Stokis Pekalongan 1',
    ];

    private const PENGELOLA = [
        'PUA1' => 'Pengelola Unit Aktif Khusus 1', 'PUA2' => 'Pengelola Unit Aktif Khusus 2', 'PUA3' => 'Pengelola Unit Aktif Khusus 3',
    ];

    /** Rumah Jakarta pasif: bukan Unit Aktif, tidak dihitung di kode A. */
    private const BUKAN_AKTIF = ['JKTP', 'DLC', 'EBT', 'INV', 'BSS'];

    public function reportAngkaCetak(Request $request)
    {
        $norm = fn ($v) => KodeKalender::normalize($v);

        $edisiList = collect()
            ->merge(\App\Modules\Kalender\Models\KalenderPuwPeriode::pluck('edisi'))
            ->merge(\App\Modules\Kalender\Models\KalenderDlcPeriode::pluck('edisi'))
            ->merge(\App\Modules\Kalender\Models\KalenderPasifPeriode::pluck('edisi'))
            ->merge(\App\Modules\Kalender\Models\KalenderBacaanPeriode::pluck('edisi'))
            ->merge(\App\Modules\Kalender\Models\KalenderPasifManualPeriode::pluck('edisi'))
            ->map($norm)
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $selectedEdisi = $norm($request->get('edisi', $edisiList->last() ?? ''));

        if ($selectedEdisi === '') {
            return view('kalender::report-angka-cetak', [
                'edisiList' => $edisiList, 'selectedEdisi' => null, 'report' => null,
            ]);
        }

        $tahun  = KodeKalender::tahun($selectedEdisi);
        $varian = KodeKalender::varian($selectedEdisi);
        $in     = implode(',', array_fill(0, count($varian), '?'));

        $periodeEdisi = fn ($q) => $q->where('status', 'aktif')->whereRaw("UPPER(TRIM(edisi)) IN ($in)", $varian);

        // ======================= UNIT AKTIF (kode A): biMBA Shop + Casdana =======================
        $hasilShop  = $tahun === null ? ['bayar' => [], 'semua' => []] : $this->qtyBimbaShop($tahun);
        $shop       = $hasilShop['bayar'];   // [token => qty] yang sudah dibayar, '_JKT' = tanpa kode stokis
        $shopSemua  = $hasilShop['semua'];   // [token => qty] seluruh order biMBA Shop (pembanding)

        $aktif = [
            $this->baris('A',  'Unit Aktif - Stokis Jakarta', (int) ($shop['_JKT'] ?? 0), null, (int) ($shopSemua['_JKT'] ?? 0)),
            // PUW tidak ada pembayaran Casdana -> selalu 0 di sini, nilainya ada di P7
            $this->baris('A1', 'PUW - Stokis Logistik (masuk P7)', 0),
            $this->induk('A2', 'Stokis Pasif - Logistik', $this->anak(self::STOKIS_PASIF, $shop, $shopSemua), 'Unit Aktif - Stokis Pasif :'),
            $this->induk('A3', 'Stokis Aktif - Logistik', $this->anak(self::STOKIS_AKTIF, $shop, $shopSemua), 'Unit Aktif - Stokis Aktif :'),
            $this->induk('A4', 'Pengelola Unit Aktif Khusus', $this->anak(self::PENGELOLA, $shop, $shopSemua)),
        ];

        // ======================= UNIT PASIF (kode P): tanpa Casdana =======================
        $qtyMurid  = (int) KalenderPasifPesanan::whereHas('periode', $periodeEdisi)->sum('qty');
        $qtyHumas  = (int) KalenderPasifPesanan::whereHas('periode', $periodeEdisi)->sum('humas');
        $qtySpare  = (int) KalenderSpareManual::whereRaw("UPPER(TRIM(edisi)) IN ($in)", $varian)->sum('qty');
        $qtyDlc    = (int) KalenderDlcPesanan::whereHas('periode', $periodeEdisi)->sum('qty');
        $qtyBacaan = (int) KalenderBacaanPesanan::whereHas('periode', $periodeEdisi)->sum('bacaan_unit');
        $qtyManual = (int) KalenderPasifManualTransaksi::whereHas('periode', $periodeEdisi)->sum('jumlah');

        $qtyPuw = (int) KalenderPuwUnit::whereHas('periode', fn ($q) => $q->whereRaw("UPPER(TRIM(edisi)) IN ($in)", $varian))->sum('qty');

        $pasif = [
            $this->induk('P', 'biMBA Unit Pasif', [
                $this->baris('P01', 'Murid', $qtyMurid),
                $this->baris('P02', 'Motivator', 0),
                $this->baris('P03', 'Humas', $qtyHumas),
                $this->baris('P04', 'Unit biMBA Pasif (Cabang Unit)', 0),
                $this->baris('P05', 'Spare Untuk Murid Pasif 3%', $qtySpare),
            ]),
            $this->induk('P1', 'Murid InterVio', $this->nol(['P1.1' => 'Anggrek 1', 'P1.2' => 'Anggrek 2', 'P1.3' => 'Anggrek 3', 'P1.4' => 'Anggrek 4', 'P1.5' => 'Anggrek 5', 'P1.6' => 'Dhuafa', 'P1.7' => 'Khusus'])),
            $this->induk('P2', 'Motivator Intervio', $this->nol(['P2.1' => 'Anggrek 1', 'P2.2' => 'Anggrek 2', 'P2.3' => 'Anggrek 3', 'P2.4' => 'Anggrek 4', 'P2.5' => 'Anggrek 5', 'P2.6' => 'Dhuafa'])),
            $this->induk('P3', 'Kantor biMBA', $this->nol(['P3.1' => 'Kantor Pusat', 'P3.2' => 'Kantor Wilayah'])),
            $this->induk('P4', 'Relawan Pembelajar biMBA', $this->nol(['P4.1' => 'Relawan WFO - Kantor', 'P4.2' => 'Relawan WFH - Rumah'])),
            $this->induk('P5', 'biMBA SS', $this->nol(['P5.1' => 'Murid SS', 'P5.2' => 'Official + Pelatih', 'P5.3' => 'Relawan SS', 'P5.4' => 'Relasi biMBA SS'])),
            $this->induk('P6', 'Manajemen biMBA', $this->nol(['P6.B1' => 'biMBA 1', 'P6.B2' => 'biMBA 2', 'P6.B3' => 'biMBA 3', 'P6.B4' => 'biMBA 4', 'P6.B5' => 'biMBA 5', 'P6.B6' => 'biMBA 6'])),
            // Baru: PUW tidak ada pembayaran Casdana, jadi masuk P7
            $this->baris('P7', 'PUW (tanpa pembayaran)', $qtyPuw),
            // Souvenir unit yang order ke Jakarta (cek 3 bulan terakhir oleh tim): menunggu aturan hitung
            $this->baris('P8', 'Souvenir Unit ke Jakarta', 0, 'belum dihitung, menunggu aturan'),
            // Menu yang sudah ada di aplikasi tetapi belum punya kode di Excel: tetap dihitung agar tidak hilang
            $this->induk('LN', 'Lainnya (belum ada kode di Excel)', [
                $this->baris('DLC', 'DLC', $qtyDlc),
                $this->baris('BCN', 'Bacaan Unit', $qtyBacaan),
                $this->baris('PSM', 'Pasif Manual', $qtyManual),
            ]),
        ];

        $totalAktif = array_sum(array_column($aktif, 'qty'));
        $totalPasif = array_sum(array_column($pasif, 'qty'));

        $report = [
            'edisi'       => $selectedEdisi,
            'rows_aktif'  => $this->ratakan($aktif),
            'total_aktif' => $totalAktif,
            'rows_pasif'  => $this->ratakan($pasif),
            'total_pasif' => $totalPasif,
            'grand_total' => $totalAktif + $totalPasif,
        ];

        return view('kalender::report-angka-cetak', compact('edisiList', 'selectedEdisi', 'report'));
    }

    // ------------------------------------------------------------------ biMBA Shop + Casdana

    /**
     * Qty item Kalender (tahun tertentu) dari order biMBA Shop yang sudah dibayar (Casdana SUCCESS / SETTLED).
     * Kode stokis dibaca dari potongan SKU (mis. KLD27-TNG1). Tanpa kode stokis dianggap Stokis Jakarta ('_JKT').
     * Rumah pasif Jakarta (JKTP, DLC, EBT, INV, BSS) tidak dihitung sebagai Unit Aktif.
     */
    private function qtyBimbaShop(int $tahun): array
    {
        $orders = BimbashopOrder::query()
            ->where(function ($q) {
                $q->where('item_sku', 'like', 'KAL%')
                  ->orWhere('item_sku', 'like', 'KLD%')
                  ->orWhere('item_name', 'like', '%kalender%');
            })
            ->where(function ($q) {
                $q->whereNull('status')->orWhere(function ($q2) {
                    $q2->where('status', 'not like', '%cancel%')
                       ->where('status', 'not like', '%fail%')
                       ->where('status', 'not like', '%refund%');
                });
            })
            ->get(['order_id', 'item_sku', 'item_name', 'item_qty']);

        $orders = $orders->filter(fn ($o) => ShopMatcher::tahunFrom($o->item_sku, $o->item_name) === (string) $tahun);

        if ($orders->isEmpty()) {
            return ['bayar' => [], 'semua' => []];
        }

        // Casdana yang sudah bayar. Nomor invoice bisa berawalan "ID", jadi dibandingkan tanpa awalan itu.
        $dibayar = [];
        CasdanaTransaction::query()
            ->whereIn(\DB::raw('UPPER(TRIM(status))'), ['SUCCESS', 'SETTLED'])
            ->pluck('invoice_number')
            ->each(function ($inv) use (&$dibayar) {
                $inv = trim((string) $inv);
                $dibayar[$inv] = true;
                $dibayar[preg_replace('/^ID/i', '', $inv)] = true;
            });

        $hasil = [];   // sudah dibayar (Casdana SUCCESS / SETTLED)
        $semua = [];   // seluruh order (bukan cancel / gagal / refund), dibayar atau belum

        foreach ($orders as $o) {
            $id = trim((string) $o->order_id);

            $token = preg_split('/[^A-Z0-9]+/', strtoupper((string) $o->item_sku), -1, PREG_SPLIT_NO_EMPTY);

            if (array_intersect($token, self::BUKAN_AKTIF)) {
                continue;
            }

            $kunci = '_JKT';
            foreach (array_merge(array_keys(self::STOKIS_PASIF), array_keys(self::STOKIS_AKTIF), array_keys(self::PENGELOLA)) as $kode) {
                if (in_array($kode, $token, true)) {
                    $kunci = $kode;
                    break;
                }
            }

            $qtyItem = max((int) $o->item_qty, 0);

            $semua[$kunci] = ($semua[$kunci] ?? 0) + $qtyItem;

            if (isset($dibayar[$id])) {
                $hasil[$kunci] = ($hasil[$kunci] ?? 0) + $qtyItem;
            }
        }

        return ['bayar' => $hasil, 'semua' => $semua];
    }

    // ------------------------------------------------------------------ pembentuk baris

    private function baris(string $kode, string $label, int $qty, ?string $catatan = null, ?int $semua = null): array
    {
        return ['kode' => $kode, 'label' => $label, 'qty' => $qty, 'semua' => $semua, 'anak' => [], 'catatan' => $catatan];
    }

    /** Baris induk: qty = jumlah anak. */
    private function induk(string $kode, string $label, array $anak, ?string $labelSubtotal = null): array
    {
        $semua = array_filter(array_column($anak, 'semua'), fn ($v) => $v !== null);

        return [
            'kode' => $kode, 'label' => $label, 'qty' => array_sum(array_column($anak, 'qty')),
            'semua' => $semua === [] ? null : (int) array_sum($semua), 'anak' => $anak, 'catatan' => null,
            'subtotal_label' => $labelSubtotal,   // baris ringkasan seperti di Excel: "Unit Aktif - Stokis Pasif :"
        ];
    }

    private function anak(array $daftar, array $shop, array $semua = []): array
    {
        $out = [];
        foreach ($daftar as $kode => $label) {
            $out[] = $this->baris($kode, $label, (int) ($shop[$kode] ?? 0), null, (int) ($semua[$kode] ?? 0));
        }

        return $out;
    }

    private function nol(array $daftar): array
    {
        $out = [];
        foreach ($daftar as $kode => $label) {
            $out[] = $this->baris($kode, $label, 0);
        }

        return $out;
    }

    /** Induk lalu anaknya, diberi 'level' untuk indentasi di view. */
    private function ratakan(array $baris): array
    {
        $out = [];
        foreach ($baris as $b) {
            $out[] = ['kode' => $b['kode'], 'label' => $b['label'], 'qty' => $b['qty'], 'semua' => $b['semua'] ?? null, 'level' => 0, 'induk' => ! empty($b['anak']), 'catatan' => $b['catatan']];

            if (! empty($b['subtotal_label'])) {
                $out[] = ['kode' => '', 'label' => $b['subtotal_label'], 'qty' => $b['qty'], 'semua' => $b['semua'] ?? null, 'level' => 0, 'induk' => false, 'catatan' => null, 'subtotal' => true];
            }

            foreach ($b['anak'] as $a) {
                $out[] = ['kode' => $a['kode'], 'label' => $a['label'], 'qty' => $a['qty'], 'semua' => $a['semua'] ?? null, 'level' => 1, 'induk' => false, 'catatan' => $a['catatan']];
            }
        }

        return $out;
    }
}
