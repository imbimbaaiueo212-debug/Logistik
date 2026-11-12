<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Import\Models\BimbashopOrder;
use App\Modules\Import\Services\ShopMatcher;
use App\Modules\Import\Services\ShopStatusSync;
use App\Modules\Kalender\Models\KalenderDlcPesanan;
use App\Modules\Kalender\Models\KalenderPasifPesanan;
use App\Modules\Kalender\Models\KalenderSpareManual;
use App\Modules\Kalender\Support\KodeKalender;
use App\Modules\OrderManual\Models\ManualCetakOrder;
use App\Modules\OrderManual\Services\CetakShopMatcher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Report Angka Cetak Kalender, mengikuti susunan Excel "Report Angka Cetak" (rumus SUMIFS).
 *
 *  Kode A (Unit Aktif) = qty order Kalender di biMBA Shop berstatus processing (sudah dibayar), per kode stokis di SKU,
 *                        dipecah menurut tanggal order ke kolom Periode 1 / Periode 2.
 *  SOUV                = PUW (A1), tanpa pembayaran.
 *  Unit Pasif (P)      = ada di biMBA Shop tanpa Casdana, masuk kolom Periode (bukan SOUV).
 *  Order Shop          = seluruh order Kalender di biMBA Shop (bukan batal / gagal / refund).
 *  Selisih             = Order Shop dikurangi (Periode 1 + Periode 2).
 *
 * Sebelum dihitung, order Shop yang masih on-hold tetapi sudah SETTLED / SUCCESS di Casdana
 * otomatis diubah menjadi processing (ShopStatusSync), jadi angka tidak tertahan di on-hold.
 */
class KalenderReportAngkaCetakController extends Controller
{
    /** Kode stokis (token SKU) per kelompok, sesuai Excel. */
    private const STOKIS_PASIF = [
        'SMG' => 'Semarang', 'SBY' => 'Surabaya', 'SGN' => 'Sragen', 'YYK' => 'Yogyakarta',
    ];

    private const STOKIS_AKTIF = [
        'TNG1' => 'Tangerang 1', 'BKS' => 'Bekasi 1', 'SRG1' => 'Serang 1',
        'TNG2' => 'Tangerang 2', 'BGR1' => 'Bogor 1', 'BGR2' => 'Bogor 2',
        'DPK' => 'Depok 1', 'SKB1' => 'Sukabumi 1', 'KNG' => 'Kuningan 1',
        'IDM' => 'Indramayu 1', 'SKB2' => 'Sukabumi 2', 'SNG' => 'Subang 1',
        'KWG1' => 'Karawang 1', 'PWK' => 'Purwakarta 1', 'BDG1' => 'Bandung 1',
        'BDG2' => 'Bandung 2', 'CIL1' => 'Cilincing 1', 'SRG2' => 'Serang 2',
        'DPR1' => 'Denpasar 1', 'KWG2' => 'Karawang 2', 'BGR3' => 'Bogor 3',
        'YYK1' => 'Yogyakarta 1', 'CRB1' => 'Cirebon 1',
        // ada di sync Jakarta tetapi tidak di Excel: dimasukkan agar tidak salah masuk ke Stokis Jakarta
        'TNG3' => 'Tangerang 3', 'PKL1' => 'Pekalongan 1',
    ];

    private const PENGELOLA = [
        'PUA1' => 'Pengelola Unit Aktif Khusus 1', 'PUA2' => 'Pengelola Unit Aktif Khusus 2', 'PUA3' => 'Pengelola Unit Aktif Khusus 3',
    ];

    /** Rumah Jakarta pasif: bukan Unit Aktif, tidak dihitung di kode A. */
    private const BUKAN_AKTIF = ['JKTP', 'DLC', 'EBT', 'INV', 'BSS'];

    /** Status Shop yang dianggap sudah dibayar. */
    private const STATUS_DIBAYAR = ['processing', 'completed'];

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

        // Rentang tanggal order Periode 1 dan Periode 2 (boleh kosong)
        $p1 = $this->rentang($request->get('p1_dari'), $request->get('p1_sampai'));
        $p2 = $this->rentang($request->get('p2_dari'), $request->get('p2_sampai'));

