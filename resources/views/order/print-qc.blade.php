<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual QC OUTGOING</title>

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
            border: 1px solid #374151;
        }

        th, td {
            border: 1px solid #374151;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
        }

        .header1 th, .header2 th {
            background: #f1f5f9;
            font-weight: bold;
            font-size: 9px;
        }

        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }

        /* TANDA TANGAN */
        .signature td {
            border: none;
            padding: 6px 4px;
            vertical-align: top;
            text-align: center;
            font-size: 8.8px;
        }
        .col-no {
            width: 5px;
        }

        .col-id {
            width: 50px;
        }

        .col-unit {
            width: 200px;
        }

        .col-kategori {
            width: 150px;
        }

        .col-tanggal {
            width: 50px;
        }

        .col-hari {
            width: 50px;
        }
        .col-kode {
            width: 35px;
        }

        .col-hasilcek {
            width: 50px;
        }

        .col-ceklist {
            width: 10px;
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

<!-- HEADER UTAMA: TANDA TANGAN + JUDUL + WAKTU -->
<table style="width:100%; margin-bottom: 2px; border: 1px solid #374151;">
    <tr>
        <!-- KOLOM TANDA TANGAN (KIRI) - DIBUAT LEBIH KECIL -->
        <td style="width: 18%; padding: 6px 4px; border-right: 1px solid #374151; vertical-align: top;">
            <div style="height:68px; position:relative;">
                
                <!-- Tulisan Serah Terima & Packing -->
                <div style="padding-top:6px; font-weight:bold; font-size:8.8px; text-align:center; line-height:1.05;">
                    Serah Terima<br>Packing
                </div>
                
                <!-- Garis + Nama/Tgl di bawah -->
                <div style="position:absolute; bottom:0px; left:0; right:0; text-align:center; font-size:8.3px;">
                    ____________________<br>
                    <strong>Nama/Tgl</strong>
                </div>
                
            </div>
        </td>

        <!-- JUDUL + WAKTU (KANAN) -->
        <td style="
                            width:75%;
                            border:none;
                            text-align:center;
                            font-size:13px;
                            font-weight:bold;
                            padding:10px;
                        ">
                            Rekap Aktual Detail - QC OUTGOING {{ $stokisName }}
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
        </td>
    </tr>
</table>
        </td>
    </tr>
</table>

<!-- TABEL UTAMA -->
<table>
    <thead>
        <tr class="header1">
            <th rowspan="2" class="col-no">NO</th>
            <th colspan="3">DETAIL ORDER</th>
            <th colspan="2">ESTIMASI (WAKTU)</th>
            <th colspan="2">PIC QC OUTGOING</th>
            <th rowspan="2" class="col-ceklist">CEKLIST</th>
            <th rowspan="2" class="col-catatan">CATATAN</th>
        </tr>

        <tr class="header2">
            <th class="col-id">ID ORDER</th>
            <th class="col-unit">NAMA UNIT</th>
            <th class="col-kategori">KATEGORI</th>
            <th class="col-tanggal">TANGGAL</th>
            <th class="col-hari">HARI</th>
            <th class="col-kode">KODE</th>
            <th class="col-hasilcek">HASIL CEK</th>
        </tr>
    </thead>

    <tbody>
        @foreach($data as $item)
        <tr>
            <td class="font-bold">{{ $loop->iteration }}</td>
            <td>{{ $item->no_pl ?? '-' }}</td>
            <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
            <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
            <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
            <td>{{ $item->estimasi_hari ?? '-' }} Hari</td>
            <td class="manual-area"></td>
            <td class="manual-area"></td>
            <td class="manual-area"></td>
            <td class="manual-area text-left" style="font-size:8px;">
                @php
                    $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                @endphp
                {{ Str::limit(trim($catatan), 80) }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>