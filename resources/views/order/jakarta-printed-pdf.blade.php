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
        font-family: Arial, Helvetica, sans-serif; 
        font-size: 9.8px; 
        margin: 0;
        padding: 12px;
        line-height: 1.45;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 9.8px;
        border: 2px solid #374151;
        table-layout: fixed;
    }
    
    th, td {
        border: 1px solid #37415171;
        padding: 7px 5px;
        vertical-align: middle;
        text-align: center;
        overflow: hidden;
        word-wrap: break-word;
    }

    .header1 th, 
    .header2 th {
        background-color: #f1f5f9;
        border-bottom: 2px solid #374151;
        font-weight: 700;
        font-size: 9.6px;
    }

    .main-title {
        font-size: 15px;
        font-weight: 700;
        background-color: #f8fafc;
        border-bottom: 2px solid #374151;
        padding: 12px 10px;
    }

    /* ================== HEADER GROUP & BORDER TEBAL ================== */
    .header1 th, 
    .header2 th {
        background-color: #f1f5f9;
        border-bottom: 3px solid #374151;
        font-weight: 600;
    }

    /* Border luar tabel */
    th:first-child, td:first-child { border-left: 3px solid #374151; }
    th:last-child,  td:last-child  { border-right: 3px solid #374151; }

    /* ================== GARIS TEBAL PEMBATAS GROUP ================== */
    
    /* Setelah NO */
    th:nth-child(1),
    td:nth-child(1) { border-right: 3px solid #374151; }

    /* Setelah WAKTU PRINT RA */
    th:nth-child(2),
    td:nth-child(2) { border-right: 3px solid #374151; }

    /* Setelah DETAIL ORDER (setelah KATEGORI) */
    th:nth-child(3),
    td:nth-child(5) { border-right: 3px solid #374151; }

    /* Setelah PENGIRIMAN & SERVICE (setelah SERVICE) */
    th:nth-child(5),
    th:nth-child(4),
    td:nth-child(7) { border-right: 3px solid #374151; }

    /* Setelah PEMBAYARAN (setelah JUMLAH BAYAR) */
    th:nth-child(9),
    th:nth-child(7),
    td:nth-child(9) { border-right: 3px solid #374151; }

    /* Setelah ESTIMASI PERSIAPAN (setelah HARI) */
    th:nth-child(6),
    td:nth-child(11) { border-right: 3px solid #374151; }

    /* Setelah CATATAN */
    th:nth-child(12),
    td:nth-child(12) { border-right: 3px solid #374151; }

    /* ================== LEBAR KOLOM YANG SUDAH DIOPTIMALKAN ================== */
    .col-no          { width: 30px; }
    .col-waktu       { width: 88px; }
    .col-id          { width: 75px; }
    .col-unit        { width: 175px; }
    .col-kategori    { width: 70px; }
    .col-pengiriman  { width: 68px; }
    .col-service     { width: 58px; }
    .col-tglbayar    { width: 85px; }
    .col-nominal     { width: 88px; }
    .col-estimasi    { width: 75px; }
    .col-hari        { width: 50px; }
    .col-catatan     { width: 165px; }
    .col-status      { width: 58px; }

    .text-left { text-align: left; }
    .text-right { text-align: right; }
    .font-bold { font-weight: bold; }
</style>
</head>
<body>

    @php
        $firstItem = $data->first();
        $stokisName = $firstItem?->nama_stokis ?? 'STOKIS JAKARTA AKTIF';
        $rekapNo    = $firstItem?->rekap_number ?? '#0001';
        $firstDate  = $data->min('created_at') ?? $data->min('tgl_turun_pl');
    @endphp

    <table>
        <thead>
            <!-- JUDUL UTAMA -->
            <tr>
                <th colspan="14 " class="main-title" style="padding:0;">
                    <table style="width:100%; border-collapse:collapse; border:none;">
                        <tr>
                            <td style="width:75%; text-align:center; font-weight:700; font-size:13.5px; border:none; padding:8px;">
                                Rekap Aktual Detail - {{ $stokisName }}
                                <span style="color:#4f46e5;">{{ $rekapNo }}</span>
                            </td>
                            <td style="width:25%; text-align:center; border:none; padding:8px; line-height:1.4;">
                                @if($firstDate)
                                    <div style="font-size:11px; color:#64748b; font-weight:600;">Waktu Serah Terima</div>
                                    <div style="font-size:12px; font-weight:700;">
                                        {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </th>
            </tr>

            <!-- GROUP HEADER -->
            <tr class="header1">
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-waktu">WAKTU PRINT RA</th>
                <th colspan="3">DETAIL ORDER</th>
                <th colspan="2">PENGIRIMAN & SERVICE</th>
                <th colspan="2">PEMBAYARAN</th>
                <th colspan="2">ESTIMASI PERSIAPAN</th>
                <th rowspan="2" class="col-catatan">CATATAN</th>
                <th colspan="2">STATUS PRINT</th>
            </tr>

            <tr class="header2">
                <th class="col-id">ID ORDER</th>
                <th class="col-unit">NAMA UNIT</th>
                <th class="col-kategori">KATEGORI</th>
                <th class="col-pengiriman">PENGIRIMAN</th>
                <th class="col-service">SERVICE</th>
                <th class="col-tglbayar">TGL BAYAR</th>
                <th class="col-nominal">JUMLAH BAYAR</th>
                <th class="col-estimasi">TGL ESTIMASI</th>
                <th class="col-hari">HARI</th>
                <th class="col-status">REKAP</th>
                <th class="col-status">PICKING</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="font-bold" style="text-align:center;">{{ $loop->iteration }}</td>
                
                <td>{{ $item->printed_at ? \Carbon\Carbon::parse($item->printed_at)->format('d/m/Y H:i') : '-' }}</td>
                
                <td class="text-left">{{ $item->no_pl ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                
                <td class="text-left">{{ $item->pengiriman ?? '-' }}</td>
                <td class="text-left">{{ $item->service_pengiriman ?? '-' }}</td>
                
                <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>
                
                <td class="text-right font-bold">
                    Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}
                </td>
                
                <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $item->estimasi_hari ?? 0 }} Hari</td>
                
                <td class="text-left" style="font-size:9.2px;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                        echo Str::limit(trim($catatan), 70);
                    @endphp
                </td>
                
                <td style="text-align:center; font-size:10.5px;">
                    @if($item->printed_at) Sudah @else Belum @endif
                </td>
                <td style="text-align:center; font-size:10.5px;">
                    @if($item->picking_printed_at) Sudah @else Belum @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>