        $filter = [
            'p1_dari' => $request->get('p1_dari'), 'p1_sampai' => $request->get('p1_sampai'),
            'p2_dari' => $request->get('p2_dari'), 'p2_sampai' => $request->get('p2_sampai'),
            'semua_tanggal' => $p1 === null && $p2 === null,
        ];

        if ($selectedEdisi === '') {
            return view('kalender::report-angka-cetak', [
                'edisiList' => $edisiList, 'selectedEdisi' => null, 'report' => null, 'filter' => $filter,
            ]);
        }

        // On-hold di biMBA Shop yang sudah SETTLED / SUCCESS di Casdana -> processing
        $dinaikkan = 0;
        try {
            $dinaikkan = ShopStatusSync::naikkanOnHold();
        } catch (\Throwable $e) {
            Log::error('Report Angka Cetak Kalender: sync on-hold gagal: ' . $e->getMessage());
        }

        $tahun  = KodeKalender::tahun($selectedEdisi);
        $varian = KodeKalender::varian($selectedEdisi);
        $in     = implode(',', array_fill(0, count($varian), '?'));

        $periodeEdisi = fn ($q) => $q->where('status', 'aktif')->whereRaw("UPPER(TRIM(edisi)) IN ($in)", $varian);

        // ======================= UNIT AKTIF (kode A): biMBA Shop =======================
        $hasil = $tahun === null
            ? ['qty' => [], 'semua' => [], 'lg' => ['qty' => [], 'semua' => []], 'belum_bayar' => 0]
            : $this->qtyBimbaShop($tahun, $p1, $p2);
        $lg = $hasil['lg'] ?? ['qty' => [], 'semua' => []];

        $qty   = $hasil['qty'];     // [token => ['p1' => n, 'p2' => n]] order dibayar (processing / completed)
        $semua = $hasil['semua'];   // [token => n] seluruh order Shop


