<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PICKING LIST - {{ $no_pl ?? '-' }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 6mm 5mm 7mm 5mm;
        }

        body { 
            font-family: Arial, Helvetica, sans-serif; 
            font-size: 11.8px;
            color: #333;
            line-height: 1.35;
            margin: 0;
        }

        .header { 
            text-align: center; 
            margin-bottom: 8px; 
            border-bottom: 2.5px solid #d32f2f;
            padding-bottom: 6px;
        }
        .logo { 
            font-size: 24px; 
            font-weight: bold; 
            color: #d32f2f; 
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin: 8px 0; 
        }
        th, td { 
            border: 1px solid #333; 
            padding: 5px 4px; 
            font-size: 11.5px;
        }
        th { 
            background-color: #f4f4f4; 
            font-weight: bold; 
            text-align: center;
        }

        .right { text-align: right; }
        .center { text-align: center; }

        .signature-area {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
        }
    </style>
</head>
<body>

    <div style="padding: 6mm 5mm 7mm 5mm;">

        <div class="header">
            <div class="logo">biMBA LOGISTIK</div>
            <p style="font-size:12px; margin:2px 0; color:#666;">HANYA UNTUK KALANGAN SENDIRI</p>
            <h2 style="margin:6px 0 3px 0; font-size:17px;">PICKING LIST</h2>
        </div>

        <table style="border: none; margin-bottom: 8px;">
            <tr>
                <td style="width:52%;">
                    <strong>Nama Unit</strong> : {{ $item->nama_unit ?? '-' }}<br>
                    <strong>CAB</strong> : {{ $item->billing_last_name ?? '-' }}<br>
                    <strong>NIM</strong> : {{ $item->billing_company ?? '-' }}
                </td>
                <td class="right">
                    <strong>No. Order</strong> : {{ $no_pl ?? '-' }}<br>
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
                    <th width="17%">SKU</th>
                    <th width="8%">QTY</th>
                    <th width="10%" class="center">CEK</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data as $index => $row)
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td>{{ $row->item_name ?? '-' }}</td>
                    <td>{{ $row->item_sku ?? '-' }}</td>
                    <td class="center">{{ $row->item_qty ?? 1 }}</td>
                    <td class="center">☐</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="border: none; margin-top: 6px;">
            <tr>
                <td><strong>TOTAL ITEM</strong> : {{ $data->count() }}</td>
                <td class="right"><strong>TOTAL QTY</strong> : {{ $data->sum('item_qty') ?? $data->count() }}</td>
            </tr>
        </table>

        <div class="signature-area">
            <div>
                <strong>CHECKLIST PICKING</strong><br><br>
                ☐ Semua produk sudah diambil sesuai daftar<br>
                ☐ Kondisi produk baik dan sesuai<br>
                ☐ Jumlah sudah sesuai<br>
                ☐ Packing rapi dan aman
            </div>
            
            <div style="text-align:right;">
                <strong>Dipicking Oleh,</strong><br><br><br>
                ..............................................<br>
                <small>Nama & Tanggal</small><br><br>
                Tanggal Picking : ________________<br>
                Jam Picking     : ________________
            </div>
        </div>

        <div style="margin-top: 25px; text-align: center; font-size: 10.5px; color: #666;">
            No. Order: {{ $no_pl ?? '-' }} | Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>

    </div>

</body>
</html>