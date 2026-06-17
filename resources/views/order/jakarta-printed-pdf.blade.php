<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Realisasi Aktif</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 9px; 
            margin: 12px; 
            line-height: 1.3;
        }
        h1 { 
            text-align: center; 
            margin-bottom: 15px; 
            font-size: 14px; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 4px 3px; 
            text-align: center; 
            vertical-align: middle;
        }
        th { 
            background-color: #ffffff; 
            font-weight: bold; 
            font-size: 9.5px;
        }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .small { font-size: 8px; }
    </style>
</head>
<body>
    <h1>REALISASI AKTIF - {{ now()->format('F Y') }}</h1>
    
    <table>
        <thead>
            <tr>
                <th>No PL</th>
                <th>Tgl Turun PL</th>
                <th>Nama Unit</th>
                <th>Pengiriman</th>
                <th>Nama Barang</th>
                <th>Tgl Bayar</th>
                <th>Jumlah Bayar</th>
                <th>Nama Stokis</th>
                <th>Tgl Estimasi</th>
                <th>Estimasi Hari</th>
                <th>Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->no_pl ?? '-' }}</td>
                <td>{{ $item->tgl_turun_pl ? \Carbon\Carbon::parse($item->tgl_turun_pl)->format('d/m/Y') : '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-left">{{ $item->pengiriman ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-right">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                <td class="text-left">{{ $item->nama_stokis ?? 'Stokis Jakarta' }}</td>
                <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                <td>{{ $item->estimasi_hari ?? 0 }} Hari</td>
                <td class="text-left small">{{ $item->ket ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>