<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'RA Prising - Manual Modul' }}</title>
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
            font-size: 10px;
            font-weight: 700;
            padding: 6px 4px;
        }

        .text-left   { text-align: left; }
        .text-center { text-align: center; }
        .text-right  { text-align: right; }
        .font-bold   { font-weight: bold; }

        .footer {
            margin-top: 10px;
            border-top: 1px solid #d1d5db;
            padding-top: 4px;
            text-align: right;
            font-size: 8px;
            color: #6b7280;
        }

        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
@php
    $first      = $data->first();
    $rekapNo    = $first->rekap_number ?? '-';
    $allPrinted = $data->every(fn($item) => !is_null($item->printed_at ?? null));
    $tgl        = now();
@endphp

    {{-- ================= HEADER ================= --}}
    <table style="width:100%; border:none; border-collapse:collapse; margin-bottom:10px;">
        <tr>
            <td style="width:15%; border:none;"></td>

            <td style="width:70%; border:none; text-align:center; vertical-align:middle; padding-bottom:6px;">
                <div style="color:#000; font-size:15px; font-weight:bold; margin-bottom:6px;">
                    {{ $title ?? 'RA Prising - Manual Modul' }}
                    <span style="color:#4f46e5; font-weight:bold;">{{ $rekapNo }}</span>
                </div>
                <div style="font-size:10.5px; color:#64748b; font-weight:bold; margin-bottom:1px;">
                    Waktu Rekap & Cetak RA
                </div>
                <div style="font-size:11.5px; font-weight:bold; color:#111827;">
                    {{ \Carbon\Carbon::parse($tgl)->format('d/m/Y H:i:s') }}
                </div>
            </td>

            <td style="width:15%; border:none; text-align:center; vertical-align:middle;">
                <div style="font-size:10.5px; font-weight:bold; color:#64748b; margin-bottom:3px;">
                    STATUS RA & PL (PDF)
                </div>
                <div style="font-size:14px; font-weight:bold; color:{{ $allPrinted ? '#10b981' : '#ef4444' }};">
                    &#10003; {{ $allPrinted ? 'PRINT' : 'BELUM' }}
                </div>
            </td>
        </tr>

        {{-- TANDA TANGAN (di atas, seperti Rekap Aktual Detail) --}}
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

    {{-- ================= TABEL UTAMA ================= --}}
    <table>
        <thead>
            <tr class="header1">
                <th style="width:4%;">NO</th>
                <th style="width:11%;">NO PL</th>
                <th style="width:16%;">NAMA UNIT</th>
                <th style="width:8%;">CABANG</th>
                <th style="width:10%;">KATEGORI</th>
                <th style="width:18%;">NAMA BARANG</th>
                <th style="width:5%;">QTY</th>
                <th style="width:8%;">SKU</th>
                <th style="width:10%;">DISTRIBUSI</th>
                <th style="width:10%;">SERVICE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="text-center font-bold">{{ $loop->iteration }}</td>
                <td class="text-center">{{ $item->no_pl ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_unit ?? '-' }}</td>
                <td class="text-center">{{ $item->billing_last_name ?? '-' }}</td>
                <td class="text-center">{{ $item->kategori_order ?? '-' }}</td>
                <td class="text-left">{{ $item->nama_barang ?? '-' }}</td>
                <td class="text-center">{{ $item->qty ?? '-' }}</td>
                <td class="text-center">{{ $item->product_sku ?? '-' }}</td>
                <td class="text-center">{{ $item->status_kirim ?? $item->ekspedisi ?? '-' }}</td>
                <td class="text-center">{{ $item->service_pengiriman ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- FOOTER --}}
    <div class="footer">
        Dicetak oleh : Pricing {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
        &nbsp;|&nbsp; Total: {{ $data->count() }} order
    </div>

</body>
</html>