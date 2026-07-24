<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual DISTRIBUSI</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 8px;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background: #e8eef7;
            color: #111827;
            border: 1px solid #0000003d;
            padding: 6px 5px;
            text-align: center;
            vertical-align: middle;
            font-size: 9.5px;
            font-weight: bold;
            line-height: 1.25;
        }

        td {
            border: 1px solid #0000003d;
            padding: 4px 5px;
            font-size: 9px;
            color: #374151;
            vertical-align: top;
            line-height: 1.3;
            word-wrap: break-word;
        }

        /* Zebra */
        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        /* Header Group */
        .header1 th {
            background: #dbeafe;
            font-size: 10px;
            padding: 7px 5px;
        }

        .header2 th {
            background: #eff6ff;
            font-size: 9.2px;
            padding: 5px 4px;
        }

        .header-cell {
            padding: 6px 5px;
            vertical-align: middle;
            text-align: center;
        }

        /* Column Width */
        .col-no         { width: 32px; }
        .col-id         { width: 65px; }
        .col-unit       { width: 150px; }
        .col-kategori   { width: 80px; }
        .col-distribusi { width: 85px; }
        .col-estimasi   { width: 78px; }
        .col-cek        { width: 55px; }
        .col-catatan    { width: 170px; }

        .text-left  { text-align: left; }
        .text-right { text-align: right; }
        .font-bold  { font-weight: bold; }

        /* Footer */
        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8.8px;
            color: #6b7280;
        }

        /* ===== PENTING: REPEAT HEADER DI SETIAP HALAMAN ===== */
        thead {
            display: table-header-group;
        }
    </style>
</head>
<body>

    @php
        $firstItem = $data->first();
        $stokisName = $firstItem?->nama_stokis ?? 'STOKIS JAKARTA AKTIF';
        $rekapNo    = $firstItem?->rekap_number ?? '#0001';
        $firstDate  = $data->min('created_at') ?? $data->min('tgl_turun_pl');
    @endphp

    <!-- ================= HEADER UTAMA (Hanya di halaman pertama) ================= -->
    <table style="width:100%; border:none; margin-bottom:12px;">
        <tr>
            <th colspan="8" style="border:none; padding:0;">
                <table style="width:100%; border:none;">
                    <tr>
                        <td style="width:75%; text-align:center; font-size:15px; font-weight:bold; color:#1e3a8a;">
                            Rekap Aktual Detail - EKSPEDISI {{ $stokisName }}
                            <span style="color:#4f46e5;">{{ $rekapNo }}</span>
                        </td>
                        <td style="width:25%; text-align:center;">
                            @if($firstDate)
                                <div style="font-size:10.5px; color:#64748b;">Waktu Serah Terima</div>
                                <div style="font-size:11px; font-weight:bold;">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </th>
        </tr>
    </table>

    <!-- ================= TABEL UTAMA (Header akan repeat) ================= -->
    <table>
        <thead>
            <!-- HEADER GROUP -->
            <tr class="header1">
                <th rowspan="2" class="col-no header-cell">NO</th>
                <th colspan="3" class="header-cell">DETAIL ORDER</th>
                <th rowspan="2" class="col-distribusi header-cell">DISTRIBUSI</th>
                <th rowspan="2" class="col-estimasi header-cell">ESTIMASI (WAKTU)</th>
                <th rowspan="2" class="col-cek header-cell">CEK LIST</th>
                <th rowspan="2" class="col-catatan header-cell">CATATAN</th>
            </tr>

            <tr class="header2">
                <th class="col-id header-cell">ID ORDER</th>
                <th class="col-unit header-cell">NAMA UNIT</th>
                <th class="col-kategori header-cell">KATEGORI</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="font-bold text-center">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->no_pl ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-center text-sm">
    @php
        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA PRODUCT ID
        |--------------------------------------------------------------------------
        */

        $productIds = [];

        if (!empty($item->product_ids)) {
            $decodedIds = is_array($item->product_ids)
                ? $item->product_ids
                : json_decode($item->product_ids, true);

            if (is_array($decodedIds)) {
                $productIds = $decodedIds;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK PRODUCT ID UTAMA
        |--------------------------------------------------------------------------
        */

        if (empty($productIds) && !empty($item->product_id)) {
            $productIds = [$item->product_id];
        }

        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA PRODUCT
        |--------------------------------------------------------------------------
        */

        $products = collect();

        if (!empty($productIds)) {
            $products = \App\Models\Product::whereIn('id', $productIds)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK RELASI PRODUCT
        |--------------------------------------------------------------------------
        */

        if ($products->isEmpty() && $item->product) {
            $products = collect([$item->product]);
        }

        /*
        |--------------------------------------------------------------------------
        | TAMPILKAN SKU TERLEBIH DAHULU, BARU KATEGORI
        |--------------------------------------------------------------------------
        */

        $displayList = $products
            ->map(function ($product) {

                $kategori = trim(
                    $product->kategori ?? ''
                );

                $kategoriLower = strtolower($kategori);

                /*
                |--------------------------------------------------------------------------
                | AMBIL SKU / LABEL
                |--------------------------------------------------------------------------
                */

                $sku = trim(
                    $product->label
                    ?? $product->kode
                    ?? ''
                );

                /*
                |--------------------------------------------------------------------------
                | KHUSUS SERTIFIKAT
                |--------------------------------------------------------------------------
                */

                if (str_contains($kategoriLower, 'sertifikat')) {

                    return ($sku ? $sku . ' - ' : '')
                        . $kategori;
                }

                /*
                |--------------------------------------------------------------------------
                | KHUSUS MAJALAH
                |--------------------------------------------------------------------------
                */

                if (str_contains($kategoriLower, 'majalah')) {

                    return ($sku ? $sku . ' - ' : '')
                        . $kategori;
                }

                /*
                |--------------------------------------------------------------------------
                | KATEGORI LAIN
                |--------------------------------------------------------------------------
                */

                return $kategori;
            })
            ->filter()
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | GABUNG DENGAN |
        |--------------------------------------------------------------------------
        */

        $kategoriDisplay = $displayList->implode(' | ');

        /*
        |--------------------------------------------------------------------------
        | FALLBACK
        |--------------------------------------------------------------------------
        */

        if (empty($kategoriDisplay)) {
            $kategoriDisplay = $item->kategori_order ?? 'Lainnya';
        }
    @endphp

    <div class="font-medium">
        {{ $kategoriDisplay }}
    </div>
</td>

                <td class="text-center">
                    {{ $item->pengiriman ?? '-' }}<br>
                    <span style="font-size:8.5px;">{{ $item->service_pengiriman ?? '-' }}</span>
                </td>

                <td class="text-center">
                    {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}<br>
                    <span style="font-size:8.5px;">{{ $item->estimasi_hari ?? 0 }} Hari</span>
                </td>

                <td class="text-center"></td>

                <td class="text-left" style="font-size:8.8px;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                        echo Str::limit(trim($catatan), 80);
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh : DISTRIBUSI • {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>