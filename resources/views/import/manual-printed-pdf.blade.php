<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Manual Detail</title>
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
    padding: 0;
    line-height: 1.3;
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
    padding: 5px 4px;
    text-align: center;
    vertical-align: middle;
    font-size: 9.5px;
    font-weight: bold;
    line-height: 1.25;
}

td {
    border: 1px solid #0000003d;
    padding: 3px 4px;
    font-size: 9px;
    color: #374151;
    vertical-align: top;
    line-height: 1.25;
    word-wrap: break-word;
}

tbody tr:nth-child(even) { background: #fafafa; }
tbody tr:nth-child(odd)  { background: #ffffff; }

.header1 th {
    background: #dbeafe;
    border: 1px solid #0000003d;
    font-size: 10px;
    font-weight: 700;
    padding: 6px 4px;
}

.header2 th {
    background: #dbeafe;
    border: 1px solid #0000003d;
    font-size: 9px;
    font-weight: 700;
    padding: 5px 4px;
}

.text-left   { text-align: left; }
.text-center { text-align: center; }
.text-right  { text-align: right; }
.font-bold   { font-weight: bold; }

.main-table {
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
}

.main-table .col-no         { width: 4%; }
.main-table .col-id         { width: 7%; }
.main-table .col-unit       { width: 12%; }
.main-table .col-kategori   { width: 17%; }
.main-table .col-distribusi { width: 8%; }
.main-table .col-tglbayar   { width: 9%; }
.main-table .col-nominal    { width: 10%; }
.main-table .col-estimasi   { width: 9%; }
.main-table .col-catatan    { width: 14%; }
.main-table .col-status     { width: 10%; }

.footer {
    margin-top: 10px;
    border-top: 1px solid #d1d5db;
    padding-top: 4px;
    text-align: right;
    font-size: 8px;
    color: #6b7280;
}
</style>
</head>
<body>

@php
    $firstItem  = $data->first();

    // Hilangkan suffix / Majalah, / Modul, / Sertifikat
    $stokisName = $firstItem?->nama_stokis ?? 'Manual';
    $stokisName = preg_replace('/\s*\/\s*(Majalah|Modul|Sertifikat).*$/i', '', $stokisName);
    $stokisName = trim($stokisName) ?: 'Manual';

    $rekapNo   = $docNumber ?? $firstItem?->rekap_number ?? '#M0001';
    $firstDate = $data->min('created_at') ?? $data->min('tgl_turun_pl') ?? now();
@endphp

<!-- ================= HEADER ================= -->
<table style="width:100%; border:none; border-collapse:collapse; margin-bottom:10px;">
    <tr>
        <td style="width:15%; border:none;"></td>

        <td style="width:70%; border:none; text-align:center; vertical-align:middle; padding-bottom:6px;">
            @php
                $kategoriJudul = '';

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

                if (empty($kategoriJudul) && $data->isNotEmpty()) {
                    $detected = $data->map(function ($item) {
                        $nama = strtolower(trim(
                            $item->nama_barang
                            ?? $item->kategori_order
                            ?? ''
                        ));
                        $sku = strtoupper(trim($item->manualOrder?->product_sku ?? ''));

                        if (str_contains($sku, 'STA') && !str_contains($sku, 'STPB')) {
                            return 'STA - SERTIFIKAT';
                        }
                        if (str_contains($sku, 'STPB')) {
                            return 'STPB - SERTIFIKAT';
                        }
                        if (str_contains($nama, 'modul') || str_contains(strtolower($item->kategori_order ?? ''), 'modul')) {
                            return 'MODUL';
                        }
                        if (str_contains($nama, 'majalah') || preg_match('/\bM\d{2,4}\b/i', $sku) || str_contains(strtolower($item->kategori_order ?? ''), 'majalah')) {
                            return 'MAJALAH SAHABAT biMBA';
                        }
                        if (str_contains($nama, 'sertifikat') || str_contains(strtolower($item->kategori_order ?? ''), 'sertifikat')) {
                            return 'SERTIFIKAT';
                        }
                        return null;
                    })
                    ->filter()
                    ->unique()
                    ->values();

                    if ($detected->count() === 1) {
                        $kategoriJudul = $detected->first();
                    } elseif ($detected->count() > 1) {
                        $allSertifikat = $detected->every(fn ($val) => str_contains($val, 'SERTIFIKAT'));
                        if ($allSertifikat) {
                            $kategoriJudul = 'SERTIFIKAT';
                        }
                    }
                }
            @endphp

            <div style="color:#000000; font-size:15px; font-weight:bold; margin-bottom:6px;">
                Rekap Aktual Detail - {{ $stokisName }}
                @if($kategoriJudul)
                    <span style="color:#000000; font-weight:bold;">| {{ $kategoriJudul }}</span>
                @endif
                <span style="color:#4f46e5; font-weight:bold;">{{ $rekapNo }}</span>
                @if(!empty($firstItem?->no_ps))
                    <span style="color:#4f46e5; font-weight:bold;"> | {{ $firstItem->no_ps }}</span>
                @endif
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

        <td style="width:15%; border:none; text-align:center; vertical-align:middle;">
            @php $allPrinted = $data->every(fn($item) => !is_null($item->printed_at)); @endphp
            <div style="font-size:10.5px; font-weight:bold; color:#64748b; margin-bottom:3px;">
                STATUS RA & PL (PDF)
            </div>
            <div style="font-size:14px; font-weight:bold; color:{{ $allPrinted ? '#10b981' : '#ef4444' }};">
                 {{ $allPrinted ? 'PRINT' : 'BELUM' }}
            </div>
        </td>
    </tr>

    <!-- TANDA TANGAN -->
    <tr>
    <td colspan="3" style="border:1px solid #374151; padding:4px 6px 8px 6px;">
        <table style="width:100%; border:none; border-collapse:collapse;">
            <tr>
                @foreach(['PRICING','PICKING','QC OUTGOING','PACKING','EKSPEDISI'] as $bagian)
                <td style="border:none; text-align:center; vertical-align:top; padding:0;">
                    <div style="font-size:9.5px; font-weight:bold; margin:0 0 28px 0; line-height:1.2;">
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
        <col style="width:4%;">
        <col style="width:5%;">
        <col style="width:12%;">
        <col style="width:17%;">
        <col style="width:8%;">
        <col style="width:9%;">
        <col style="width:10%;">
        <col style="width:9%;">
        <col style="width:14%;">
        <col style="width:10%;">
    </colgroup>

    <thead>
        <tr class="header1">
            <th rowspan="2" class="col-no">NO</th>
            <th colspan="3">DETAIL ORDER</th>
            <th rowspan="2" class="col-distribusi">DISTRIBUSI</th>
            <th colspan="2" class="col-nominal">PEMBAYARAN</th>
            <th rowspan="2" class="col-estimasi">ESTIMASI (WAKTU)</th>
            <th rowspan="2" class="col-catatan">CATATAN</th>
            <th rowspan="2" class="col-status">STATUS PRINT</th>
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
            <td class="font-bold text-center">{{ $loop->iteration }}</td>

            {{-- ID ORDER + No. PS --}}
            <td class="text-center">
                {{ $item->no_pl ?? '-' }}
                @if(!empty($item->no_ps))
                    <div style="font-size:8px; color:#64748b;">PS: {{ $item->no_ps }}</div>
                @endif
            </td>

            <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>

            {{-- KATEGORI (Manual: pakai nama_barang / kategori_order) --}}
            <td class="text-center">
                @php
                    $kategoriDisplay = $item->nama_barang
                        ?? $item->kategori_order
                        ?? 'Lainnya';
                @endphp
                <div class="font-medium">{{ $kategoriDisplay }}</div>
            </td>

            {{-- DISTRIBUSI --}}
            <td style="padding:1px 2px; vertical-align:top; text-align:center;">
                <div style="margin:0; padding:0; line-height:1;">
                    {{ $item->pengiriman ?? '-' }}
                </div>
                <div style="margin:0; padding:0; line-height:1;">
                    {{ $item->service_pengiriman ?? '-' }}
                </div>
            </td>

            {{-- TGL BAYAR (null = Pending) --}}
            <td class="text-center">
                {{ $item->tgl_bayar
                    ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i')
                    : 'Pending' }}
            </td>

            <td class="text-right font-bold">
                Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}
            </td>

            {{-- ESTIMASI --}}
            <td style="padding:1px 2px; vertical-align:top; text-align:center;">
                <div style="margin:0; padding:0; line-height:1;">
                    {{ $item->tgl_estimasi
                        ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y')
                        : '-' }}
                </div>
                <div style="margin:0; padding:0; line-height:1;">
                    {{ $item->estimasi_hari ?? 0 }} Hari
                </div>
            </td>

            {{-- CATATAN --}}
           {{-- CATATAN --}}
                <td class="text-center" style="font-size:8.8px;">
                    @php
                        $raw = $item->ket
                            ?? $item->manualOrder?->catatan
                            ?? $item->manualOrder?->notes
                            ?? '';

                        // Bersihkan (sama seperti di index)
                        $display = preg_replace('/^CP:.*$/mi', '', $raw);
                        $display = preg_replace('/^NAMA_MISMATCH.*$/mi', '', $display);
                        $display = preg_replace('/Di\s+proses\s+bulk\s+pada\s+[\d\/:\s]+[:\s]*/i', '', $display);
                        $display = preg_replace('/[\r\n]+/', ' ', $display);
                        $display = preg_replace('/\s*\|\s*/', ' ', $display);
                        $display = trim(preg_replace('/\s+/', ' ', $display));

                        $catatan = $display !== '' ? strtoupper($display) : '-';
                    @endphp

                    {{ \Illuminate\Support\Str::limit($catatan, 65) }}
                </td>

            {{-- STATUS PRINT --}}
            <td class="text-center" style="vertical-align:middle;">
                @if($item->picking_printed_at)
                    <div style="font-size:10px; font-weight:bold; color:#10b981;">
                        &#10003;
                    </div>
                @else
                    <div style="font-size:10px; font-weight:bold; color:#ef4444;">
                        &#10003; BELUM
                    </div>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- FOOTER -->


<script type="text/php">
    if (isset($pdf)) {
        $font = $fontMetrics->getFont("DejaVu Sans");
        $size = 8;
        $color = array(0.42, 0.45, 0.50); // #6b7280

        // Kiri
        $pdf->page_text(
            40, 
            $pdf->get_height() - 18, 
            "Dicetak oleh : Pricing {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}", 
            $font, 
            $size, 
            $color
        );

        // Kanan (nomor halaman)
        $text = "Halaman {PAGE_NUM} / {PAGE_COUNT}";
        $width = $fontMetrics->getTextWidth($text, $font, $size);
        $pdf->page_text(
            $pdf->get_width() - $width , 
            $pdf->get_height() - 18, 
            $text, 
            $font, 
            $size, 
            $color
        );
    }
</script>

</body>
</html>