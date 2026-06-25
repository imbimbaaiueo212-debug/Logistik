<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Detail</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;           /* dikecilkan */
            margin: 0;
            padding: 10px;
            line-height: 1.35;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            table-layout: auto;
        }

        th,
        td{
            border:1px solid #374151;
            padding-top:1px;
            padding-bottom:1px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:top;
            text-align:center;
            line-height:1;
        }

        .header1 th, 
        .header2 th {
            background-color: #f1f5f9;
            border-bottom: 1px solid #374151;
            font-weight: 700;
            font-size: 9px;
        }

        .main-title {
            font-size: 14px;
            font-weight: 700;
            background-color: #f8fafc;
            border-bottom: 2px solid #374151;
            padding: 6px 8px;
        }

        /* ================== LEBAR KOLOM BARU (DIKECILKAN) ================== */
        .col-no          { width: 25px; }
        .col-id          { width: 68px; }
        .col-unit        { width: 135px; }
        .col-kategori    { width: 65px; }
        .col-distribusi  { width: 30px;}
        .col-tglbayar    { width: 50px; }
        .col-nominal     { width: 50px; }
        .col-estimasi    { width: 20px; }
        .col-hari        { width: 45px; }
        .col-catatan     { width: 145px; }
        .col-status      { width: 25px; }

        .text-left  { text-align: left; }
        .text-right { text-align: right; }
        .font-bold  { font-weight: bold; }

        /* Signature */
        .signature-table td {
            border: none;
            padding: 3px;
            vertical-align: top;
            width: 20%;
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
                    Waktu Serah Terima
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
    <table>
        <thead>
            <tr class="header1">
                <th rowspan="2" class="col-no" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">NO</th>
                <th colspan="3">DETAIL ORDER</th>
                <th rowspan="2" class="col-distribusi" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">DISTRIBUSI</th>
                <th colspan="2">PEMBAYARAN</th>
                <th rowspan="2" class="col-estimasi" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">WAKTU ESTIMASI PERSIAPAN</th>
                <th rowspan="2" class="col-catatan" style="border:1px solid #374151;
            padding-top:1px;
            padding-bottom:2px;
            padding-left:3px;
            padding-right:3px;
            vertical-align:middle;
            text-align:center;
            line-height:1;">CATATAN</th>
                <th rowspan="2" class="col-status" style="border:1px solid #374151;
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
                <td class="text-center">{{ $item->nama_barang ?? '-' }}</td>
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

    <div class="footer" style="margin-top:10px; font-size:9px;">
        Dicetak oleh : Pricing
    </div>

</body>
</html>