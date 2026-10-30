<?php

namespace App\Modules\Kalender\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Kalender\Models\KalenderBacaanPesanan;
use App\Modules\Kalender\Models\KalenderDlcPesanan;
use App\Modules\Kalender\Models\KalenderPasifManualTransaksi;
use App\Modules\Kalender\Models\KalenderPasifPesanan;
use App\Modules\Kalender\Models\KalenderSparePasif;
use App\Modules\Kalender\Models\PesananKalender;
use App\Modules\Kalender\Models\PesananKalenderPuw1;
use App\Modules\Kalender\Models\PesananKalenderUnit;
use App\Modules\Kalender\Models\PesananKalenderUnitKotamadya;
use App\Modules\Kalender\Models\PesananKalenderUnitPuw1;
use Illuminate\Http\Request;

class KalenderReportAngkaCetakController extends Controller
{
    /**
     * Report Angka Cetak Kalender.
     * Kunci edisi = tahun kalender (contoh 2027): pesanan Unit Aktif dicocokkan lewat kolom "tahun",
     * sedangkan DLC / Unit Pasif / Bacaan / Pasif Manual lewat kolom "edisi".
     */
    public function reportAngkaCetak(Request $request)
    {
        $norm = fn ($v) => strtoupper(trim((string) $v));

        $edisiList = collect()
            ->merge(PesananKalender::whereNotNull('tahun')->pluck('tahun'))
            ->merge(PesananKalenderPuw1::whereNotNull('tahun')->pluck('tahun'))
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
                'edisiList'     => $edisiList,
                'selectedEdisi' => null,
                'report'        => null,
            ]);
        }

        $tahun = ctype_digit($selectedEdisi) ? (int) $selectedEdisi : null;

        // ---------------- Unit Aktif (KORWIL, PINWIL, JABODETABEK PUW1)
        $qtyKorwil = $tahun === null ? 0 : (int) PesananKalenderUnit::whereHas(
            'kabupaten.pesananKalender', fn ($q) => $q->where('tahun', $tahun)
        )->sum('jumlah_pesanan');

        $qtyPinwil = $tahun === null ? 0 : (int) PesananKalenderUnitKotamadya::whereHas(
            'pesananKalenderKotamadya.pesananKalender', fn ($q) => $q->where('tahun', $tahun)
        )->sum('jumlah_pesanan');

        $qtyPuw1 = $tahun === null ? 0 : (int) PesananKalenderUnitPuw1::whereHas(
            'pesananKalenderPuw1', fn ($q) => $q->where('tahun', $tahun)
        )->sum('jumlah_pesanan');

        $rowsAktif = [
            ['kode' => 'A1', 'label' => 'PUW - KORWIL',            'qty' => $qtyKorwil],
            ['kode' => 'A2', 'label' => 'PUW - PINWIL',            'qty' => $qtyPinwil],
            ['kode' => 'A3', 'label' => 'PUW - JABODETABEK (PUW1)', 'qty' => $qtyPuw1],
        ];
        $totalAktif = array_sum(array_column($rowsAktif, 'qty'));

        // ---------------- Unit Pasif
        $periodeEdisi = fn ($q) => $q->where('status', 'aktif')->whereRaw('UPPER(TRIM(edisi)) = ?', [$selectedEdisi]);

        $qtyDlc    = (int) KalenderDlcPesanan::whereHas('periode', $periodeEdisi)->sum('qty');
        $qtyPasif  = (int) KalenderPasifPesanan::whereHas('periode', $periodeEdisi)->sum('qty');
        $qtyBacaan = (int) KalenderBacaanPesanan::whereHas('periode', $periodeEdisi)->sum('bacaan_unit');
        $qtyManual = (int) KalenderPasifManualTransaksi::whereHas('periode', $periodeEdisi)->sum('jumlah');
        $qtySpare  = (int) KalenderSparePasif::where('status', 'aktif')
            ->whereRaw('UPPER(TRIM(edisi)) = ?', [$selectedEdisi])
            ->sum('spare');

        $rowsPasif = [
            ['kode' => 'P1', 'label' => 'DLC',                   'qty' => $qtyDlc],
            ['kode' => 'P2', 'label' => 'Unit Pasif',            'qty' => $qtyPasif],
            ['kode' => 'P3', 'label' => 'Unit Pasif (Bacaan)',   'qty' => $qtyBacaan],
            ['kode' => 'P4', 'label' => 'Pasif Manual',          'qty' => $qtyManual],
            ['kode' => 'P5', 'label' => 'Spare Unit Pasif 3%',   'qty' => $qtySpare],
        ];
        $totalPasif = array_sum(array_column($rowsPasif, 'qty'));

        $report = [
            'edisi'       => $selectedEdisi,
            'rows_aktif'  => $rowsAktif,
            'total_aktif' => $totalAktif,
            'rows_pasif'  => $rowsPasif,
            'total_pasif' => $totalPasif,
            'grand_total' => $totalAktif + $totalPasif,
        ];

        return view('kalender::report-angka-cetak', compact('edisiList', 'selectedEdisi', 'report'));
    }
}
