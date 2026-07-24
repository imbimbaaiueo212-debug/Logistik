<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Detail</title>
    <style>

@page{
    size:A4 landscape;
    margin:7mm;
}

body{
    font-family:"DejaVu Sans",sans-serif;
    font-size:9px;
    color:#1f2937;
    margin:0;
    padding:0;
    line-height:1.3;
}

/* ===========================
   TABLE
=========================== */

table{
    width:100%;
    border-collapse:collapse;
    table-layout:fixed;
}

th{
    background:#e8eef7;
    color:#111827;
    border:1px solid #0000003d;
    padding:5px 4px;
    text-align:center;
    vertical-align:middle;
    font-size:9.5px;
    font-weight:bold;
    line-height:1.25;
}

td{
    border:1px solid #0000003d;
    padding:3px 4px;
    font-size:9px;
    color:#374151;
    vertical-align:top;
    line-height:1.25;
    word-wrap:break-word;
}

/* Zebra */

tbody tr:nth-child(even){
    background:#fafafa;
}

tbody tr:nth-child(odd){
    background:#ffffff;
}

/* ===========================
   HEADER
=========================== */

.header1 th{
    background:#dbeafe;
    border:1px solid #0000003d;
    font-size:10px;
    font-weight:700;
    padding:6px 4px;
}

.header2 th{
    background:#dbeafe;
    border:1px solid #0000003d;
    font-size:9px;
    font-weight:700;
    padding:5px 4px;
}

.main-title{
    font-size:18px;
    font-weight:bold;
    color:#1e3a8a;
    text-align:center;
    padding-bottom:8px;
    margin-bottom:8px;
    border-bottom:2px solid #3b82f6;
}

/* ===========================
   TEXT
=========================== */

.text-left{
    text-align:left;
}

.text-center{
    text-align:center;
}

.text-right{
    text-align:right;
}

.font-bold{
    font-weight:bold;
}

//* ===========================
   COLUMN WIDTH
=========================== */

.main-table{
    width:100%;
    table-layout:fixed;
    border-collapse:collapse;
}

/* NO */
.main-table .col-no{
    width:4%;
}

/* DETAIL ORDER */
.main-table .col-id{
    width:7%;
}

.main-table .col-unit{
    width:12%;
}

.main-table .col-kategori{
    width:17%;
}

/* DISTRIBUSI */
.main-table .col-distribusi{
    width:8%;
}

/* PEMBAYARAN */
.main-table .col-tglbayar{
    width:9%;
}

.main-table .col-nominal{
    width:10%;
}

/* ESTIMASI */
.main-table .col-estimasi{
    width:9%;
}

/* CATATAN */
.main-table .col-catatan{
    width:14%;
}

/* STATUS */
.main-table .col-status{
    width:10%;
}

/* ===========================
   SIGNATURE
=========================== */

.signature-table{
    width:100%;
    border:none;
    margin-top:8px;
}

.signature-table td{
    border:none;
    text-align:center;
    padding-top:12px;
    width:20%;
    font-size:9px;
}

.signature-title{
    font-size:10px;
    font-weight:bold;
    margin-bottom:28px;
}

.signature-name{
    margin-top:4px;
    font-size:9px;
}

/* ===========================
   FOOTER
=========================== */

.footer{
    margin-top:10px;
    border-top:1px solid #d1d5db;
    padding-top:4px;
    text-align:right;
    font-size:8px;
    color:#6b7280;
}

/* ===========================
   BADGE
=========================== */

.badge-print{
    display:inline-block;
    padding:2px 6px;
    border:1px solid #16a34a;
    color:#16a34a;
    font-weight:bold;
    border-radius:3px;
    font-size:8px;
}

.badge-pending{
    display:inline-block;
    padding:2px 6px;
    border:1px solid #dc2626;
    color:#dc2626;
    font-weight:bold;
    border-radius:3px;
    font-size:8px;
}

/* ===========================
   UTILITIES
=========================== */

.small{
    font-size:8px;
}

.large{
    font-size:10px;
}

.nowrap{
    white-space:nowrap;
}

