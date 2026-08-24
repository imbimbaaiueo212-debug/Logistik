<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>PICKING LIST - {{ $no_pl ?? $item->no_pl ?? '-' }}</title>
    <style>
        @page {
            size: A5 portrait;
            margin: 3mm 4mm 5mm 4mm;
        }

        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 10.8px;
            color: #333;
            line-height: 1;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 135mm;
            margin: 0 auto;
            box-sizing: border-box;
        }

        .header {
            text-align: left;
            border-bottom: 1px solid #000000;
            margin-bottom: 8px;
            padding-bottom: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        th, td {
            border: 1px solid #333;
            padding: 3.5px 3px;
            font-size: 13px;
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
    </style>
</head>
<body>

<div class="container">
    <div style="padding: 1mm 2mm 10mm 2mm;">

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
                        ?? $firstRow->nama_produk
                        ?? $firstRow->nama_barang
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
            <span style="font-size: 18px; font-weight: 600; margin-top: -2px; display: inline-block;">
                {{ str_replace(['Stokis ', 'Stokis'], '', $item->nama_stokis ?? $picking->vendor ?? 'Jakarta Pasif') }}
            </span>
        </div>

        <!-- INFO -->
        <table class="info-table" style="margin-bottom: 2px;">
            <tr>
                <td style="width:57%;">
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
                    {{ $data->count() }} | {{ $data->sum('item_qty') ?: $data->sum('qty') ?: $data->count() }}
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
                @forelse($data as $index => $row)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td class="left">
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
                        <td class="center">
                            @php
                                $sku = trim($row->item_sku ?? $row->sku ?? '');
                                $cleanSku = str_ireplace(['JKTP', 'JKT', '-'], '', $sku);
                                $cleanSku = trim($cleanSku);
                            @endphp
                            <span class="font-medium">{{ $cleanSku ?: '-' }}</span>
                        </td>
                        <td class="center">{{ $row->item_qty ?? $row->qty ?? 1 }}</td>
                        <td class="center"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center" style="padding:10px;color:#999;">
                            Tidak ada item
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

    </div>
</div>

{{-- Footer halaman PDF (DomPDF) --}}
<script type="text/php">
if (isset($pdf)) {
    $noOrder = "{{ $no_pl ?? $item->no_pl ?? '-' }}";
    $printed = "{{ now()->format('d/m/Y H:i') }}";

    $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($noOrder, $printed) {
        $font = $fontMetrics->getFont("DejaVu Sans", "normal");

        $canvas->text(
            20,
            575,
            "No. Order: {$noOrder} | Dicetak: {$printed} | Jakarta Pasif",
            $font,
            8
        );

        $canvas->text(
            385,
            575,
            "{$pageNumber} / {$pageCount}",
            $font,
            8
        );
    });
}
</script>

</body>
</html>