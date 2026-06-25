<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Packing</title>

    <style>
        @page {
            size: A4 landscape;
            margin: 5mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            margin: 0;
            padding: 5px;
            line-height: 1.3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #374151;
        }

        th,
        td{
            border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:top;
            text-align:center;
            line-height:1;
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
            width: 50px;
        }

        .col-unit {
            width: 120px;
        }

        .col-kategori {
            width: 60px;
        }

        .col-estimasi {
            width: 70px;
        }

        .col-pic {
            width: 100px;
        }

        .col-helper {
            width: 100px;
        }

        .col-catatan {
            width: 200px;
        }
       .col-distribusi {
            width: 25px;
       }
        .col-berat {
            width: 50px;
        }
        .col-berat1 {
            width: 50px;
        }
        .col-koli {
            width: 50px;
        }
        .col-packing {
            width: 50px;
        }

        /* ================= AREA MANUAL ================= */

        .manual-area {
            height: 9px;
            vertical-align: top;
            padding-top: 8px;
        }

        /* Border luar lebih tebal */

        tr:first-child th {
            border-top: 1px solid #374151;
        }

        tr:last-child td {
            border-bottom: 1px solid #374151;
        }

        th:first-child,
        td:first-child {
            border-left: 1px solid #374151;
        }

        th:last-child,
        td:last-child {
            border-right: 1px solid #374151;
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
            <th colspan="11" class="main-title">

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
                            Rekap Aktual Detail - Packing {{ $stokisName }}
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

            <th rowspan="2" class="col-no" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">
                NO
            </th>

            <th colspan="3">
                DETAIL ORDER
            </th>

            <th rowspan="2" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;" class="col-estimasi">
                ESTIMASI (WAKTU)
            </th>

             <th rowspan="2" class="col-distribusi" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">DISTRIBUSI</th>

            <th rowspan="2" class="col-berat" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">BERAT biMBA SHOP</th>
            <th rowspan="2" class="col-berat1" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">BERAT AKTUAL</th>

             <th rowspan="2" class="col-koli" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">JUMLAH KOLI</th>
             <th rowspan="2" class="col-packing" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">NAMA PACKING</th>

            <th rowspan="2" class="col-catatan" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">
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

            <td class="text-center">
                {{ $item->nama_barang ?? '-' }}
            </td>

            <td style="
                    padding:1px 2px;
                    vertical-align:top;
                    text-align:center;
                ">
                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}
                    </div>

                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->estimasi_hari ?? 0 }} Hari
                    </div>
                </td>

            <td style="
                    padding:1px 2px;
                    vertical-align:top;
                    text-align:center;
                ">
                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->pengiriman ?? '-' }}
                    </div>

                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->service_pengiriman ?? '-' }}
                    </div>
                </td>

            <td class="text-left">{{ $item->order_weight ?? '-' }} gr</td>
            <td class="manual-area">
            </td>
            <td class="manual-area">
            </td>
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