<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>QC Report - {{ $docNumber ?? 'QC-' . now()->format('Ymd') }}</title>

    <style>
        @page {
            margin: 10px;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #222;
            margin: 0;
            padding: 0;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .header h3 {
            margin-top: 5px;
            font-size: 12px;
            font-weight: normal;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 4px;
            vertical-align: middle;
            font-size: 12px;
        }

        th {
            background: #e5e7eb;
            text-align: center;
            font-weight: bold;
        }

        .main-title {
            font-size: 11px;
            font-weight: bold;
            background: #ffffff;
            text-align: center;
            padding: 8px;
        }

        .text-left {
            text-align: left;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        /* =========================
           LEBAR KOLOM
        ========================== */

        .col-nopl {
            width: 55px;
        }

        .col-serah {
            width: 85px;
        }

        .col-unit {
            width: 220px;
            overflow: hidden;
        }

        .col-pengiriman {
            width: 65px;
        }

        .col-service {
            width: 60px;
        }

        .col-kategori {
            width: 75px;
        }

        .col-tglbayar {
            width: 80px;
        }

        .col-bayar {
            width: 90px;
        }

        .col-stokis {
            width: 100px;
        }

        .col-estimasi {
            width: 70px;
        }

        .col-hari {
            width: 60px;
        }

        .col-berat {
            width: 75px;
        }

        .col-qc {
            width: 60px;
        }

        .col-ket {
            width: 280px;
            font-size: 12px;
            line-height: 1.2;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #666;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2>REKAP AKTUAL - QC OUTGOING</h2>
        <h3>
            {{ $docNumber ?? 'QC Report' }}
            —
            {{ now()->format('d F Y H:i') }}
        </h3>
    </div>

    <table>

        <thead>

            <tr>
                <th colspan="13" class="main-title">
                    REKAP AKTUAL - QC OUTGOING
                    {{ $data->first()?->nama_stokis ?? 'STOKIS JAKARTA' }}
                </th>
            </tr>

            <tr>
                <th colspan="2">TANGGAL</th>
                <th colspan="4">PENGIRIMAN & BARANG</th>
                <th colspan="2">PEMBAYARAN</th>
                <th>STOKIS</th>
                <th colspan="2">ESTIMASI PERSIAPAN</th>
                <th>QUALITY CONTROL</th>
                <th>KETERANGAN</th>
            </tr>

            <tr>

                <th class="col-nopl">
                    No PL
                </th>

                <th class="col-serah">
                    Waktu Serah Terima
                </th>

                <th class="col-unit">
                    Nama Unit
                </th>

                <th class="col-pengiriman">
                    Pengiriman
                </th>

                <th class="col-service">
                    Service
                </th>

                <th class="col-kategori">
                    Kategori
                </th>

                <th class="col-tglbayar">
                    Tgl Bayar
                </th>

                <th class="col-bayar">
                    Jumlah Bayar
                </th>

                <th class="col-stokis">
                    Nama Stokis
                </th>

                <th class="col-estimasi">
                    Tgl Estimasi
                </th>

                <th class="col-hari">
                    Estimasi Hari
                </th>

                <th class="col-qc">
                    Kode QC
                </th>

                <th class="col-ket">
                    Ket
                </th>

            </tr>

        </thead>

        <tbody>

            @forelse($data as $item)

                <tr>

                    <td class="text-center">
                        {{ $item->no_pl ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->created_at
                            ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i')
                            : '-' }}
                    </td>

                    <td class="text-center col-unit">
                        {{ $item->nama_unit ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->pengiriman ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->service_pengiriman ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->nama_barang ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->tgl_bayar
                            ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y')
                            : '-' }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}
                    </td>

                    <td class="text-center">
                        {{ $item->nama_stokis ?? '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->tgl_estimasi
                            ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y')
                            : '-' }}
                    </td>

                    <td class="text-center">
                        {{ $item->estimasi_hari ?? '-' }} Hari
                    </td>

                    <td class="text-right font-semibold">{{ null }}</td>

                    <td class="text-left col-ket">
                        {{ null }}
                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="13"
                        style="text-align:center;padding:20px;">
                        Belum ada data QC Report
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

    <div class="footer">
        Dicetak pada :
        {{ now()->format('d/m/Y H:i:s') }}
        | Quality Control Report
    </div>

</body>
</html>