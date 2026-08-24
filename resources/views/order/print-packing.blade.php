<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Packing</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            color: #1f2937;
            margin: 0;
            padding: 8px;
            line-height: 1.3;
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
            padding: 5px 2px;
            text-align: center;
            font-size: 9px;
            font-weight: bold;
            line-height: 1.2;
        }

        td {
            padding: 3px 2px;
            font-size: 8.8px;
            color: #374151;
            line-height: 1.25;
        }

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .header1 th {
            background: #ffffff;
            font-size: 9.5px;
            padding: 6px 2px;
        }

        .header2 th {
            background: #ffffff;
            font-size: 9px;
            padding: 4px 2px;
        }

        /* ===== LEBAR KOLOM ===== */
        .col-no         { width: 3%; }
        .col-id         { width: 7%; }
        .col-unit       { width: 14%; }
        .col-kategori   { width: 12%; }
        .col-estimasi   { width: 9%; }
        .col-distribusi { width: 9%; }
        .col-berat      { width: 9%; }
        .col-berat1     { width: 9%; }
        .col-koli       { width: 5%; }
        .col-packing    { width: 9%; }
        .col-catatan    { width: 30%; }

        .text-left   { text-align: left; }
        .text-center { text-align: center; }
        .font-bold   { font-weight: bold; }

        .footer {
            margin-top: 10px;
            text-align: right;
            font-size: 8.5px;
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
    $rekapNo   = $firstItem?->rekap_number ?? '#0001';
    $firstDate = $data->min('created_at') ?? $data->min('tgl_turun_pl');

    // Nama stokis: bedakan Aktif vs Pasif
    if ($is_pasif ?? false) {
        $stokisName = 'Stokis Jakarta Pasif';
    } else {
        $stokisName = $firstItem?->nama_stokis ?? 'Stokis Jakarta Aktif';
        // Jaga-jaga data salah label
        if (str_contains(strtolower($stokisName), 'pasif')) {
            $stokisName = 'Stokis Jakarta Aktif';
        }
    }
@endphp

    <!-- HEADER UTAMA -->
    <table style="width:100%; border:none; margin-bottom:10px;">
        <tr>
            <th colspan="11" style="border:none; padding:0;">
                <table style="width:100%; border:none;">
                    <tr>
                        @php
                                $kategoriJudul = '';

                                // 1. Cek dari request (jika ada)
                                if (request()->has('kategori') && !empty(request('kategori'))) {
                                    $kat = strtolower(request('kategori'));

                                    if (str_contains($kat, 'modul')) {
                                        $kategoriJudul = 'MODUL';
                                    } elseif (str_contains($kat, 'majalah')) {
                                        $kategoriJudul = 'MAJALAH SAHABAT biMBA';
                                    } elseif (str_contains($kat, 'sertifikat')) {
                                        $kategoriJudul = 'SERTIFIKAT';
                                    }
                                }

                                // 2. Fallback: deteksi dari data
                                if (empty($kategoriJudul) && isset($data) && $data->isNotEmpty()) {

                                    $detected = $data->map(function ($item) {
                                        $sku = strtoupper(trim($item->item_sku ?? $item->sku ?? $item->product?->label ?? ''));
                                        $sku = preg_replace('/[^A-Z0-9]/', '', $sku);

                                        $nama = strtolower(trim(
                                            $item->item_name 
                                            ?? $item->nama_barang 
                                            ?? $item->kategori 
                                            ?? $item->kategori_order 
                                            ?? $item->product?->kategori 
                                            ?? ''
                                        ));

                                        if (str_contains($sku, 'STA') && !str_contains($sku, 'STPB')) {
                                            return 'STA - SERTIFIKAT';
                                        }
                                        if (str_contains($sku, 'STPB')) {
                                            return 'STPB - SERTIFIKAT';
                                        }
                                        if (str_contains($nama, 'modul')) {
                                            return 'MODUL';
                                        }
                                        if (str_contains($nama, 'majalah')) {
                                            return 'MAJALAH SAHABAT biMBA';
                                        }
                                        if (str_contains($nama, 'sertifikat') || str_contains($nama, 'surat tanda')) {
                                            return 'SERTIFIKAT';
                                        }

                                        return null;
                                    })
                                    ->filter()
                                    ->unique()
                                    ->values();

                                    if ($detected->count() === 1) {
                                        $kategoriJudul = $detected->first();
                                    } 
                                    elseif ($detected->count() > 1) {
                                        $allSertifikat = $detected->every(function ($val) {
                                            return str_contains($val, 'SERTIFIKAT');
                                        });

                                        if ($allSertifikat) {
                                            $kategoriJudul = 'SERTIFIKAT';
                                        }
                                    }
                                }
                            @endphp

                            <td style="width:75%; text-align:center; font-size:15px; font-weight:bold; color:#000000; border:none;">
                                Rekap Aktual Detail - PACKING {{ $stokisName }}
                                @if($kategoriJudul)
                                    <span style="color:#000000; font-weight:bold;">| {{ $kategoriJudul }}</span>
                                @endif
                                <span style="color:#000000;">{{ $rekapNo }}</span>
                            </td>
                        <td style="width:25%; text-align:center; border:none;">
                            @if($firstDate)
                                <div style="font-size:10px; color:#000000;">Waktu Serah Terima</div>
                                <div style="font-size:10.5px; font-weight:bold;">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </th>
        </tr>
    </table>

    <!-- TABEL UTAMA -->
    <table style="width:100%; table-layout:fixed; border-collapse:collapse;">
        <colgroup>
            <col class="col-no">
            <col class="col-id">
            <col class="col-unit">
            <col class="col-kategori">
            <col class="col-estimasi">
            <col class="col-distribusi">
            <col class="col-berat">
            <col class="col-berat1">
            <col class="col-koli">
            <col class="col-packing">
            <col class="col-catatan">
        </colgroup>

        <thead>
            <tr class="header1">
                <th rowspan="2" class="col-no">NO</th>
                <th colspan="3">DETAIL ORDER</th>
                <th rowspan="2" class="col-estimasi">Leadtime</th>
                <th rowspan="2" class="col-distribusi">DISTRIBUSI</th>
                <th rowspan="2" class="col-berat">BERAT BIMBA SHOP</th>
                <th rowspan="2" class="col-berat1">BERAT AKTUAL</th>
                <th rowspan="2" class="col-koli">JUMLAH KOLI</th>
                <th rowspan="2" class="col-packing">NAMA PACKING</th>
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

                <td class="col-estimasi text-center">
                    {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}<br>
                    <span style="font-size:8px;">{{ $item->estimasi_hari ?? 0 }} Hari</span>
                </td>

                <td class="col-distribusi text-center">
                    {{ $item->pengiriman ?? '-' }}<br>
                    <span style="font-size:8px;">{{ $item->service_pengiriman ?? '-' }}</span>
                </td>

                <td class="col-berat text-center">{{ (int)($item->order_weight ?? 0) }} g</td>
                <td class="col-berat1 text-center"></td>
                <td class="col-koli text-center"></td>
                <td class="col-packing text-center"></td>

                <td class="col-catatan text-left" style="font-size:8.2px;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                        echo \Illuminate\Support\Str::limit(trim($catatan), 70);
                    @endphp
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dicetak oleh : PACKING • {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>