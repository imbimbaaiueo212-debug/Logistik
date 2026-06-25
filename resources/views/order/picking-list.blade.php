<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl ?? '-' }}</title>
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
            background: #f8f9fa;
            padding: 20px;
        }

        /* === PREVIEW CONTAINER (A5 Simulation) === */
        .preview {
            width: 148mm;
            min-height: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
            overflow: hidden;
            position: relative;
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
        .subheader { 
            font-size: 12px; 
            margin: 2px 0;
            color: #666;
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

        /* Print Style */
        @media print {
            body { 
                margin: 0; 
                padding: 0;
                background: white;
            }
            .preview {
                box-shadow: none;
                width: 100%;
                min-height: auto;
                margin: 0;
            }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>

    <!-- Tombol Print -->
    <button onclick="window.print()" class="print-btn" 
            style="position:fixed; top:20px; right:20px; padding:8px 16px; 
                   background:#1e40af; color:white; border:none; border-radius:5px; 
                   cursor:pointer; font-size:14px; z-index:100;">
        🖨 CETAK
    </button>

    <div class="preview">
        <div style="padding: 6mm 5mm 7mm 5mm;">
            <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:8px;">
                <tr>
                    
                    <td style="width:40%; border:none;">
                        
                        <strong style="font-size: 15px;"">{{ $no_pl ?? $item->no_pl ?? '-' }}</strong><br>
                        <span>PayDate</span>, <strong>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</strong><br>
                        
                        <strong>{{ $item->pengiriman ?? '-' }} | {{ $item->service_pengiriman}}</strong><strong></strong> {{ $data->count() }} | {{ $data->sum('item_qty') ?? $data->sum('qty') ?? $data->count() }}</strong><br>
                        <strong> {{ $item->nama_unit ?? '-' }}</strong><br>
                        
                    </td>

                    <td style="border:none; text-align:right; vertical-align: top; padding-top: 15px; width: 35%;">
                        <td style="text-align:right;">
                            <strong style="font-size: 15px; padding:50px ">PARAF</strong><br><br><br><br>
                           <br><br><br>
                            
                        </td>
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
                        <td>{{ $row->item_name ?? $row->nama_barang ?? '-' }}</td>
                        <td>{{ $row->item_sku ?? '-' }}</td>
                        <td class="center">{{ $row->item_qty ?? $row->qty ?? 1 }}</td>
                        <td class="center">☐</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            

        </div>
    </div>

</body>
</html>