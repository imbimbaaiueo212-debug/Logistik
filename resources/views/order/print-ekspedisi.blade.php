<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pemesanan Report - {{ $docNumber ?? 'PEMESANAN-' . now()->format('Ymd') }}</title>
    <style>
        body { 
            font-family: 'Poppins', Arial, sans-serif; 
            margin: 20px;
            font-size: 12px;
            color: #333;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 15px 0;
        }
        th, td {
            border: 1px solid #374151;
            padding: 8px 6px;
            text-align: center;
            vertical-align: middle;
            font-size: 12px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: 700;
        }
        .main-title {
            font-size: 18px;
            font-weight: 700;
            text-align: center;
            padding: 12px;
            background-color: #ffffff;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 25px; }
        .small { font-size: 10px; }
        .green { color: #10b981; }
        .red { color: #ef4444; }
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
                
                <th colspan="4">EKSPEDISI</th>
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
                <th>JML KOLI</th>
                <th>KODE PACKING</th>

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