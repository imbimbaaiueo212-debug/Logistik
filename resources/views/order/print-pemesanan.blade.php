<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Picking</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            margin: 0;
            padding: 5px;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid #374151;
        }

        th,
        td {
            border: 1px solid #374151;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        /* ================= HEADER ================= */

        .header1 th,
        .header2 th {
            background: #f1f5f9;
            font-weight: bold;
            font-size: 9px;
        }

        .main-title {
            background: #f8fafc;
            font-size: 14px;
            font-weight: bold;
            padding: 0;
        }

        .text-left {
            text-align: left;
        }

        .font-bold {
            font-weight: bold;
        }

        /* ================= KOLOM ================= */

        .col-no {
            width: 25px;
        }

        .col-id {
            width: 70px;
        }

        .col-unit {
            width: 150px;
        }

        .col-kategori {
            width: 70px;
        }

        .col-tanggal {
            width: 75px;
        }

        .col-hari {
            width: 50px;
        }

        .col-pic {
            width: 100px;
        }

        .col-helper {
            width: 100px;
        }

        .col-catatan {
            width: 300px;
        }

        /* ================= AREA MANUAL ================= */

        .manual-area {
            height: 9px;
            vertical-align: top;
            padding-top: 8px;
        }

        /* Border luar lebih tebal */

        tr:first-child th {
            border-top: 2px solid #374151;
        }

        tr:last-child td {
            border-bottom: 2px solid #374151;
        }

        th:first-child,
        td:first-child {
            border-left: 2px solid #374151;
        }

        th:last-child,
        td:last-child {
            border-right: 2px solid #374151;
        }
    </style>
</head>

<body>

@php
    $firstItem = $data->first();

    $stokisName = $firstItem?->nama_stokis ?? 'STOKIS JAKARTA AKTIF';

    $rekapNo = $firstItem?->rekap_number ?? '#0001';

    $firstDate = $data->min('created_at') ?? $data->min('tgl_turun_pl');
@endphp

<table>

    <thead>

        <!-- JUDUL -->
        <tr>
            <th colspan="10" class="main-title">

                <table style="width:100%; border:none; border-collapse:collapse;">
                    <tr>

                        <td style="
                            width:75%;
                            border:none;
                            text-align:center;
                            font-size:13px;
                            font-weight:bold;
                            padding:10px;
                        ">
                            Rekap Aktual Detail - Picking {{ $stokisName }}
                        </td>

                        <td style="
                            width:25%;
                            border:none;
                            text-align:center;
                            padding:8px;
                        ">

                            @if($firstDate)

                                <div style="
                                    font-size:10px;
                                    color:#64748b;
                                    font-weight:bold;
                                ">
                                    Waktu Serah Terima
                                </div>

                                <div style="
                                    font-size:11px;
                                    font-weight:bold;
                                ">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>

                            @endif

                        </td>

                    </tr>
                </table>

            </th>
        </tr>

        <!-- HEADER GROUP -->
        <tr class="header1">

            <th rowspan="2" class="col-no">
                NO
            </th>

            <th colspan="3">
                DETAIL ORDER
            </th>

            <th rowspan="2">
                WAKTU BAYAR
            </th>

            <th colspan="2">
                ESTIMASI (WAKTU)
            </th>

            <th rowspan="2" class="col-pic">
                PIC
            </th>

            <th rowspan="2" class="col-helper">
                HELPER
            </th>

            <th rowspan="2" class="col-catatan">
                CATATAN
            </th>

        </tr>

        <tr class="header2">

            <th class="col-id">
                ID ORDER
            </th>

            <th class="col-unit">
                NAMA UNIT
            </th>

            <th class="col-kategori">
                KATEGORI
            </th>

            <th class="col-tanggal">
                TANGGAL
            </th>

            <th class="col-hari">
                HARI
            </th>

        </tr>

    </thead>

    <tbody>

        @foreach($data as $item)

        <tr>

            <td class="font-bold">
                {{ $loop->iteration }}
            </td>

            <td>
                {{ $item->no_pl ?? '-' }}
            </td>

            <td class="text-left">
                {{ $item->nama_unit ?? '-' }}
            </td>

            <td class="text-left">
                {{ $item->nama_barang ?? '-' }}
            </td>

            <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>

            <td>
                {{ $item->tgl_estimasi
                    ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y')
                    : '-' }}
            </td>

            <td>
                {{ $item->estimasi_hari ?? '-' }} Hari
            </td>

            <!-- PIC -->
            <td class="manual-area">
            </td>

            <!-- HELPER -->
            <td class="manual-area">
            </td>

            <!-- CATATAN -->
            <td class="manual-area text-left" style="font-size:8px;">

                @php
                    $catatan = $item->ket
                        ?? $item->jakartaAktif?->catatan
                        ?? '';
                @endphp

                {{ Str::limit(trim($catatan), 80) }}

            </td>

        </tr>

        @endforeach

    </tbody>

</table>

</body>
</html>