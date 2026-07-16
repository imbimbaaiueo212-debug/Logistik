<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Packing</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;           /* Sedikit lebih kecil */
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
            padding: 4px 4px;
            font-size: 8.8px;
            color: #374151;
            vertical-align: top;
            line-height: 1.25;
            word-wrap: break-word;
        }

        /* Zebra */
        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        /* Header Group */
        .header1 th {
            background: #dbeafe;
            font-size: 9.5px;
            padding: 6px 4px;
        }

        .header2 th {
            background: #eff6ff;
            font-size: 9px;
            padding: 4px 3px;
        }

        .header-cell {
            padding: 6px 4px;
            vertical-align: middle;
            text-align: center;
        }

        /* Column Width - DIOPTIMALKAN */
        .col-no         { width: 28px; }
        .col-id         { width: 58px; }
        .col-unit       { width: 125px; }
        .col-kategori   { width: 68px; }
        .col-estimasi   { width: 72px; }
        .col-distribusi { width: 68px; }
        .col-berat      { width: 58px; }
        .col-berat1     { width: 58px; }
        .col-koli       { width: 48px; }
        .col-packing    { width: 72px; }
        .col-catatan    { width: 130px; }

        .text-left  { text-align: left; }
        .font-bold  { font-weight: bold; }

        /* Footer */
        .footer {
            margin-top: 1px;
            text-align: right;
            font-size: 8.5px;
            color: #6b7280;
        }

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

    <!-- HEADER UTAMA -->
    <table style="width:100%; border:none; margin-bottom:10px;">
        <tr>
            <th colspan="11" style="border:none; padding:0;">
                <table style="width:100%; border:none;">
                    <tr>
                        <td style="width:75%; text-align:center; font-size:14px; font-weight:bold; color:#1e3a8a;">
                            Rekap Aktual Detail - Packing {{ $stokisName }}
                            <span style="color:#4f46e5;">{{ $rekapNo }}</span>
                        </td>
                        <td style="width:25%; text-align:center;">
                            @if($firstDate)
                                <div style="font-size:10px; color:#64748b;">Waktu Serah Terima</div>
                                <div style="font-size:10.5px; font-weight:bold;">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </th>
        </tr>
    </table>

    <!-- TABEL UTAMA -->
    <table>
        <thead>
            <tr class="header1">
                <th rowspan="2" class="col-no header-cell">NO</th>
                <th colspan="3" class="header-cell">DETAIL ORDER</th>
                <th rowspan="2" class="col-estimasi header-cell">ESTIMASI (WAKTU)</th>
                <th rowspan="2" class="col-distribusi header-cell">DISTRIBUSI</th>
                <th rowspan="2" class="col-berat header-cell">BERAT BIMBA SHOP</th>
                <th rowspan="2" class="col-berat1 header-cell">BERAT AKTUAL</th>
                <th rowspan="2" class="col-koli header-cell">JUMLAH KOLI</th>
                <th rowspan="2" class="col-packing header-cell">NAMA PACKING</th>
                <th rowspan="2" class="col-catatan header-cell">CATATAN</th>
            </tr>

            <tr class="header2">
                <th class="col-id header-cell">ID ORDER</th>
                <th class="col-unit header-cell">NAMA UNIT</th>
                <th class="col-kategori header-cell">KATEGORI</th>
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
                    <span style="font-size:8px;">{{ $item->estimasi_hari ?? 0 }} Hari</span>
                </td>

                <td class="text-center">
                    {{ $item->pengiriman ?? '-' }}<br>
                    <span style="font-size:8px;">{{ $item->service_pengiriman ?? '-' }}</span>
                </td>

                <td class="text-center">{{ (int)($item->order_weight ?? 0) }} g</td>
                <td class="text-center"></td>
                <td class="text-center"></td>
                <td class="text-center"></td>

                <td class="text-left" style="font-size:8.2px;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                        echo Str::limit(trim($catatan), 70);
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh : PACKING • {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>