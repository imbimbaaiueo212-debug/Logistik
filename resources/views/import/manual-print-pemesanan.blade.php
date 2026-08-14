<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Picking Manual</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 7mm 7mm 12mm 7mm; /* bawah lebih besar biar ada ruang footer */
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
        .col-id       { width: 7%; }
        .col-unit     { width: 12%; }
        .col-kategori { width: 18%; }
        .col-bayar    { width: 10%; }
        .col-estimasi { width: 8%; }
        .col-pic      { width: 9%; }
        .col-catatan  { width: 33%; }

        .text-left   { text-align: left; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }

        thead {
            display: table-header-group;
        }
    </style>
</head>
<body>

    @php
        $firstItem  = $data->first();
        $stokisName = $firstItem?->nama_stokis ?? 'Manual';
        $rekapNo    = $docNumber ?? $firstItem?->rekap_number ?? '#M0001';
        $firstDate  = $data->min('created_at') ?? $data->min('tgl_turun_pl') ?? now();
    @endphp

    <!-- ================= HEADER UTAMA ================= -->
    <table style="width:100%; border:none; margin-bottom:12px;">
        <tr>
            <th colspan="8" style="border:none; padding:0;">
                <table style="width:100%; border:none;">
                    <tr>
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
                                    $sku = strtoupper(trim(
                                        $item->manualOrder?->product_sku ?? ''
                                    ));
                                    $sku = preg_replace('/[^A-Z0-9]/', '', $sku);

                                    $nama = strtolower(trim(
                                        $item->nama_barang
                                        ?? $item->kategori_order
                                        ?? ''
                                    ));

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

                        <td style="width:75%; text-align:center; font-size:15px; font-weight:bold; color:#000000; border:none;">
                            Rekap Aktual Detail - Picking {{ $stokisName }}
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

                <td class="col-id text-center">
                    {{ $item->no_pl ?? '-' }}
                    @if(!empty($item->no_ps))
                        <div style="font-size:8px; color:#64748b;">PS: {{ $item->no_ps }}</div>
                    @endif
                </td>

                <td class="col-unit text-left">{{ $item->nama_unit ?? '-' }}</td>

                <td class="col-kategori text-center">
                    {{ $item->nama_barang ?? $item->kategori_order ?? 'Lainnya' }}
                </td>

                <td class="col-bayar text-center">
                    {{ $item->tgl_bayar
                        ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i')
                        : 'Pending' }}
                </td>

                <td class="col-estimasi text-center">
                    {{ $item->tgl_estimasi
                        ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y')
                        : '-' }}<br>
                    <span style="font-size:8.5px;">{{ $item->estimasi_hari ?? 0 }} Hari</span>
                </td>

                <td class="col-pic text-center"></td>

                <td class="col-catatan text-left" style="font-size:8.8px;">
                    @php
                        $raw = $item->ket
                            ?? $item->manualOrder?->catatan
                            ?? $item->manualOrder?->notes
                            ?? '';

                        $lines = preg_split('/\r\n|\r|\n/', $raw);
                        $cleanLines = [];

                        foreach ($lines as $line) {
                            $line = trim($line);
                            if ($line === '') continue;

                            if (preg_match('/^CP\s*:/i', $line)) continue;
                            if (preg_match('/NAMA_MISMATCH/i', $line)) continue;
                            if (preg_match('/Di\s+proses\s+bulk\s+pada/i', $line)) continue;
                            if (preg_match('/^[\|\s\-]+$/', $line)) continue;

                            if (preg_match('/^(.*?)\s*\|?\s*CP\s*:.*$/i', $line, $m)) {
                                $line = trim($m[1]);
                                if ($line === '' || preg_match('/^[\|\s\-]+$/', $line)) continue;
                            }

                            $cleanLines[] = $line;
                        }

                        $display = implode(' ', $cleanLines);
                        $display = trim(preg_replace('/\s+/', ' ', $display));
                        $display = trim(preg_replace('/\s*\|\s*/', ' ', $display));

                        $catatan = $display !== '' ? strtoupper($display) : '-';
                    @endphp

                    {{ \Illuminate\Support\Str::limit($catatan, 75) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ================= NOMOR HALAMAN ================= -->
    <script type="text/php">
        if (isset($pdf)) {
            $font = $fontMetrics->getFont("DejaVu Sans");
            $size = 8;
            $color = array(0.42, 0.45, 0.50); // abu-abu

            // Kiri
            $pdf->page_text(
                40,
                $pdf->get_height() - 18,
                "Dicetak oleh : PICKING • {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}",
                $font,
                $size,
                $color
            );

            // Kanan (nomor halaman)
            $text = "Halaman {PAGE_NUM} / {PAGE_COUNT}";
            $width = $fontMetrics->getTextWidth($text, $font, $size);
            $pdf->page_text(
                $pdf->get_width() - $width - 40,
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