        // Create Manual Kalender unit AKTIF TIDAK masuk SOUV.
        //  - Sudah ada di biMBA Shop + Kasdana -> sudah terhitung lewat order Shop (processing), tidak dihitung lagi.
        //  - Belum lengkap -> tetap on-hold, belum dihitung.
        // Unit PASIF: Create Manual pasif tidak lagi ditampilkan sebagai baris sendiri (sudah masuk pos masing-masing).
        $manualMenunggu = 0;
        $cetakPasif     = 0;
        try {
            app(CetakShopMatcher::class)->sync('kalender');

            $cetak = ManualCetakOrder::where('jenis', 'kalender')
                ->whereRaw("UPPER(TRIM(edisi)) IN ($in)", $varian)
                ->get(['status_unit', 'status', 'qty']);

            foreach ($cetak as $c) {
                if ($c->status_unit === 'pasif') {
                    $cetakPasif += (int) $c->qty;
                } elseif (! in_array($c->status, CetakShopMatcher::STATUS_SELESAI, true)) {
                    $manualMenunggu += (int) $c->qty;   // aktif, masih on-hold
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Report Angka Cetak Kalender: manual_cetak_orders belum bisa dibaca: ' . $e->getMessage());
        }

        $barisJakarta = $this->shopBaris('A', 'Unit Aktif - Stokis Jakarta', '_JKT', $qty, $semua);

        $aktif = [
            $barisJakarta,
            // PUW1 sementara 0 (di Excel 0), data PUW belum dihitung ke Report Angka Cetak
            $this->baris('A1', 'PUW1 - Stokis Logistik', 0, 0, 0),
            $this->induk('A2', 'Stokis Pasif - Logistik', $this->anak(self::STOKIS_PASIF, $qty, $semua, $lg), 'Unit Aktif - Stokis Pasif'),
            $this->induk('A3', 'Stokis Aktif - Logistik', $this->anak(self::STOKIS_AKTIF, $qty, $semua, $lg), 'Unit Aktif - Stokis Aktif'),
            $this->induk('A4', 'Pengelola Unit Aktif Khusus', $this->anak(self::PENGELOLA, $qty, $semua)),
        ];

        // ======================= UNIT PASIF (kode P): kolom Periode =======================
        $qtyMurid  = (int) KalenderPasifPesanan::whereHas('periode', $periodeEdisi)->sum('qty');
        // DLC = kode SKU untuk Murid InterVio (P1): nilainya di baris P1, dirinci per unit
        $dlcPerUnit = KalenderDlcPesanan::whereHas('periode', $periodeEdisi)->get(['nama_unit', 'qty'])
            ->groupBy(fn ($r) => strtoupper(trim((string) $r->nama_unit)))
            ->map(fn ($g) => (int) $g->sum('qty'));
        $qtyDlc = (int) $dlcPerUnit->sum();
        $qtyHumas  = (int) KalenderPasifPesanan::whereHas('periode', $periodeEdisi)->sum('humas');
        // Motivator = kolom RELAWAN di Daftar Pasif (bacaan_unit); tiap relawan dapat 2 kalender (RELAWAN @2 exp)
        $qtyMotivator = (int) KalenderPasifPesanan::whereHas('periode', $periodeEdisi)->sum('bacaan_unit') * 2;
        $qtySpare  = (int) KalenderSpareManual::whereRaw("UPPER(TRIM(edisi)) IN ($in)", $varian)->sum('qty');

        // Manajemen biMBA (P6) = pesanan Create Manual Kalender berstatus Pasif (mis. MK-20261111-0001 JAKARTA PASIF),
        // diambil langsung dari Create Manual Kalender ($cetakPasif), bukan angka tetap.
        $pasif = [
            $this->induk('P', 'biMBA Unit Pasif', [
                $this->pasif('P', 'Murid', $qtyMurid),
                $this->pasif('P', 'Motivator Unit', $qtyMotivator),
                $this->pasif('P', 'Cabang biMBA (Humas + Pajangan)', $qtyHumas),
                $this->pasif('P', 'Spare Untuk Murid Pasif', $qtySpare),
            ]),
            $this->indukBernilai(
                $this->induk('P1', 'Murid InterVio', $this->dlcAnak($dlcPerUnit, ['P1.1' => 'Anggrek 1', 'P1.2' => 'Anggrek 2', 'P1.3' => 'Anggrek 3', 'P1.4' => 'Anggrek 4', 'P1.5' => 'Anggrek 5', 'P1.6' => 'Dhuafa', 'P1.7' => 'Khusus'])),
                $qtyDlc
            ),
            $this->induk('P3', 'Kantor biMBA', $this->nol(['P3.1' => 'Kantor Pusat', 'P3.2' => 'Kantor Wilayah'])),
            $this->induk('P4', 'Relawan Pembelajar biMBA', $this->nol(['P4.1' => 'Relawan WFO - Kantor', 'P4.2' => 'Relawan WFH - Rumah'])),
            $this->induk('P5', 'biMBA SS', $this->nol(['P5.1' => 'Murid SS', 'P5.2' => 'Official + Pelatih', 'P5.3' => 'Relawan SS', 'P5.4' => 'Relasi biMBA SS'])),
            $this->indukBernilai($this->induk('P6', 'Manajemen biMBA', $this->nol(['P6.B1' => 'biMBA 1', 'P6.B2' => 'biMBA 2', 'P6.B3' => 'biMBA 3', 'P6.B4' => 'biMBA 4', 'P6.B5' => 'biMBA 5', 'P6.B6' => 'biMBA 6'])), $cetakPasif),
            // Souvenir unit yang order ke Jakarta (cek 3 bulan terakhir oleh tim): menunggu aturan hitung
            $this->baris('P8', 'Souvenir Unit ke Jakarta', 0, 0, 0, null, 'belum dihitung, menunggu aturan'),
        ];

        $totalAktif = array_sum(array_column($aktif, 'total'));
        $totalPasif = array_sum(array_column($pasif, 'total'));

        $report = [
            'edisi'       => $selectedEdisi,
            'rows_aktif'  => $this->ratakan($aktif),
            'rows_pasif'  => $this->ratakan($pasif),
            'total_aktif' => $totalAktif,
            'total_pasif' => $totalPasif,
            'grand_total' => $totalAktif + $totalPasif,
            'dinaikkan'   => $dinaikkan,              // baris on-hold -> processing pada pemuatan ini
            'manual_menunggu' => $manualMenunggu,     // Create Manual aktif yang masih on-hold (belum dihitung)
        ];

        return view('kalender::report-angka-cetak', compact('edisiList', 'selectedEdisi', 'report', 'filter'));
    }

    // ------------------------------------------------------------------ biMBA Shop

    /**
     * Qty item Kalender (tahun tertentu) dari order biMBA Shop.
     * Kode stokis dibaca dari potongan SKU (mis. KLD27-TNG1). Stokis Jakarta ('_JKT') hanya SKU yang memuat kode JKT (KLD27-JKT); SKU tanpa kode dikenal diabaikan.
     * Rumah pasif Jakarta (JKTP, DLC, EBT, INV, BSS) tidak dihitung sebagai Unit Aktif.
     *
     * Order yang belum dibayar (on-hold / pending) tidak dihitung. Pengecualian COD / wallet / other
     * (dibayar di luar Casdana) HANYA berlaku untuk yang bukan stokis terdaftar (mis. Jakarta);
     * stokis terdaftar (aktif, pasif, pengelola) wajib berstatus processing / completed.
     *
     * @return array{qty: array<string, array{p1:int,p2:int}>, semua: array<string,int>, belum_bayar: int}
     */
    private function qtyBimbaShop(int $tahun, ?array $p1, ?array $p2): array
    {
        $baris = BimbashopOrder::query()
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
            ->get(['order_id', 'order_date', 'status', 'payment_method', 'billing_first_name', 'billing_last_name', 'billing_company', 'item_sku', 'item_name', 'item_qty']);

        $baris = $baris->filter(fn ($o) => ShopMatcher::tahunFrom($o->item_sku, $o->item_name) === (string) $tahun);

        $semuaTanggal = $p1 === null && $p2 === null;
        $kodeStokis   = array_merge(array_keys(self::STOKIS_PASIF), array_keys(self::STOKIS_AKTIF), array_keys(self::PENGELOLA));

        $qty = [];
        $semua = [];
        // Pesanan ber-SKU LG (order ke logistik) dikenali lewat nama billing "Stokis ...".
        // Disimpan TERPISAH dari order SKU stokis sendiri; hanya dipakai bila stokis tidak punya order sendiri.
        $lg = ['qty' => [], 'semua' => []];
        $belumBayar = 0;

        // Nama stokis (label terpanjang dulu agar "Yogyakarta 1" didahulukan dari "Yogyakarta")
        $namaStokis = self::STOKIS_PASIF + self::STOKIS_AKTIF;
        uasort($namaStokis, fn ($a, $b) => strlen($b) <=> strlen($a));

        foreach ($baris as $o) {
            $token = preg_split('/[^A-Z0-9]+/', strtoupper((string) $o->item_sku), -1, PREG_SPLIT_NO_EMPTY);

            if (array_intersect($token, self::BUKAN_AKTIF)) {
                continue;
            }

            $kunci = null;
            foreach ($kodeStokis as $kode) {
                if (in_array($kode, $token, true)) {
                    $kunci = $kode;
                    break;
                }
            }

            // Hanya SKU persis KLDxx-LG (order di logistik): stokis dikenali dari nama billing "Stokis <nama>",
            // mis. "Stokis Sukabumi 2 2992.S" -> SKB2. Bila nama billing tidak cocok, tidak dimasukkan.
            $viaLg = false;
            if ($kunci === null && $this->skuLogistik($token)) {
                $kunci = $this->stokisDariNama($o, $namaStokis);
                $viaLg = $kunci !== null;
            }

            // Stokis Jakarta hanya SKU ber-kode JKT (mis. KLD27-JKT).
            // SKU lain yang tidak dikenal (LG, PUW, dll) tidak lagi ikut tertarik ke Jakarta.
            if ($kunci === null) {
                if (! in_array('JKT', $token, true)) {
                    continue;
                }
                $kunci = '_JKT';
            }

            $jumlah = max((int) $o->item_qty, 0);
            $status = preg_replace('/^wc-/', '', strtolower(trim((string) $o->status)));

            // On-hold / pending (belum dibayar) tidak dimasukkan sama sekali ke report,
            // termasuk kolom Order Shop. On-hold yang sudah SETTLED/SUCCESS sudah jadi processing lewat ShopStatusSync.
            // Pengecualian: COD / wallet / other dibayar di luar Casdana, tetap dihitung,
            // tetapi TIDAK untuk stokis terdaftar (aktif / pasif / pengelola): mereka wajib berstatus dibayar.
            $dibayar         = in_array($status, self::STATUS_DIBAYAR, true);
            $stokisTerdaftar = $kunci !== '_JKT';

            if (! $dibayar) {
                if ($stokisTerdaftar || ! CetakShopMatcher::bayarTanpaCasdana($o->payment_method)) {
                    $belumBayar += $jumlah;

                    continue;
                }
            }

            $kolom = null;
            if ($semuaTanggal) {
                $kolom = 'p1';
            } elseif ($this->dalamRentang($o->order_date, $p1)) {
                $kolom = 'p1';
            } elseif ($this->dalamRentang($o->order_date, $p2)) {
                $kolom = 'p2';
            }

            if ($viaLg) {
                $lg['semua'][$kunci] = ($lg['semua'][$kunci] ?? 0) + $jumlah;
                if ($kolom !== null) {
                    $lg['qty'][$kunci][$kolom] = ($lg['qty'][$kunci][$kolom] ?? 0) + $jumlah;
                }

                continue;   // tidak digabung ke order SKU stokis sendiri
            }

            $semua[$kunci] = ($semua[$kunci] ?? 0) + $jumlah;

            if ($kolom !== null) {
                $qty[$kunci][$kolom] = ($qty[$kunci][$kolom] ?? 0) + $jumlah;
            }
        }

        return ['qty' => $qty, 'semua' => $semua, 'lg' => $lg, 'belum_bayar' => $belumBayar];
    }

    /** SKU persis KLDxx-LG (dua potongan saja: edisi kalender + LG). */
    private function skuLogistik(array $token): bool
    {
        return count($token) === 2 && $token[1] === 'LG' && preg_match('/^KLD\d{2}$/', $token[0]) === 1;
    }

    /** Kode stokis dari nama billing berpola "Stokis <nama stokis>", atau null. */
    private function stokisDariNama($o, array $daftar): ?string
    {
        $nama = strtoupper(trim(implode(' ', array_filter([
            $o->billing_first_name, $o->billing_last_name, $o->billing_company,
        ]))));

        if ($nama === '') {
            return null;
        }

        foreach ($daftar as $kode => $label) {
            $pola = str_replace(' ', '\\s*', preg_quote(strtoupper($label), '/'));
            if (preg_match('/(?<![A-Z0-9])STOKIS\s+' . $pola . '(?![A-Z0-9])/', $nama)) {
                return $kode;
            }
        }

        return null;
    }

    /** @return array{0:Carbon|null,1:Carbon|null}|null  null bila kedua tanggal kosong / tidak valid */
    private function rentang($dari, $sampai): ?array
    {
        $parse = function ($v, bool $akhir) {
            if (blank($v)) {
                return null;
            }
            try {
                $c = Carbon::parse($v);

                return $akhir ? $c->endOfDay() : $c->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        };

        $a = $parse($dari, false);
        $b = $parse($sampai, true);

        return ($a === null && $b === null) ? null : [$a, $b];
    }

    private function dalamRentang($tanggal, ?array $rentang): bool
    {
        if ($rentang === null || $tanggal === null) {
            return false;
        }

        $t = $tanggal instanceof Carbon ? $tanggal : Carbon::parse($tanggal);

        return ($rentang[0] === null || $t->gte($rentang[0])) && ($rentang[1] === null || $t->lte($rentang[1]));
    }

    // ------------------------------------------------------------------ pembentuk baris

    private function baris(string $kode, string $label, int $p1, int $p2 = 0, int $souv = 0, ?int $shop = null, ?string $catatan = null): array
    {
        return [
            'kode' => $kode, 'label' => $label, 'p1' => $p1, 'p2' => $p2, 'souv' => $souv,
            'total' => $p1 + $p2 + $souv, 'shop' => $shop, 'lg' => null, 'anak' => [], 'catatan' => $catatan,
        ];
    }

    /** Unit Pasif: ada di biMBA Shop tetapi tanpa Casdana, jadi masuk kolom Periode (bukan SOUV). */
    private function pasif(string $kode, string $label, int $qty): array
    {
        return $this->baris($kode, $label, $qty, 0, 0);
    }

    private function souv(string $kode, string $label, int $souv): array
    {
        return $this->baris($kode, $label, 0, 0, $souv);
    }

    /** Baris yang nilainya dari order biMBA Shop (kolom Periode + Order Shop). */
    private function shopBaris(string $kode, string $label, string $kunci, array $qty, array $semua): array
    {
        return $this->baris(
            $kode, $label,
            (int) ($qty[$kunci]['p1'] ?? 0), (int) ($qty[$kunci]['p2'] ?? 0), 0,
            (int) ($semua[$kunci] ?? 0)
        );
    }

    /** Baris induk: nilai = jumlah anak. */
    private function induk(string $kode, string $label, array $anak, ?string $labelSubtotal = null): array
    {
        $shop = array_filter(array_column($anak, 'shop'), fn ($v) => $v !== null);

        return [
            'kode' => $kode, 'label' => $label,
            'p1' => array_sum(array_column($anak, 'p1')),
            'p2' => array_sum(array_column($anak, 'p2')),
            'souv' => array_sum(array_column($anak, 'souv')),
            'total' => array_sum(array_column($anak, 'total')),
            'shop' => $shop === [] ? null : (int) array_sum($shop),
            'lg' => ($lgAnak = array_filter(array_column($anak, 'lg'), fn ($v) => $v !== null)) === [] ? null : (int) array_sum($lgAnak),
            'anak' => $anak, 'catatan' => null,
            'subtotal_label' => $labelSubtotal,   // baris ringkasan seperti di Excel: "Unit Aktif - Stokis Pasif"
        ];
    }

    private function anak(array $daftar, array $qty, array $semua, array $lg = []): array
    {
        $out = [];
        foreach ($daftar as $kode => $label) {
            $b = $this->shopBaris($kode, $label, $kode, $qty, $semua);

            // Order ber-SKU LG (dikenali dari nama billing "Stokis ...") adalah pesanan yang SAMA dengan order SKU stokis
            // (tercatat dua kali), jadi tidak dijumlahkan. Dipakai angka yang lebih besar, sesuai Excel:
            // stokis tanpa order sendiri (mis. Sukabumi 2) atau yang order sendirinya kurang (mis. Bogor 3) memakai angka LG.
            $lgP1    = (int) ($lg['qty'][$kode]['p1'] ?? 0);
            $lgP2    = (int) ($lg['qty'][$kode]['p2'] ?? 0);
            $lgSemua = (int) ($lg['semua'][$kode] ?? 0);

            $b['p1']    = max($b['p1'], $lgP1);
            $b['p2']    = max($b['p2'], $lgP2);
            $b['total'] = $b['p1'] + $b['p2'] + $b['souv'];
            $b['shop']  = max((int) $b['shop'], $lgSemua);
            $b['lg']    = $lgSemua;   // angka dari SKU LG, hanya sebagai keterangan

            $out[] = $b;
        }

        return $out;
    }

    /**
     * Induk dengan nilai sendiri (total seluruh data), anak hanya rincian.
     * Grand total dihitung dari baris induk saja, jadi anak tidak terhitung dua kali.
     */
    private function indukBernilai(array $induk, int $qty): array
    {
        $induk['p1']    = $qty;
        $induk['total'] = $qty;

        return $induk;
    }

    /** Rincian per nama unit (cocok menurut label, tanpa membedakan huruf besar/kecil). */
    private function dlcAnak($perUnit, array $daftar): array
    {
        $out = [];
        foreach ($daftar as $kode => $label) {
            $out[] = $this->baris($kode, $label, (int) ($perUnit[strtoupper($label)] ?? 0));
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
            $adaAnak = ! empty($b['anak']);
            $subtotal = $adaAnak && ! empty($b['subtotal_label']);

            $out[] = [
                'kode' => $b['kode'],
                'label' => $subtotal ? $b['subtotal_label'] : $b['label'],
                'p1' => $b['p1'], 'p2' => $b['p2'], 'souv' => $b['souv'], 'total' => $b['total'],
                'shop' => $b['shop'] ?? null, 'lg' => $b['lg'] ?? null,
                'level' => 0, 'induk' => $adaAnak, 'subtotal' => $subtotal, 'catatan' => $b['catatan'],
            ];

            foreach ($b['anak'] as $a) {
                $out[] = [
                    'kode' => $a['kode'], 'label' => $a['label'],
                    'p1' => $a['p1'], 'p2' => $a['p2'], 'souv' => $a['souv'], 'total' => $a['total'],
                    'shop' => $a['shop'] ?? null, 'lg' => $a['lg'] ?? null,
                    'level' => 1, 'induk' => false, 'subtotal' => false, 'catatan' => $a['catatan'],
                ];
            }
        }

        return $out;
    }
}