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

        th, td {
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

        tbody tr {
            height: 18px;
        }

        @media print {
            body { margin: 0; padding: 0; background: white; }
            .preview { box-shadow: none; width: 100%; margin: 0; }
            .print-btn { display: none !important; }
        }
    </style>
</head>
<body>

<a href="{{ url('/order/jakarta-pasif/picking-list-pdf/' . ($item->id ?? 0)) }}"
   target="_blank"
   class="print-btn"
   style="position:fixed; top:20px; right:20px; padding:8px 16px;
          background:#7c2d12; color:white; border:none; border-radius:5px;
          cursor:pointer; font-size:14px; z-index:100;
          text-decoration: none; display: inline-block;">
    🖨 CETAK PDF
</a>

<div class="preview">
    <div style="padding: 4mm 5mm 10mm 5mm;">

        <!-- HEADER -->
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
                        ?? $kategori_order
                        ?? ''
                    ));

                    if (str_contains($sku, 'STA') && !str_contains($sku, 'STPB')) {
                        $kategoriJudul = 'STA';
                    } elseif (str_contains($sku, 'STPB')) {
                        $kategoriJudul = 'STPB';
                    } elseif (str_contains($nama, 'modul') || str_contains(strtolower($kategori_order ?? ''), 'modul')) {
                        $kategoriJudul = 'MODUL';
                    } elseif (str_contains($nama, 'majalah') || str_contains(strtolower($kategori_order ?? ''), 'majalah')) {
                        $kategoriJudul = 'MAJALAH SAHABAT biMBA';
                    } elseif (
                        str_contains($nama, 'sertifikat')
                        || str_contains($nama, 'surat tanda')
                        || str_contains(strtolower($kategori_order ?? ''), 'sertifikat')
                    ) {
                        $kategoriJudul = 'SERTIFIKAT';
                    } elseif (str_contains($nama, 'seragam')) {
                        $kategoriJudul = 'SERAGAM';
                    }
                }

                if (empty($kategoriJudul) && !empty($kategori_order)) {
                    $kategoriJudul = strtoupper($kategori_order);
                }
            @endphp

            <span class="header-title" style="font-size: 20px; line-height: 1;">
                PICKING LIST
                @if($kategoriJudul)
                    <span style="font-size: 20px; color: #000000;">| {{ $kategoriJudul }}</span>
                @endif
            </span><br>
            <span style="font-size: 15px; font-weight: 600; margin-top: -8px; display: inline-block;">
                {{ str_replace(['Stokis ', 'Stokis'], '', $item->nama_stokis ?? $picking->vendor ?? 'Jakarta Pasif') }}
            </span>
        </div>

        <!-- INFO -->
        <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:10px;">
            <tr>
                <td style="width:60%; border:none; vertical-align:top;">
                    <strong>{{ $no_pl ?? $item->no_pl ?? '-' }}</strong><br>
                    PayDate: <strong>
                        {{ ($item->tgl_bayar ?? null)
                            ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i')
                            : '-' }}
                    </strong><br>
                    Estimasi: <strong>
                        {{ ($item->tgl_estimasi ?? null)
                            ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y')
                            : '-' }}
                    </strong><br>
                    <strong>{{ $item->nama_unit ?? $picking->nama_unit ?? '-' }}</strong><br>
                    <strong>
                        {{ $jakarta_pasif->kirim
                            ?? $picking->kirim
                            ?? $picking->alamat_kirim
                            ?? '-' }}
                    </strong><br>
                    <strong>
                        {{ $item->pengiriman ?? $picking->ekspedisi ?? '-' }}
                        |
                        {{ $item->service_pengiriman ?? $picking->service_pengiriman ?? '-' }}
                    </strong>
                    {{ $data->count() }} | {{ $data->sum('item_qty') ?? $data->sum('qty') ?? $data->count() }}
                </td>

                <td style="text-align:center; vertical-align:top; width:20%;">
                    <strong style="font-size: 15px;">PARAF</strong><br><br>
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
                    <th width="8%">QTY</th>
                    <th width="10%" class="center">CEK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $index => $row)
                    <tr>
                        <td style="text-align:center; vertical-align:top; padding-top:1px; line-height:1;">
                            {{ $index + 1 }}
                        </td>
                        <td style="text-align:left; vertical-align:top; padding-top:1px; padding-bottom:1px; padding-left:4px; line-height:1;">
                            @php
                                $namaProduk = $row->item_name ?? $row->nama_produk ?? $row->nama_barang ?? '-';
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
                                $sku = trim($row->item_sku ?? $row->sku ?? '');
                                // Bersihkan JKT / JKTP dan tanda -
                                $cleanSku = str_ireplace(['JKTP', 'JKT', '-'], '', $sku);
                                $cleanSku = trim($cleanSku);
                            @endphp
                            <span class="font-medium">{{ $cleanSku ?: '-' }}</span>
                        </td>
                        <td style="text-align:center; vertical-align:top; padding-top:1px; line-height:1;">
                            {{ $row->item_qty ?? $row->qty ?? 1 }}
                        </td>
                        <td class="center"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align:center; padding:12px; color:#999;">
                            Tidak ada item
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>

    <!-- FOOTER -->
    <div class="footer">
        No. Order: {{ $no_pl ?? $item->no_pl ?? '-' }}
        | Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
        biMBA LOGISTIK · Jakarta Pasif
    </div>
</div>

</body>
</html>