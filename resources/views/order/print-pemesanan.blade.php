<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemesanan Report - {{ $docNumber ?? 'PEMESANAN-' . now()->format('Ymd') }}</title>
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
            font-size: 8px;
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
            white-space: nowrap;
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
            font-size: 7.5px;
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
        <h2>PEMESANAN</h2>
        <h3>{{ $docNumber ?? 'Pemesanan Report' }} — {{ now()->format('d F Y H:i') }}</h3>
    </div>

    <table>
        <thead>
            <tr>
                <th colspan="16" class="main-title">PEMESANAN CONTROL REPORT</th>
            </tr>

            <tr>
                <th colspan="2">TANGGAL</th>
                <th colspan="4">PENGIRIMAN & BARANG</th>
                <th colspan="2">PEMBAYARAN</th>
                <th>STOKIS</th>
                <th colspan="2">ESTIMASI PERSIAPAN</th>
                <th colspan="2">BERAT</th>
                <th colspan="2">PEMESANAN</th>
                <th colspan="1">KETERANGAN</th>
            </tr>

            <tr>
                <th>No PL</th>
                <th>Waktu Serah</th>
                <th>NAMA UNIT</th>
                <th>PENGIRIMAN</th>
                <th>SERVICE</th>
                <th>KATEGORI</th>
                <th>TGL BAYAR</th>
                <th>JUMLAH BAYAR</th>
                <th>NAMA STOKIS</th>
                <th>TGL ESTIMASI</th>
                <th>EST. HARI</th>
                <th>BERAT SHOP</th>
                <th>BERAT AKTUAL</th>
                <th>PENYEBUT</th>
                <th>PENGAMBIL</th>

                <th>KET</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $item)
            <tr>
                <td>{{ $item->no_pl ?? '-' }}</td>
                <td>{{ $item->created_at ? \Carbon\Carbon::parse($item->created_at)->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-left">{{ $item->pengiriman ?? '-' }}</td>
                <td class="text-left">{{ $item->service_pengiriman ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                <td>{{ $item->tgl_bayar ?? '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                <td class="text-left">{{ $item->nama_stokis ?? '-' }}</td>
                <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->estimasi_hari ?? '-' }} Hari</td>
                <td class="text-right">{{ number_format($item->order_weight ?? 0, 0, ',', '.') }} gr</td>
                <td class="text-right font-semibold">{{ null }}</td>
                <td class="text-right font-semibold">{{ null }}</td>
                <td class="text-right font-semibold">{{ null }}</td>
                <td class="text-left text-xs">{{ $item->ket ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="16" class="text-center py-10">Tidak ada data</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 40px; text-align: center; font-size: 11px; color: #666;">
        Dicetak pada: {{ now()->format('d/m/Y H:i:s') }} | PEMESANAN CONTROL REPORT
    </div>

</body>
</html>