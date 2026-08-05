<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl ?? '-' }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 5mm 5mm 15mm 5mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        .preview {
            width: 148mm;
            min-height: 210mm;
            margin: 0 auto;
            background: white;
            box-shadow: 0 0 15px rgba(0,0,0,0.15);
        }

        .header {
            text-align: left;
            padding-bottom: 6px;
            border-bottom: 1px solid #000000;
            margin-bottom: 10px;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-top: 20px;
        }

        @media print {
            .footer {
                position: fixed;
                bottom: 5mm;
                left: 5mm;
                right: 5mm;
                margin: 0;
                border-top: 1px solid #ddd;
                padding-top: 5px;
            }
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }

        th,
        td {
            border: 1px solid #333;
            padding-top: 1px;
            padding-bottom: 1px;
            padding-left: 3px;
            padding-right: 3px;
            vertical-align: top;
            line-height: 1;
            font-size: 13px;
        }

        th {
            background: #f4f4f4;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            line-height: 1;
            padding-top: 2px;
            padding-bottom: 2px;
        }

        @media print {
            body { margin: 0; padding: 0; background: white; }
            .preview { box-shadow: none; width: 100%; margin: 0; }
            .print-btn { display: none !important; }
        }

        tbody tr {
            height: 18px;
        }
    </style>
</head>
<body>

{{-- Tombol PDF Manual --}}
<a href="{{ route('import.manual-printed.picking-pdf', $item->id ?? $id ?? '') }}"
   target="_blank"
   class="print-btn"
   style="position:fixed; top:20px; right:20px; padding:8px 16px;
          background:#1e40af; color:white; border:none; border-radius:5px;
          cursor:pointer; font-size:14px; z-index:100;
          text-decoration: none; display: inline-block;">
    🖨 CETAK PDF
</a>

<div class="preview">
    <div style="padding: 4mm 5mm 10mm 5mm;">

        {{-- HEADER --}}
        <div class="header" style="margin-bottom: 8px;">
            @php
                $kategoriJudul = '';

                if ($data->isNotEmpty()) {
                    $firstRow = $data->first();

                    $skuRaw = strtoupper(trim($firstRow->item_sku ?? $firstRow->sku ?? ''));
                    $sku = preg_replace('/[^A-Z0-9]/', '', $skuRaw);

                    $nama = strtolower(trim(
                        $firstRow->item_name
                        ?? $firstRow->nama_barang
                        ?? $firstRow->kategori
                        ?? $firstRow->kategori_order
                        ?? $kategori_order
                        ?? ''
                    ));

                    if (str_contains($sku, 'STA') && !str_contains($sku, 'STPB')) {
                        $kategoriJudul = 'STA';
                    } elseif (str_contains($sku, 'STPB')) {
                        $kategoriJudul = 'STPB';
                    } elseif (str_contains($nama, 'modul')) {
                        $kategoriJudul = 'MODUL';
                    } elseif (str_contains($nama, 'majalah')) {
                        $kategoriJudul = 'MAJALAH SAHABAT biMBA';
                    } elseif (str_contains($nama, 'sertifikat') || str_contains($nama, 'surat tanda')) {
                        $kategoriJudul = 'SERTIFIKAT';
                    } elseif (str_contains($nama, 'seragam')) {
                        $kategoriJudul = 'SERAGAM';
                    }
                }

                // Fallback dari variabel controller
                if (empty($kategoriJudul) && !empty($kategori_order)) {
                    $kategoriJudul = strtoupper($kategori_order);
                }

                if (empty($kategoriJudul) && request()->has('kategori')) {
                    $kategoriJudul = strtoupper(request('kategori'));
                }
            @endphp

            <span class="header-title" style="font-size: 20px; line-height: 1;">
                PICKING LIST
                @if($kategoriJudul)
                    <span style="font-size: 20px; color: #000000;">| {{ $kategoriJudul }}</span>
                @endif
            </span><br>
            <span style="font-size: 15px; font-weight: 600; margin-top: -8px; display: inline-block;">
                {{ str_replace(['Stokis ', 'Stokis'], '', $item->nama_stokis ?? 'Manual / Majalah') }}
                @if(!empty($item->grup))
                    <span style="font-size:13px; font-weight:bold; color:#555;">(Group {{ $item->grup }})</span>
                @endif
                | {{ $item->no_ps ?? '-' }}
            </span>
        </div>

        {{-- INFO --}}
        <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:10px;">
            <tr>
                <td style="width:60%; border:none; vertical-align:top;">
                    <strong>{{ $no_pl ?? $item->no_pl ?? '-' }}</strong>
                    <br>

                    <strong>{{ $item->nama_unit ?? '-' }}</strong><br>

                    {{-- Telepon unit --}}
                    @if(!empty($item->manualOrder?->phone))
                        Telp: {{ $item->manualOrder->phone }}<br>
                    @endif

                    {{-- Alamat (tanpa shipping_city) --}}
                    @php
                        $alamat = trim(implode(', ', array_filter([
                            $item->manualOrder?->shipping_address_1,
                            $item->manualOrder?->shipping_address_2,
                        ])));
                    @endphp
                    @if($alamat !== '')
                        {{ $alamat }}<br>
                    @endif

                    {{-- Wilayah (dari shipping_city) --}}
                    @if(!empty($item->manualOrder?->shipping_city))
                        Wilayah: <strong>{{ $item->manualOrder->shipping_city }}</strong><br>
                    @endif


                    {{-- Contact person --}}
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
    <td style="text-align:center; vertical-align:top; width:20%;">
        <strong style="font-size: 15px;">PARAF</strong><br><br>
    </td>
</tr>
        </table>

        {{-- TABEL PRODUK --}}
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
                    <td style="text-align:center; vertical-align:top; padding-top:1px; line-height:1;">
                        {{ $index + 1 }}
                    </td>
                    <td style="text-align:left; vertical-align:top; padding-top:1px; padding-bottom:1px; padding-left:4px; line-height:1;">
                        @php
                            $namaProduk = $row->item_name ?? $row->nama_barang ?? '-';
                            $isMajalah = str_contains(strtolower($namaProduk), 'majalah');

                            if ($isMajalah) {
                                $namaProduk = preg_replace('/\s*\([^)]*\)/', '', $namaProduk);
                                $namaProduk = trim($namaProduk);
                            }
                        @endphp
                        {{ $namaProduk }}
                    </td>
                    <td style="text-align:center; vertical-align:top; padding-top:1px; line-height:1;">
                        @php
                            $sku = trim($row->item_sku ?? '');
                            // Hapus prefix JKT / tanda -
                            $cleanSku = str_ireplace(['JKT', '-'], '', $sku);
                            $cleanSku = trim($cleanSku);
                        @endphp
                        <span class="font-medium">{{ $cleanSku ?: '-' }}</span>
                    </td>
                    <td style="text-align:center; vertical-align:top; padding-top:1px; line-height:1;">
                        {{ $row->item_qty ?? $row->qty ?? 1 }}
                    </td>
                    <td class="center"></td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    {{-- FOOTER --}}
    <div class="footer">
        No. Order: {{ $no_pl ?? $item->no_pl ?? '-' }}
        | Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
        biMBA LOGISTIK — MANUAL
    </div>
</div>

</body>
</html>