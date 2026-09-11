<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Picking - Manual Modul</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.2px;
            color: #1f2937;
            margin: 0;
            padding: 8px;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #0000003d;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #ffffff;
            color: #111827;
            padding: 6px 2px;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            line-height: 1.25;
        }

        td {
            padding: 4px 2px;
            font-size: 9px;
            color: #374151;
            line-height: 1.3;
        }

        tbody tr:nth-child(even) { background: #fafafa; }

        .header1 th {
            background: #ffffff;
            font-size: 10px;
            padding: 7px 2px;
        }

        .header2 th {
            background: #ffffff;
            font-size: 9.2px;
            padding: 5px 2px;
        }

        .col-no       { width: 3%; }
        .col-id       { width: 8%; }
        .col-unit     { width: 12%; }
        .col-kategori { width: 18%; }
        .col-bayar    { width: 10%; }
        .col-estimasi { width: 8%; }
        .col-pic      { width: 9%; }
        .col-catatan  { width: 32%; }

        .text-left   { text-align: left; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8.8px;
            color: #6b7280;
        }

        thead { display: table-header-group; }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    @php
    $firstItem = $data->first();
    $rekapNo   = $firstItem?->rekap_number ?? '#0001';
    
    // Waktu cetak = waktu sekarang (saat diklik)
    $printTime = now();
@endphp

{{-- ================= HEADER ================= --}}
<table style="width:100%; border:none; margin-bottom:12px;">
    <tr>
        <th colspan="8" style="border:none; padding:0;">
            <table style="width:100%; border:none;">
                <tr>
                    <td style="width:75%; text-align:center; font-size:15px; font-weight:bold; color:#000; border:none;">
                        Rekap Aktual Detail - Picking Manual Modul
                        <span style="color:#000; font-weight:bold;">| MODUL</span>
                        <span style="color:#000;">{{ $rekapNo }}</span>
                    </td>
                    <td style="width:25%; text-align:center; border:none;">
                        <div style="font-size:10.5px; color:#000;">Waktu Serah Terima</div>
                        <div style="font-size:11px; font-weight:bold;">
                            {{ $printTime->format('d/m/Y H:i:s') }}
                        </div>
                    </td>
                </tr>
            </table>
        </th>
    </tr>
</table>

    {{-- ================= TABEL UTAMA ================= --}}
    <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
        <colgroup>
            <col class="col-no">
            <col class="col-id">
            <col class="col-unit">
            <col class="col-kategori">
            <col class="col-bayar">
            <col class="col-estimasi">
            <col class="col-pic">
            <col class="col-catatan">
        </colgroup>

        <thead>
            <tr class="header1">
                <th rowspan="2" class="col-no">NO</th>
                <th colspan="3">DETAIL ORDER</th>
                <th rowspan="2" class="col-bayar">WAKTU BAYAR</th>
                <th rowspan="2" class="col-estimasi">Leadtime</th>
                <th rowspan="2" class="col-pic">PIC</th>
                <th rowspan="2" class="col-catatan">CATATAN</th>
            </tr>
            <tr class="header2">
                <th class="col-id">ID ORDER</th>
                <th class="col-unit">NAMA UNIT</th>
                <th class="col-kategori">KATEGORI</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="col-no font-bold text-center">{{ $loop->iteration }}</td>

                <td class="col-id text-center">{{ $item->no_pl ?? '-' }}</td>

                <td class="col-unit text-left">{{ $item->nama_unit ?? '-' }}</td>

                <td class="col-kategori text-center">
                        @php
                            $sku = trim($item->product_sku ?? '');
                            $kat = trim($item->kategori_order ?? 'Modul');

                            // Format: Modul - B1A
                            if ($sku !== '') {
                                $kategoriDisplay = $kat . ' - ' . $sku;
                            } else {
                                $kategoriDisplay = $kat;
                            }
                        @endphp
                        {{ $kategoriDisplay }}
                    </td>

                <td class="col-bayar text-center">
                    @php
                        $tglBayar = $item->payment_date
                                 ?? $item->tgl_bayar
                                 ?? $item->order_date
                                 ?? null;
                    @endphp
                    {{ $tglBayar ? \Carbon\Carbon::parse($tglBayar)->format('d/m/Y H:i') : '-' }}
                </td>

                <td class="col-estimasi text-center">
                @php
                    $tglBayar = $item->payment_date
                            ?? $item->tgl_bayar
                            ?? $item->order_date
                            ?? null;

                    $tglEstimasi = $item->tgl_estimasi
                                ?? $item->waktu_estimasi_persiapan
                                ?? null;

                    $estimasiHari = null;

                    // Helper: hitung selisih hari kerja (skip Minggu)
                    $hitungHariKerja = function (\Carbon\Carbon $start, \Carbon\Carbon $end) {
                        $start = $start->copy()->startOfDay();
                        $end   = $end->copy()->startOfDay();

                        // kalau end < start, tukar arah biar ga infinite loop / minus salah
                        $reverse = false;
                        if ($end->lt($start)) {
                            [$start, $end] = [$end, $start];
                            $reverse = true;
                        }

                        $hari = 0;
                        while ($start->lt($end)) {
                            $start->addDay();
                            if (!$start->isSunday()) {
                                $hari++;
                            }
                        }

                        return $reverse ? -$hari : $hari;
                    };

                    // Kalau belum ada di DB, hitung manual (sama seperti Controller)
                    if (!$tglEstimasi && $tglBayar) {
                        try {
                            $base = \Carbon\Carbon::parse($tglBayar);

                            // Estimasi print PL
                            $estimasiPrint = $base->copy();
                            if ($base->hour >= 12) {
                                $estimasiPrint->addDay();
                            }

                            // Lewati Minggu (dan libur kalau ada)
                            while ($estimasiPrint->isSunday()) {
                                $estimasiPrint->addDay();
                            }

                            // +2 hari kerja (skip Minggu)
                            $tglEstimasi = $estimasiPrint->copy();
                            $added = 0;
                            while ($added < 2) {
                                $tglEstimasi->addDay();
                                if ($tglEstimasi->isSunday()) {
                                    continue;
                                }
                                $added++;
                            }

                            // Selisih hari KERJA (skip Minggu), bukan hari kalender
                            $estimasiHari = $hitungHariKerja($base, $tglEstimasi);

                        } catch (\Exception $e) {
                            $tglEstimasi = null;
                        }
                    } elseif ($tglEstimasi && $tglBayar) {
                        try {
                            $estimasiHari = $hitungHariKerja(
                                \Carbon\Carbon::parse($tglBayar),
                                \Carbon\Carbon::parse($tglEstimasi)
                            );
                        } catch (\Exception $e) {
                            $estimasiHari = null;
                        }
                    }
                @endphp

                @if($tglEstimasi)
                    {{ \Carbon\Carbon::parse($tglEstimasi)->format('d/m/Y') }}
                    @if($estimasiHari !== null)
                        <br><span style="font-size:8.5px;">{{ $estimasiHari }} Hari</span>
                    @endif
                @else
                    -
                @endif
                </td>   

                <td class="col-pic text-center"></td>

                <td class="col-catatan text-left" style="font-size:8.8px;">
                    @php
                        $catatan = $item->catatan
                                ?? $item->order_catatan
                                ?? $item->ket
                                ?? '';
                        $catatan = preg_replace(
                            '/Di proses bulk pada \d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\s*/i',
                            '',
                            $catatan
                        );
                        echo \Illuminate\Support\Str::limit(trim($catatan), 75) ?: '-';
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
    Dicetak oleh : PICKING • {{ $printTime->format('d/m/Y H:i:s') }}
    &nbsp;|&nbsp; Total: {{ $data->count() }} order
</div>

</body>
</html>