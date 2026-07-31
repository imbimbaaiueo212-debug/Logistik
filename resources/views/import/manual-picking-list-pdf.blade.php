<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl ?? '-' }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 4mm 5mm 12mm 5mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
        }

        .header {
            border-bottom: 1px solid #000;
            margin-bottom: 6px;
            padding-bottom: 4px;
        }

        .header-title {
            font-size: 16px;
            font-weight: bold;
            line-height: 1.2;
        }

        .header-sub {
            font-size: 13px;
            font-weight: 600;
            margin-top: 1px;
        }

        /* ===== INFO TABLE ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .info-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }

        .info-left {
            width: 78%;
            font-size: 11px;
            line-height: 1.35;
        }

        .info-right {
            width: 22%;
            text-align: right;
        }

        .paraf-box {
            width: 85px;
            height: 68px;
            border: 1px solid #333;
            text-align: center;
            display: inline-block;
        }

        .paraf-label {
            font-size: 11px;
            font-weight: bold;
            padding-top: 3px;
        }

        /* ===== TABEL PRODUK ===== */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        .product-table th,
        .product-table td {
            border: 1px solid #333;
            padding: 3px 4px;
            font-size: 11px;
            vertical-align: top;
        }

        .product-table th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: center;
            padding: 4px 3px;
        }

        .center { text-align: center; }
        .left   { text-align: left; }
    </style>
</head>
<body>

<div class="container">

    {{-- ================= HEADER ================= --}}
    <div class="header">
        @php
            $kategoriJudul = '';

            if ($data->isNotEmpty()) {
                $firstRow = $data->first();

                $skuRaw = strtoupper(trim($firstRow->item_sku ?? $firstRow->sku ?? ''));
                $sku    = preg_replace('/[^A-Z0-9]/', '', $skuRaw);

                $nama = strtolower(trim(
                    $firstRow->item_name
                    ?? $firstRow->nama_barang
                    ?? $firstRow->kategori
                    ?? $item->kategori_order
                    ?? ''
                ));

                if (str_contains($sku, 'STA') && !str_contains($sku, 'STPB')) {
                    $kategoriJudul = 'STA';
                } elseif (str_contains($sku, 'STPB')) {
                    $kategoriJudul = 'STPB';
                } elseif (str_contains($nama, 'modul')) {
                    $kategoriJudul = 'MODUL';
                } elseif (str_contains($nama, 'majalah') || preg_match('/\bM\d{2,4}\b/i', $skuRaw)) {
                    $kategoriJudul = 'MAJALAH SAHABAT biMBA';
                } elseif (str_contains($nama, 'sertifikat') || str_contains($nama, 'surat tanda')) {
                    $kategoriJudul = 'SERTIFIKAT';
                } elseif (str_contains($nama, 'seragam')) {
                    $kategoriJudul = 'SERAGAM';
                }
            }

            if (empty($kategoriJudul) && !empty($item->kategori_order)) {
                $kategoriJudul = strtoupper($item->kategori_order);
            }

            if (empty($kategoriJudul) && request()->has('kategori')) {
                $kategoriJudul = strtoupper(request('kategori'));
            }
        @endphp

        <div class="header-title">
            PICKING LIST
            @if($kategoriJudul)
                | {{ $kategoriJudul }}
            @endif
        </div>
        <div class="header-sub">
            {{ str_replace(['Stokis ', 'Stokis'], '', $item->nama_stokis ?? 'Manual') }}
        </div>
    </div>

    {{-- ================= INFO + PARAF ================= --}}
    <table class="info-table">
        <tr>
            <td class="info-left">
                <strong>{{ $no_pl ?? $item->no_pl ?? '-' }}</strong>
                @if(!empty($item->no_ps))
                    | No. PS: <strong>{{ $item->no_ps }}</strong>
                @endif
                <br>

                <strong>{{ $item->nama_unit ?? '-' }}</strong><br>

                @if(!empty($item->manualOrder?->phone))
                    Telp: {{ $item->manualOrder->phone }}<br>
                @endif

                @php
                    $alamat = trim(implode(', ', array_filter([
                        $item->manualOrder?->shipping_address_1,
                        $item->manualOrder?->shipping_address_2,
                    ])));
                @endphp
                @if($alamat !== '')
                    {{ $alamat }}<br>
                @endif

                @if(!empty($item->manualOrder?->shipping_city))
                    Wilayah: <strong>{{ $item->manualOrder->shipping_city }}</strong><br>
                @endif

                @php
                    $cp = null;
                    $rawNotes = $item->manualOrder?->notes ?? $item->manualOrder?->catatan ?? '';
                    if (preg_match('/\bCP:\s*(.+)$/u', $rawNotes, $m)) {
                        $cp = trim($m[1]);
                    }
                @endphp
                @if(!empty($cp))
                    CP: {{ $cp }}<br>
                @endif

                <strong>{{ $item->pengiriman ?? '-' }} | {{ $item->service_pengiriman ?? '-' }}</strong>
                {{ $data->count() }} | {{ $data->sum('item_qty') ?: $data->sum('qty') ?: $data->count() }}
            </td>

            <td class="info-right">
                <div class="paraf-box">
                    <div class="paraf-label">PARAF</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ================= TABEL PRODUK ================= --}}
    <table class="product-table">
        <thead>
            <tr>
                <th width="6%">NO</th>
                <th>NAMA PRODUK</th>
                <th width="16%">SKU</th>
                <th width="8%">QTY</th>
                <th width="9%">CEK</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
            <tr>
                <td class="center">{{ $index + 1 }}</td>
                <td class="left">
                    @php
                        $namaProduk = $row->item_name ?? $row->nama_barang ?? '-';
                        if (str_contains(strtolower($namaProduk), 'majalah')) {
                            $namaProduk = trim(preg_replace('/\s*\([^)]*\)/', '', $namaProduk));
                        }
                    @endphp
                    {{ $namaProduk }}
                </td>
                <td class="center">
                    @php
                        $sku = trim($row->item_sku ?? '');
                        $cleanSku = trim(str_ireplace(['JKT', '-'], '', $sku));
                    @endphp
                    {{ $cleanSku ?: '-' }}
                </td>
                <td class="center">{{ $row->item_qty ?? $row->qty ?? 1 }}</td>
                <td class="center"></td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

{{-- FOOTER via DomPDF page_script --}}
<script type="text/php">
if (isset($pdf)) {
    $noOrder  = "{{ $no_pl ?? $item->no_pl ?? '-' }}";
    $printed  = "{{ now()->format('d/m/Y H:i') }}";

    $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($noOrder, $printed) {
        $font = $fontMetrics->getFont("Arial", "normal");

        // Kiri bawah
        $canvas->text(18, 575, "No. Order: {$noOrder} | Dicetak: {$printed}", $font, 8);

        // Kanan bawah
        $canvas->text(390, 575, "{$pageNumber} / {$pageCount}", $font, 8);
    });
}
</script>

</body>
</html>