<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl ?? '-' }}</title>
    <style>
    @page {
        size: A5 landscape;
        margin: 15mm 18mm 15mm 18mm;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        color: #111;
        font-size: 11px;
        line-height: 1.35;
        /* cadangan jika @page diabaikan DomPDF */
        padding: 4mm;
    }

    .container {
        width: 100%;
        max-width: 100%;
    }

    /* ===== HEADER ===== */
    .header-title {
        font-size: 15px;
        font-weight: 700;
        margin-bottom: 4px;
        letter-spacing: 0.3px;
    }

    .divider {
        border-bottom: 1.5px solid #000;
        margin-bottom: 10px;
    }

    /* ===== INFO SECTION ===== */
    .info-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
    }

    .info-table td {
        vertical-align: top;
        padding: 0;
    }

    .info-left {
        width: 50%;
        padding-right: 18px;
    }

    .info-right {
        width: 50%;
        text-align: right;
        padding-left: 14px;
    }

    .label {
        display: inline-block;
        min-width: 105px;
        font-weight: 700;
        color: #333;
    }

    .info-row {
        margin-bottom: 4px;
    }

    .section-title {
        font-weight: 700;
        margin-top: 8px;
        margin-bottom: 4px;
        border-bottom: 1px solid #999;
        padding-bottom: 2px;
        display: inline-block;
        min-width: 120px;
        font-size: 11px;
    }

    /* Kotak PARAF */
    .paraf-wrapper {
        margin-top: 12px;
        text-align: right;
    }

    .paraf-box {
        width: 78px;
        height: 46px;
        border: 1.4px solid #000;
        text-align: center;
        display: inline-block;
    }

    .paraf-label {
        font-size: 11px;
        font-weight: 700;
        background: #f0f0f0;
        border-bottom: 1px solid #000;
        padding: 2px 0;
    }

    /* ===== PRODUCT TABLE ===== */
    .product-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        margin-top: 6px;
    }

    .product-table th {
        font-weight: 700;
        text-align: left;
        border-bottom: 1.4px solid #000;
        padding: 6px 5px;
        font-size: 11px;
        background: #f5f5f5;
    }

    .product-table td {
        padding: 8px 5px;
        vertical-align: middle;
        border-bottom: 1px solid #ccc;
        font-size: 11.5px;
    }

    .center { text-align: center; }
    .left   { text-align: left; }

    .check-box {
        width: 34px;
        height: 18px;
        border: 1.3px solid #333;
        display: inline-block;
        background: #fff;
    }

    /* ===== WATERMARK GAMBAR ===== */
