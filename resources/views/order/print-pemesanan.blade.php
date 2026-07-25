<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Picking</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9.2px;
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

        th, td {
            border: 1px solid #0000003d;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background: #ffffff;
            color: #111827;
            padding: 6px 2px;
            text-align: center;
            font-size: 9.5px;
            font-weight: bold;
            line-height: 1.25;
        }

        td {
            padding: 4px 2px;
            font-size: 9px;
            color: #374151;
            line-height: 1.3;
        }

        tbody tr:nth-child(even) { background: #fafafa; }

        .header1 th {
            background: #ffffff;
            font-size: 10px;
            padding: 7px 2px;
        }

        .header2 th {
            background: #ffffff;
            font-size: 9.2px;
            padding: 5px 2px;
        }

        /* ===== LEBAR KOLOM ===== */
        .col-no       { width: 3%; }
        .col-id       { width: 5%; }   /* ID ORDER */
        .col-unit     { width: 5%; }   /* NAMA UNIT */
        .col-kategori { width: 18%; }
        .col-bayar    { width: 10%; }
        .col-estimasi { width: 8%; }
        .col-pic      { width: 9%; }
        .col-catatan  { width: 40%; }

        .text-left  { text-align: left; }
        .text-center{ text-align: center; }
        .text-right { text-align: right; }
        .font-bold  { font-weight: bold; }

        .footer {
            margin-top: 15px;
            text-align: right;
            font-size: 8.8px;
            color: #6b7280;
        }

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

    <!-- ================= HEADER UTAMA ================= -->
    <table style="width:100%; border:none; margin-bottom:12px;">
        <tr>
            <th colspan="8" style="border:none; padding:0;">
                <table style="width:100%; border:none;">
                    <tr>
                        <td style="width:75%; text-align:center; font-size:15px; font-weight:bold; color:#000000; border:none;">
                            Rekap Aktual Detail - Picking {{ $stokisName }}
                            <span style="color:#000000;">{{ $rekapNo }}</span>
                        </td>
                        <td style="width:25%; text-align:center; border:none;">
                            @if($firstDate)
                                <div style="font-size:10.5px; color:#000000;">Waktu Serah Terima</div>
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

    <!-- ================= TABEL UTAMA ================= -->
    <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
        <colgroup>
            <col class="col-no">
            <col class="col-id">
            <col class="col-unit">
            <col class="col-kategori">
            <col class="col-bayar">
            <col class="col-estimasi">
            <col class="col-pic">
            <col class="col-catatan">
        </colgroup>

        <thead>
            <tr class="header1">
                <th rowspan="2" class="col-no">NO</th>
                <th colspan="3">DETAIL ORDER</th>
                <th rowspan="2" class="col-bayar">WAKTU BAYAR</th>
                <th rowspan="2" class="col-estimasi">Leadtime</th>
                <th rowspan="2" class="col-pic">PIC</th>
                <th rowspan="2" class="col-catatan">CATATAN</th>
            </tr>
            <tr class="header2">
                <th class="col-id">ID ORDER</th>
                <th class="col-unit">NAMA UNIT</th>
                <th class="col-kategori">KATEGORI</th>
            </tr>
        </thead>

        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="col-no font-bold text-center">{{ $loop->iteration }}</td>
                <td class="col-id text-center">{{ $item->no_pl ?? '-' }}</td>
                <td class="col-unit text-left">{{ $item->nama_unit ?? '-' }}</td>

                <td class="col-kategori text-center">
                    @php
                        $productIds = [];

                        if (!empty($item->product_ids)) {
                            $decodedIds = is_array($item->product_ids)
                                ? $item->product_ids
                                : json_decode($item->product_ids, true);

                            if (is_array($decodedIds)) {
                                $productIds = $decodedIds;
                            }
                        }

                        if (empty($productIds) && !empty($item->product_id)) {
                            $productIds = [$item->product_id];
                        }

                        $products = collect();
                        if (!empty($productIds)) {
                            $products = \App\Models\Product::whereIn('id', $productIds)->get();
                        }

                        if ($products->isEmpty() && $item->product) {
                            $products = collect([$item->product]);
                        }

                        $displayList = $products->map(function ($product) {
                            $kategori = trim($product->kategori ?? '');
                            $kategoriLower = strtolower($kategori);

                            $kategori = preg_replace('/\s*(biMBA|Bimba|AIUEO|Aiueo)\s*/i', ' ', $kategori);
                            $kategori = preg_replace('/\s+/', ' ', trim($kategori));

                            $sku = trim($product->label ?? $product->kode ?? '');

                            if (str_contains($kategoriLower, 'sertifikat')) {
                                return ($sku ? $sku . ' - ' : '') . $kategori;
                            }

                            if (str_contains($kategoriLower, 'majalah')) {
                                return ($sku ? $sku . ' - ' : '') . $kategori;
                            }

                            return $kategori ?: 'Modul';
                        })
                        ->filter()
                        ->unique()
                        ->values();

                        $kategoriDisplay = $displayList->implode(' | ');

                        if (empty($kategoriDisplay)) {
                            $kategoriDisplay = $item->kategori_order ?? 'Lainnya';
                        }
                    @endphp

                    {{ $kategoriDisplay }}
                </td>

                <td class="col-bayar text-center">
                    {{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}
                </td>

                <td class="col-estimasi text-center">
                    {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}<br>
                    <span style="font-size:8.5px;">{{ $item->estimasi_hari ?? 0 }} Hari</span>
                </td>

                <td class="col-pic text-center"></td>

                <td class="col-catatan text-left" style="font-size:8.8px;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                        echo \Illuminate\Support\Str::limit(trim($catatan), 75);
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh : PICKING • {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>