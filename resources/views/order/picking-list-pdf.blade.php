<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl ?? '-' }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 3mm 4mm 5mm 4mm;   /* margin kanan-kiri dikecilkan */
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.8px;           /* dikecilkan */
            color: #333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .container{
            width:100%;
            max-width:135mm;
            margin:0 auto;
            box-sizing:border-box;
        }

        .header {
            text-align: center;
            padding-bottom: px;
            border-bottom: 1px solid #000000;
            margin-bottom: 8px;
        }

        .header-title {
            font-size: 26px;
            font-weight: 700;
            letter-spacing: 1.5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        th, td {
            border: 1px solid #333;
            padding: 3.5px 3px;          /* padding kolom dikecilkan */
            font-size: 15px;
        }

        th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: center;
        }

        .center { text-align: center; }
        .left { text-align: left; }

        .info-table td {
            border: none;
            padding: 3px 2px;
            vertical-align: top;
        }

        .footer {
            position: absolute;
            bottom: 3mm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9.5px;
            color: #555;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div style="padding: 1mm 2mm 10mm 2mm;">

            <!-- HEADER -->
            <!-- HEADER - Lebih Nempel -->
            <div class="header" style="margin-bottom: 8px;">
                <span class="header-title" style="font-size: 27px; line-height: 1;">PICKING LIST</span><br>
                <span style="font-size: 15px; font-weight: 600; margin-top: -8px; display: inline-block;">
                    {{ str_replace(['Stokis ', 'Stokis'], '', $item->nama_stokis ?? 'Jakarta Aktif') }}
                </span>
            </div>

            <!-- INFO -->
            <table class="info-table" style="margin-bottom: 2px;">
                <tr>
                    <td style="width:57%;">
                        <strong>{{ $no_pl ?? $item->no_pl ?? '-' }}</strong><br>
                        PayDate: <strong>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</strong><br>
                        Estimasi: <strong>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-'}}</strong><br>
                        <strong>{{ $item->nama_unit ?? '-' }}</strong><br>
                        <strong>{{ $item->pengiriman ?? '-' }} | {{ $item->service_pengiriman ?? '-' }}</strong> {{ $data->count() }} | {{ $data->sum('item_qty') ?: $data->sum('qty') ?: $data->count() }}
                    </td>

                    <td style="width:20%; text-align:center; border:1px solid #333;">
                        <strong style="font-size:13.5px;">PARAF</strong>
                        <br><br><br>
                        
                    </td>
                </tr>
            </table>

            <!-- TABEL PRODUK -->
            <table>
                <thead>
                    <tr>
                        <th width="4%">NO</th>
                        <th>NAMA PRODUK</th>
                        <th width="17%">SKU</th>
                        <th width="7%">QTY</th>
                        <th width="8%">CEK</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="left">{{ $row->item_name ?? $row->nama_barang ?? '-' }}</td>
                        <td class="center">{{ $row->item_sku ?? '-' }}</td>
                        <td class="center">{{ $row->item_qty ?? $row->qty ?? 1 }}</td>
                        <td class="center"></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <!-- FOOTER -->
        

    </div>

    
<script type="text/php">
if (isset($pdf)) {

    $noOrder = "{{ $no_pl ?? $item->no_pl ?? '-' }}";
    $printed = "{{ now()->format('d/m/Y H:i') }}";

    $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($noOrder, $printed) {

        $font = $fontMetrics->getFont("Arial", "normal");

        $canvas->text(
            20,
            575,
            "No. Order: {$noOrder} | Dicetak: {$printed}",
            $font,
            9
        );

        $canvas->text(
            385,
            575,
            "{$pageNumber} / {$pageCount}",
            $font,
            9
        );
    });

}
</script>
</body>
</html>