.watermark-img {
    position: fixed;
    top: 50%;
    left: 50%;
    width: 70%;              /* lebih besar dari sebelumnya */
    max-width: 480px;        /* naikkan batas maksimal */
    height: auto;
    transform: translate(-50%, -50%);  /* centering sempurna */
    opacity: 0.12;
    z-index: -1000;
    pointer-events: none;
}

    /* ===== FOOTER ===== */
    .footer {
        margin-top: 14px;
        padding-top: 6px;
        border-top: 1px solid #aaa;
        font-size: 10px;
        color: #555;
        display: flex;
        justify-content: space-between;
    }

    @media print {
        body {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>
</head>
<body>

<div class="container">

    {{-- WATERMARK GAMBAR --}}
{{-- WATERMARK GAMBAR (base64 - paling stabil di DomPDF) --}}
@php
    $wmPath = public_path('assets/img/logologistik.jpg');
    $wmSrc  = '';
    if (file_exists($wmPath)) {
        $wmData = base64_encode(file_get_contents($wmPath));
        $wmSrc  = 'data:image/png;base64,' . $wmData;
    }
@endphp

@if($wmSrc)
    <img class="watermark-img" src="{{ $wmSrc }}" alt="watermark">
@endif

    @php
        $kategoriJudul = '';
        if ($data->isNotEmpty()) {
            $firstRow = $data->first();
            $skuRaw = strtoupper(trim($firstRow->item_sku ?? $firstRow->sku ?? ''));
            $sku    = preg_replace('/[^A-Z0-9]/', '', $skuRaw);
            $nama = strtolower(trim($firstRow->item_name ?? $firstRow->nama_barang ?? $firstRow->kategori ?? $item->kategori_order ?? ''));

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
    @endphp

    <!-- HEADER -->
    <div class="header-title">
        PICKING LIST @if($kategoriJudul) | {{ $kategoriJudul }} @endif
    </div>
    <div class="divider"></div>

    <!-- INFO -->
    <table class="info-table">
        <tr>
            <!-- KIRI -->
            <td class="info-left">
                <div class="info-row">
                    <span class="label">Group:</span>
                </div>
                <div style="margin-bottom: 6px;">
                    @php
                        $namaProdukGrup = '';
                        if ($data->isNotEmpty()) {
                            $firstItem = $data->first();
                            $namaProdukGrup = $firstItem->item_name ?? $firstItem->nama_barang ?? '';
                            $namaProdukGrup = trim(preg_replace('/\s*\([^)]*\)/', '', $namaProdukGrup));
                        }

                        $stokis = $item->nama_stokis ?? 'Manual';
                        $stokis = str_ireplace(['Stokis ', 'Stokis', 'Majalah'], '', $stokis);
                        $stokis = trim($stokis, ' /');

                        if (empty($stokis)) {
                            $stokis = 'Manual';
                        }
                    @endphp

                    {{ $stokis }}
                    @if(!empty($namaProdukGrup))
                        / {{ $namaProdukGrup }}
                    @endif
                    @if(!empty($item->grup))
                        (Group {{ $item->grup }})
                    @endif
                </div>

                <div class="section-title">Alamat Pengiriman</div>
                <div><strong>{{ $item->nama_unit ?? '-' }}</strong></div>

                @if(!empty($item->manualOrder?->phone))
                    <div>Telp: {{ $item->manualOrder->phone }}</div>
                @endif

                @php
                    $alamat = trim(implode(', ', array_filter([
                        $item->manualOrder?->shipping_address_1,
                        $item->manualOrder?->shipping_address_2,
                    ])));
                @endphp
                @if($alamat !== '')
                    <div>{{ $alamat }}</div>
                @endif

                @if(!empty($item->manualOrder?->shipping_city))
                    <div>Wilayah: <strong>{{ strtoupper($item->manualOrder->shipping_city) }}</strong></div>
                @endif

                @php
                    $cp = null;
                    $rawNotes = $item->manualOrder?->notes ?? $item->manualOrder?->catatan ?? '';
                    if (preg_match('/\bCP:\s*(.+)$/u', $rawNotes, $m)) {
                        $cp = trim($m[1]);
                    }
                @endphp
                @if(!empty($cp))
                    <div>CP: {{ $cp }}</div>
                @endif
            </td>

            <!-- KANAN -->
            <td class="info-right">
                <div class="info-row">
                    <span class="label">Picking List Ref:</span>
                    <strong>{{ $no_pl ?? $item->no_pl ?? '-' }}</strong>
                </div>
                <div class="info-row">
                    <span class="label">No. PS:</span>
                    {{ $item->no_ps ?? '-' }}
                </div>
                <div class="info-row">
                    <span class="label">Kurir / Service:</span>
                    {{ $item->pengiriman ?? $item->ekspedisi ?? '-' }} | {{ $item->service_pengiriman ?? '-' }}
                </div>
                <div class="info-row">
                    <span class="label">Total Lembar:</span>
                    {{ $jumlah_lembar ?? 1 }} Lembar
                </div>
                <div class="info-row">
                    <span class="label">Summary Item:</span>
                    {{ $data->count() }} SKU | {{ $data->sum('item_qty') ?: $data->sum('qty') ?: $data->count() }} Qty
                </div>

                <div class="paraf-wrapper">
                    <div class="paraf-box">
                        <div class="paraf-label">PARAF</div>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TABEL PRODUK -->
    <table class="product-table">
        <thead>
            <tr>
                <th style="width: 6%;">No</th>
                <th style="width: 18%;">Stock Code</th>
                <th>Description</th>
                <th style="width: 10%;" class="center">Qty</th>
                <th style="width: 10%;" class="center">Cek</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $row)
                <tr>
                    <td class="left">{{ $index + 1 }}</td>
                    <td class="left">
                        @php
                            $sku = trim($row->item_sku ?? $row->sku ?? '');
                            $cleanSku = trim(str_ireplace(['JKT', '-'], '', $sku));
                        @endphp
                        <strong>{{ $cleanSku ?: '-' }}</strong>
                    </td>
                    <td class="left">
                        @php
                            $namaProduk = $row->item_name ?? $row->nama_barang ?? '-';
                            if (str_contains(strtolower($namaProduk), 'majalah')) {
                                $namaProduk = trim(preg_replace('/\s*\([^)]*\)/', '', $namaProduk));
                            }
                        @endphp
                        {{ $namaProduk }}
                    </td>
                    <td class="center" style="font-weight: 700; font-size: 14px;">
                        {{ $row->item_qty ?? $row->qty ?? 1 }}
                    </td>
                    <td class="center">
                        <div class="check-box"></div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <div>
            No. Order: {{ $no_pl ?? $item->no_pl ?? '-' }}
            &nbsp;|&nbsp; Dicetak: {{ now()->format('d/m/Y H:i') }}
        </div>
        <div>1 / 1</div>
    </div>

</div>

</body>
</html>