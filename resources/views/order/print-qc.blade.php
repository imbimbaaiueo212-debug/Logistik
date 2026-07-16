<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual QC OUTGOING</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
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

        th {
            background: #e8eef7;
            color: #111827;
            border: 1px solid #0000003d;
            padding: 6px 5px;
            text-align: center;
            vertical-align: middle;
            font-size: 9.5px;
            font-weight: bold;
            line-height: 1.25;
        }

        td {
            border: 1px solid #0000003d;
            padding: 4px 5px;
            font-size: 9px;
            color: #374151;
            vertical-align: top;
            line-height: 1.3;
            word-wrap: break-word;
        }

        /* Zebra */
        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        /* Header Group */
        .header1 th {
            background: #dbeafe;
            font-size: 10px;
            padding: 7px 5px;
        }

        .header2 th {
            background: #eff6ff;
            font-size: 9.2px;
            padding: 5px 4px;
        }

        .header-cell {
            padding: 6px 5px;
            vertical-align: middle;
            text-align: center;
        }

        /* Column Width */
        .col-no       { width: 32px; }
        .col-id       { width: 78px; }
        .col-unit     { width: 160px; }
        .col-kategori { width: 110px; }
        .col-estimasi { width: 85px; }
        .col-kode     { width: 65px; }
        .col-hasilcek { width: 75px; }
        .col-ceklist  { width: 45px; }
        .col-catatan  { width: 145px; }

        .text-left  { text-align: left; }
        .text-right { text-align: right; }
        .font-bold  { font-weight: bold; }

        /* Footer */
        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8.8px;
            color: #6b7280;
        }

        /* ===== PENTING: REPEAT HEADER DI SETIAP HALAMAN ===== */
        thead {
            display: table-header-group;
        }
    </style>
</head>
<body>

    @php
        $firstItem = $data->first();
        $stokisName = $firstItem?->nama_stokis ?? 'STOKIS JAKARTA AKTIF';
        $rekapNo    = $firstItem?->rekap_number ?? '#0001';
        $firstDate  = $data->min('created_at') ?? $data->min('tgl_turun_pl');
    @endphp

    <!-- ================= HEADER UTAMA (Hanya halaman pertama) ================= -->
    <table style="width:100%; border:none; margin-bottom:12px;">
        <tr>
            <th colspan="9" style="border:none; padding:0;">
                <table style="width:100%; border:none;">
                    <tr>
                        <td style="width:75%; text-align:center; font-size:15px; font-weight:bold; color:#1e3a8a;">
                            Rekap Aktual Detail - QC OUTGOING {{ $stokisName }}
                            <span style="color:#4f46e5;">{{ $rekapNo }}</span>
                        </td>
                        <td style="width:25%; text-align:center;">
                            @if($firstDate)
                                <div style="font-size:10.5px; color:#64748b;">Waktu Serah Terima</div>
                                <div style="font-size:11px; font-weight:bold;">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </th>
        </tr>
    </table>

    <!-- ================= TABEL UTAMA DENGAN HEADER REPEAT ================= -->
    <table>
        <thead>
            <!-- HEADER GROUP -->
            <tr class="header1">
                <th rowspan="2" class="col-no header-cell">NO</th>
                <th colspan="3" class="header-cell">DETAIL ORDER</th>
                <th rowspan="2" class="col-estimasi header-cell">ESTIMASI (WAKTU)</th>
                <th colspan="2" class="header-cell">PIC QC OUTGOING</th>
                <th rowspan="2" class="col-ceklist header-cell">CEKLIST</th>
                <th rowspan="2" class="col-catatan header-cell">CATATAN</th>
            </tr>

            <tr class="header2">
                <th class="col-id header-cell">ID ORDER</th>
                <th class="col-unit header-cell">NAMA UNIT</th>
                <th class="col-kategori header-cell">KATEGORI</th>
                <th class="col-kode header-cell">KODE</th>
                <th class="col-hasilcek header-cell">HASIL CEK</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="font-bold text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->no_pl ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                <td class="text-center">
                    {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}<br>
                    <span style="font-size:8.5px;">{{ $item->estimasi_hari ?? 0 }} Hari</span>
                </td>
                <td class="text-center"></td>
                <td class="text-center"></td>
                <td class="text-center"></td>
                <td class="text-left" style="font-size:8.8px;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                        echo Str::limit(trim($catatan), 75);
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh : QC • {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>