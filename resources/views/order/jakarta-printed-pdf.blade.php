<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 9.5px; 
            margin: 15px; 
            line-height: 1.4;
        }
        h1 { 
            text-align: center; 
            margin-bottom: 12px; 
            font-size: 15px; 
            font-weight: bold;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 5px;
        }
        th, td { 
            border: 1px solid #333; 
            padding: 6px 4px; 
            vertical-align: middle;
            word-wrap: break-word;
            word-break: break-word;
        }
        th { 
            background-color: #f0f0f0; 
            font-weight: bold; 
            font-size: 10px;
            text-align: center;
        }
        td { text-align: center; }

        .text-left { text-align: center; }
        .text-right { text-align: center; }

        .small { font-size: 8.5px; }

        /* Penyesuaian Lebar Kolom */
        .no-pl        { width: 7%; }
        .tgl-pl       { width: 7%; }
        .nama-unit    { width: 10%; }
        .pengiriman   { width: 9%; }
        .nama-barang  { width: 8%; }     /* ← Dikecilkan dari 18% */
        .tgl-bayar    { width: 8%; }
        .jumlah-bayar { width: 9%; }
        .nama-stokis  { width: 11%; }
        .tgl-est      { width: 7%; }
        .est-hari     { width: 6%; }
        .ket          { width: 12%; }

        .wrap-text {
            white-space: normal !important;
            word-break: break-word;
            hyphens: auto;
        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }
    </style>
</head>
<body>
    <h1>REKAP AKTUAL - {{ now()->locale('id')->format('F Y') }}</h1>
    <p style="text-align:left; margin-bottom:10px; font-size:10px;">
        Dicetak pada: {{ now()->format('d M Y H:i') }}
    </p>
    
    <table>
        <thead>
            <tr>
                <th class="no-pl">No PL</th>
                <th class="tgl-pl">Tgl Turun PL</th>
                <th class="nama-unit text-left">Nama Unit</th>
                <th class="pengiriman text-left">Pengiriman</th>
                <th class="nama-barang text-left">Nama Barang</th>
                <th class="tgl-bayar">Tgl Bayar</th>
                <th class="jumlah-bayar text-right">Jumlah Bayar</th>
                <th class="nama-stokis text-left">Nama Stokis</th>
                <th class="tgl-est">Tgl Estimasi</th>
                <th class="est-hari">Est. Hari</th>
                <th class="ket text-left">Ket</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $item->no_pl ?? '-' }}</td>
                <td>{{ $item->tgl_turun_pl ? \Carbon\Carbon::parse($item->tgl_turun_pl)->format('d/m/Y') : '-' }}</td>
                
                <td class="text-left wrap-text">{{ $item->nama_unit ?? '-' }}</td>
                
                <td class="text-left wrap-text">{{ $item->pengiriman ?? '-' }}</td>
                
                <td class="text-left wrap-text nama-barang">{{ $item->nama_barang ?? '-' }}</td>
                
                <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>
                
                <td class="text-right">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                
                <td class="text-left wrap-text">{{ $item->nama_stokis ?? 'Stokis Jakarta' }}</td>
                
                <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                
                <td>{{ $item->estimasi_hari ?? 0 }} Hari</td>
                
                <td class="text-left wrap-text small">{{ Str::limit($item->ket ?? '-', 70) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>