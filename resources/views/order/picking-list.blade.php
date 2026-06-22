<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl }}</title>
    <style>
        @page {
            size: A5 landscape;
            margin: 8mm 6mm 8mm 6mm;
        }

        body { 
            font-family: Arial, sans-serif; 
            font-size: 13px;
            color: #333;
            line-height: 1.4;
            margin: 0;
        }

        .header { 
            text-align: center; 
            margin-bottom: 15px; 
            border-bottom: 3px solid #d32f2f;
            padding-bottom: 10px;
        }
        .logo { 
            font-size: 26px; 
            font-weight: bold; 
            color: #d32f2f; 
        }
        .subheader { 
            font-size: 13px; 
            margin: 4px 0;
            color: #666;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 12px 0; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 6px 5px; 
            text-align: left; 
            font-size: 12.5px;
        }
        th { 
            background-color: #f0f0f0; 
            font-weight: bold; 
            text-align: center;
        }
        .right { text-align: right; }
        .center { text-align: center; }

        /* Tombol Print */
        .print-btn {
            position: fixed;
            top: 15px;
            right: 15px;
            background: #1e40af;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        .print-btn:hover { background: #1e3a8a; }

        @media print {
            .print-btn { display: none; }
            body { margin: 0; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <!-- Tombol Print -->
    <button onclick="window.print()" class="print-btn">
        <strong>🖨 CETAK SEKARANG</strong>
    </button>

    <div class="header">
        <div class="logo">biMBA LOGISTIK</div>
        <p class="subheader">HANYA UNTUK KALANGAN SENDIRI</p>
        <h2>PICKING LIST</h2>
    </div>

    <table style="border: none;">
        <tr>
            <td>
                <strong>Nama Unit</strong> : {{ $item->nama_unit ?? '-' }}<br>
                <strong>CAB</strong> : {{ $billing_last_name ?? $item->billing_last_name ?? '-' }}<br>
                <strong>NIM</strong> : {{ $billing_company ?? $item->billing_company ?? '-' }}<br>
            </td>
            <td class="right">
                <strong>No. Order</strong> : {{ $no_pl ?? $item->no_pl }}<br>
                <strong>Tanggal Order</strong> : {{ $tgl_order ? \Carbon\Carbon::parse($tgl_order)->format('d F Y') : '-' }}<br>
                <strong>Pengiriman</strong> : {{ $item->status_kirim ?? 'Ambil Sendiri' }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th width="4%">NO</th>
                <th>NAMA PRODUK</th>
                <th width="16%">SKU</th>
                <th width="8%">QTY</th>
                <th width="9%" class="center">CEK LIST (✔)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td>{{ $row->item_name ?? $row->nama_barang ?? '-' }}</td>
                <td>{{ $row->item_sku ?? '-' }}</td>
                <td class="center">{{ $row->item_qty ?? $row->qty ?? 1 }}</td>
                <td class="center">☐</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table style="border: none; margin-top: 10px;">
        <tr>
            <td><strong>TOTAL ITEM</strong> : {{ $data->count() }}</td>
            <td class="right"><strong>TOTAL QTY</strong> : {{ $data->sum('item_qty') ?? $data->sum('qty') ?? $data->count() }}</td>
        </tr>
    </table>
    <table style="border: none; margin-top: 5px;">
        <tr>
    <td>Total Berat : {{ number_format($row->order_weight ?? 0, 2) }} gr</td>
        </tr>
    </table>

    <div style="margin-top: 35px; display: flex; gap: 60px; font-size: 13px;">
        <div>
            <strong>CHECKLIST PICKING</strong><br><br>
            ☐ Semua produk sudah diambil sesuai daftar<br>
            ☐ Kondisi produk baik dan sesuai<br>
            ☐ Jumlah sudah sesuai<br>
            ☐ Packing rapi dan aman
        </div>
        <div>
            <strong>Dipicking Oleh,</strong><br><br>
            ..............................................<br><br>
            Tanggal Picking : ________________<br>
            Jam Picking     : ________________
        </div>
    </div>

    <div style="margin-top: 40px; text-align: center; font-size: 12px; color: #666;">
        No. Order: {{ $no_pl ?? $item->no_pl }} | Dicetak: {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>