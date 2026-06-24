<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Rekap Aktual Detail</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm;
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
            border: 1px solid #374151;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid #374151;
            padding: 7px 5px;
            vertical-align: middle;
            text-align: center;
            overflow: hidden;
            word-wrap: break-word;
        }

        .header1 th, 
        .header2 th {
            background-color: #f1f5f9;
            border-bottom: 1px solid #374151;
            font-weight: 700;
            font-size: 9.6px;
        }

        .main-title {
            font-size: 15px;
            font-weight: 700;
            background-color: #f8fafc;
            border-bottom: 2px solid #374151;
            padding: 8px 10px;
        }

        /* Lebar Kolom */
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

        /* Signature Kotak */
        .signature-table td {
            border: none;
            padding: 3px;
            vertical-align: top;
            width: 72px;
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

    <!-- JUDUL + WAKTU + TANDA TANGAN (DIGABUNG) -->
    <table style="width:100%; margin-bottom: 15px; border: 1px solid #374151;">
        <tr>
            <!-- Baris Judul & Waktu -->
            <td colspan="5" style="border-bottom: 1px solid #374151; padding: 8px;">
                <table style="width:100%; border:none;">
                    <tr>
                        <td style="width:75%; text-align:center; font-weight:700; font-size:13.5px;">
                            Rekap Aktual Detail - {{ $stokisName }}
                            <span style="color:#4f46e5;">{{ $rekapNo }}</span>
                        </td>
                        <td style="width:25%; text-align:center; line-height:1.4;">
                            @if($firstDate)
                                <div style="font-size:11px; color:#64748b; font-weight:600;">Waktu Serah Terima</div>
                                <div style="font-size:12px; font-weight:700;">
                                    {{ \Carbon\Carbon::parse($firstDate)->format('d/m/Y H:i:s') }}
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <!-- Baris Tanda Tangan (Kotak) -->
        <tr>
            <td style="border:none; padding:4px;">
                <div style="border:1px solid #374151; height:65px; position:relative;">
                    <div style="padding:6px 4px 0; font-weight:bold; font-size:9px; text-align:center;">Pricing</div>
                    <div style="position:absolute; bottom:8px; left:0; right:0; text-align:center; font-size:8.5px;">
                        <strong>Nama/Tgl</strong>
                    </div>
                </div>
            </td>
            <td style="border:none; padding:4px;">
                <div style="border:1px solid #374151; height:65px; position:relative;">
                    <div style="padding:6px 4px 0; font-weight:bold; font-size:9px; text-align:center;">Picking</div>
                    <div style="position:absolute; bottom:8px; left:0; right:0; text-align:center; font-size:8.5px;">
                        <strong>Nama/Tgl</strong>
                    </div>
                </div>
            </td>
            <td style="border:none; padding:4px;">
                <div style="border:1px solid #374151; height:65px; position:relative;">
                    <div style="padding:6px 4px 0; font-weight:bold; font-size:9px; text-align:center;">Checking</div>
                    <div style="position:absolute; bottom:8px; left:0; right:0; text-align:center; font-size:8.5px;">
                        <strong>Nama/Tgl</strong>
                    </div>
                </div>
            </td>
            <td style="border:none; padding:4px;">
                <div style="border:1px solid #374151; height:65px; position:relative;">
                    <div style="padding:6px 4px 0; font-weight:bold; font-size:9px; text-align:center;">Packing</div>
                    <div style="position:absolute; bottom:8px; left:0; right:0; text-align:center; font-size:8.5px;">
                        <strong>Nama/Tgl</strong>
                    </div>
                </div>
            </td>
            <td style="border:none; padding:4px;">
                <div style="border:1px solid #374151; height:65px; position:relative;">
                    <div style="padding:6px 4px 0; font-weight:bold; font-size:9px; text-align:center;">Finishing</div>
                    <div style="position:absolute; bottom:8px; left:0; right:0; text-align:center; font-size:8.5px;">
                        <strong>Nama/Tgl</strong>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- TABEL UTAMA -->
    <table>
        <thead>
            <tr class="header1">
                <th rowspan="2" class="col-no">NO</th>
                
                <th colspan="3">DETAIL ORDER</th>
                <th rowspan="2" class="col-distribusi">DISTRIBUSI</th>
                <th colspan="2">PEMBAYARAN</th>
                <th colspan="2">ESTIMASI PERSIAPAN</th>
                <th rowspan="2" class="col-catatan">CATATAN</th>
                <th colspan="2">STATUS PRINT</th>
            </tr>

            <tr class="header2">
                <th class="col-id">ID ORDER</th>
                <th class="col-unit">NAMA UNIT</th>
                <th class="col-kategori">KATEGORI</th>
                
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
                <td class="font-bold">{{ $loop->iteration }}</td>
                
                <td class="text-left">{{ $item->no_pl ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                <td class="text-center" style="padding: 6px 4px; line-height: 1.35;">
                    <div>{{ $item->pengiriman ?? '-' }}</div>
                    <div style="border-top: 1px solid #64748b; margin: 5px 0;"></div>
                    <div>{{ $item->service_pengiriman ?? '-' }}</div>
                </td>
                <td>{{ $item->tgl_bayar ? \Carbon\Carbon::parse($item->tgl_bayar)->format('d/m/Y H:i') : '-' }}</td>
                <td class="text-right font-bold">Rp {{ number_format($item->jumlah_bayar ?? 0, 0, ',', '.') }}</td>
                <td>{{ $item->tgl_estimasi ? \Carbon\Carbon::parse($item->tgl_estimasi)->format('d/m/Y') : '-' }}</td>
                <td class="text-center">{{ $item->estimasi_hari ?? 0 }} Hari</td>
                <td class="text-left" style="font-size:9.2px;">
                    @php
                        $catatan = $item->ket ?? $item->jakartaAktif?->catatan ?? '';
                        echo Str::limit(trim($catatan), 70);
                    @endphp
                </td>
                <td>{{ $item->printed_at ? 'Sudah' : 'Belum' }}</td>
                <td>{{ $item->picking_printed_at ? 'Sudah' : 'Belum' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

        <div class="footer">
        Dicetak oleh :
        Pricing
    </div>

</body>
</html>