<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual QC OUTGOING</title>
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

        tbody tr:nth-child(even) {
            background: #fafafa;
        }

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

        /* ===== LEBAR KOLOM (sama teknik dengan Picking) ===== */
        .col-no       { width: 3%; }
        .col-id       { width: 5%; }
        .col-unit     { width: 10%; }
        .col-kategori { width: 15%; }
        .col-estimasi { width: 10%; }
        .col-kode     { width: 10%; }
        .col-hasilcek { width: 12%; }
        .col-ceklist  { width: 5%; }
        .col-catatan  { width: 30%; }

        .text-left   { text-align: left; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }

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
            <th colspan="9" style="border:none; padding:0;">
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
    Rekap Aktual Detail - QC OUTGOING {{ $stokisName }}
    @if($kategoriJudul)
        <span style="color:#000000; font-weight:bold;">| {{ $kategoriJudul }}</span>
    @endif
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
            <col class="col-estimasi">
            <col class="col-kode">
            <col class="col-hasilcek">
            <col class="col-ceklist">
            <col class="col-catatan">
        </colgroup>

        <thead>
            <tr class="header1">
                <th rowspan="2" class="col-no">NO</th>
                <th colspan="3">DETAIL ORDER</th>
                <th rowspan="2" class="col-estimasi">Leadtime</th>
                <th colspan="2">PIC QC OUTGOING</th>
                <th rowspan="2" class="col-ceklist">CEKLIST</th>
                <th rowspan="2" class="col-catatan">CATATAN</th>
            </tr>
            <tr class="header2">
                <th class="col-id">ID ORDER</th>
                <th class="col-unit">NAMA UNIT</th>
                <th class="col-kategori">KATEGORI</th>
                <th class="col-kode">KODE</th>
                <th class="col-hasilcek">HASIL CEK</th>
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
                    <span style="font-size:8.5px;">{{ $item->estimasi_hari ?? 0 }} Hari</span>
                </td>

                <td class="col-kode text-center"></td>
                <td class="col-hasilcek text-center"></td>
                <td class="col-ceklist text-center"></td>

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
        Dicetak oleh : QC • {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>