<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl ?? '-' }}</title>
    <style>
        @page {
            size: A5 {{ $orientation ?? 'portrait' }};
            /* sedikit lebih lega agar scale 110-120% jarang ke-potong */
            margin: {{ ($orientation ?? 'portrait') === 'landscape' ? '7mm' : '5mm 6mm 12mm 6mm' }};
        }

        * {
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            line-height: 1.35;
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '14px' : '11px' }};

            /* ===== CENTER CONTENT ===== */
            display: flex;
            justify-content: center;   /* horizontal center */
            align-items: flex-start;   /* mulai dari atas (bisa diganti center kalau mau vertikal juga) */
        }

        /* ===== CONTAINER ===== */
        .container {
            width: 98%;               /* buffer untuk scale */
            max-width: 100%;
            margin: 0 auto;
        }

        /* ===== HEADER ===== */
        .header {
            border-bottom: 1.5px solid #000;
            margin-bottom: {{ ($orientation ?? 'portrait') === 'landscape' ? '10px' : '6px' }};
            padding-bottom: {{ ($orientation ?? 'portrait') === 'landscape' ? '6px' : '4px' }};
            text-align: left;
        }

        .header-title {
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '22px' : '16px' }};
            font-weight: bold;
            line-height: 1.2;
        }

        .header-sub {
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '15px' : '13px' }};
            font-weight: 600;
            margin-top: 2px;
        }

        .paraf-lembar {
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '14px' : '11px' }};
            font-weight: bold;
            margin-top: {{ ($orientation ?? 'portrait') === 'landscape' ? '6px' : '4px' }};
        }

        /* ===== INFO TABLE ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: {{ ($orientation ?? 'portrait') === 'landscape' ? '12px' : '6px' }};
        }

        .info-table td {
            border: none;
            vertical-align: top;
            padding: 0;
        }

        .info-left {
            width: 72%;
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '14px' : '11px' }};
            line-height: 1.4;
        }

        .info-right {
            width: 28%;
            text-align: right;
        }

        .paraf-box {
            width: {{ ($orientation ?? 'portrait') === 'landscape' ? '110px' : '85px' }};
            height: {{ ($orientation ?? 'portrait') === 'landscape' ? '85px' : '68px' }};
            border: 1.5px solid #333;
            text-align: center;
            display: inline-block;
        }

        .paraf-label {
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '14px' : '11px' }};
            font-weight: bold;
            padding-top: 5px;
        }

        /* ===== TABEL PRODUK ===== */
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            table-layout: fixed; /* mencegah overflow horizontal */
        }

        .product-table th,
        .product-table td {
            border: 1px solid #333;
            padding: {{ ($orientation ?? 'portrait') === 'landscape' ? '8px 7px' : '3px 4px' }};
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '14px' : '11px' }};
            vertical-align: middle;
            word-wrap: break-word;
        }

        .product-table th {
            background-color: #f4f4f4;
            font-weight: bold;
            text-align: center;
            padding: {{ ($orientation ?? 'portrait') === 'landscape' ? '8px 6px' : '4px 3px' }};
        }

        .center { text-align: center; }
        .left   { text-align: left; }

        /* ===== FOOTER ===== */
        .footer-bar {
            margin-top: {{ ($orientation ?? 'portrait') === 'landscape' ? '12px' : '10px' }};
            padding-top: 5px;
            border-top: 1px solid #ccc;
            font-size: {{ ($orientation ?? 'portrait') === 'landscape' ? '11px' : '9px' }};
            color: #555;
            width: 100%;
            overflow: hidden; /* clear float */
        }

        .footer-bar .right {
            float: right;
        }

        /* Optional: pastikan warna tetap muncul saat print */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
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
            @if(!empty($item->grup))
                <span style="font-weight:bold; color:#555;">(Group {{ $item->grup }})</span>
            @endif
            | {{ $item->no_ps ?? '-' }}
        </div>
        <div class="paraf-lembar">
            {{ $jumlah_lembar ?? 1 }} Lembar
        </div>
    </div>

    {{-- ================= INFO + PARAF ================= --}}
    <table class="info-table">
        <tr>
            <td class="info-left">
                <strong>{{ $no_pl ?? $item->no_pl ?? '-' }}</strong>
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
                &nbsp; {{ $data->count() }} | {{ $data->sum('item_qty') ?: $data->sum('qty') ?: $data->count() }}
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
                <th width="{{ ($orientation ?? 'portrait') === 'landscape' ? '17%' : '16%' }}">SKU</th>
                <th width="{{ ($orientation ?? 'portrait') === 'landscape' ? '10%' : '8%' }}">QTY</th>
                <th width="{{ ($orientation ?? 'portrait') === 'landscape' ? '10%' : '9%' }}">CEK</th>
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

    {{-- ================= FOOTER ================= --}}
    <div class="footer-bar">
        <span>
            No. Order: {{ $no_pl ?? $item->no_pl ?? '-' }}
            | Dicetak: {{ now()->format('d/m/Y H:i') }}
            | {{ $jumlah_lembar ?? 1 }} Lembar
        </span>
        <span class="right">1 / 1</span>
    </div>

</div>

</body>
</html>