.wrap{
    white-space:normal;
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

                    <!-- ================= HEADER ================= -->
                    <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:10px;">
                        <tr>
                            <!-- Spacer kiri -->
                            <td style="width:15%; border:none;"></td>

                            <!-- Judul -->
                            <td style="width:70%; border:none; text-align:center; vertical-align:middle; padding-bottom:6px;">
                                <div style="font-size:15px; font-weight:bold; margin-bottom:6px;">
                                    Rekap Aktual Detail - {{ $stokisName }}
                                    <span style="color:#4f46e5; font-weight:bold;">{{ $rekapNo }}</span>
                                </div>

                                @if($firstDate)
                                <div style="font-size:10.5px; color:#64748b; font-weight:bold; margin-bottom:1px;">
                                    Waktu Rekap & Cetak RA
                                </div>
                                <div style="font-size:11.5px; font-weight:bold; color:#111827;">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>
                                @endif
                            </td>

                            <!-- Status -->
                            <td style="width:15%; border:none; text-align:center; vertical-align:middle;">
                                @php $allPrinted = $data->every(fn($item) => !is_null($item->printed_at)); @endphp
                                <div style="font-size:10.5px; font-weight:bold; color:#64748b; margin-bottom:3px;">
                                    STATUS RA & PL (PDF)
                                </div>
                                <div style="font-size:14px; font-weight:bold; color:{{ $allPrinted ? '#10b981' : '#ef4444' }};">
                                    &#10003; {{ $allPrinted ? 'PRINT' : 'BELUM' }}
                                </div>
                            </td>
                        </tr>

                        <!-- TANDA TANGAN -->
                        <tr>
                            <td colspan="3" style="border:1px solid #374151; padding:10px 8px;">
                                <table style="width:100%; border:none; border-collapse:collapse;">
                                    <tr>
                                        @foreach(['Pricing','Picking','Checking','Packing','Finishing'] as $bagian)
                                        <td style="border:none; text-align:center; vertical-align:top;">
                                            <div style="font-size:9.5px; font-weight:bold; margin-bottom:35px;">
                                                {{ $bagian }}
                                            </div>
                                            <div style="font-size:8px; font-weight:bold; white-space:nowrap;">
                                                Nama __________ &nbsp;&nbsp; Tgl __________
                                            </div>
                                        </td>
                                        @endforeach
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>

                    <!-- TABEL UTAMA -->
                    <table class="main-table">

                    <colgroup>
                        <!-- NO -->
                        <col style="width:4%;">

                        <!-- DETAIL ORDER -->
                        <col style="width:7%;">
                        <col style="width:12%;">
                        <col style="width:17%;">

                        <!-- DISTRIBUSI -->
                        <col style="width:8%;">

                        <!-- PEMBAYARAN -->
                        <col style="width:9%;">
                        <col style="width:10%;">

                        <!-- ESTIMASI -->
                        <col style="width:9%;">

                        <!-- CATATAN -->
                        <col style="width:14%;">

                        <!-- STATUS -->
                        <col style="width:10%;">
                    </colgroup>

                    <thead>
                            <tr class="header1">
                                <th rowspan="2" class="col-no">
                    NO
                </th>
                <th colspan="3">DETAIL ORDER</th>
                <th rowspan="2" class="col-distribusi" style="border:1px solid #0000003d;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">DISTRIBUSI</th>
                <th colspan="2" class="col-nominal">PEMBAYARAN</th>
                <th rowspan="2" class="col-estimasi" style="border:1px solid #0000003d;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">ESTIMASI (WAKTU)</th>
                <th rowspan="2" class="col-catatan">
                    CATATAN
                </th>
                <th rowspan="2" class="col-status" style="border:1px solid #0000003d;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">STATUS PRINT</th>
            </tr>
            <tr class="header2">
                <th class="col-id">ID ORDER</th>
                <th class="col-unit">NAMA UNIT</th>
                <th class="col-kategori">KATEGORI</th>
                <th class="col-tglbayar">TGL BAYAR</th>
                <th class="col-nominal">JUMLAH BAYAR</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="font-bold">{{ $loop->iteration }}</td>
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
                <td style="
                    padding:1px 2px;
                    vertical-align:top;
                    text-align:center;
                ">
                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->pengiriman ?? '-' }}
                    </div>

                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->service_pengiriman ?? '-' }}
                    </div>
                </td>
                <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-right font-bold">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                <td style="
                    padding:1px 2px;
                    vertical-align:top;
                    text-align:center;
                ">
                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}
                    </div>

                    <div style="
                        margin:0;
                        padding:0;
                        line-height:1;
                    ">
                        {{ $item->estimasi_hari ?? 0 }} Hari
                    </div>
                </td>
                <td class="text-center" style="font-size:8.8px fw-bold;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';

                        // Hapus tulisan "Di proses bulk pada dd/mm/yyyy hh:mm:"
                        $catatan = preg_replace(
                            '/Di proses bulk pada \d{2}\/\d{2}\/\d{4} \d{2}:\d{2}:\s*/i',
                            '',
                            $catatan
                        );

                        echo Str::limit(trim($catatan), 65);
                    @endphp
                </td>
                <td style="text-align:center;">
                    
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer" style="margin-top:10px; font-size:9px;">
        Dicetak oleh : Pricing {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>