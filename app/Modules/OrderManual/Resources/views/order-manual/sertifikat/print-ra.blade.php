<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Rekap Aktual Manual Sertifikat' }}</title>
    <style>
        @page { size: A4 landscape; margin: 8mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 0; color: #111; }
        h1 { font-size: 16px; margin: 0 0 4px; text-align: center; }
        .sub { text-align: center; font-size: 11px; color: #555; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 5px 6px; vertical-align: top; }
        th { background: #e8eef7; font-size: 10px; }
        .left { text-align: left; }
        .center { text-align: center; }
        .sign { margin-top: 24px; width: 100%; }
        .sign td { border: none; text-align: center; width: 20%; padding-top: 8px; }
        .sign .line { margin-top: 40px; font-size: 10px; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="padding:10px; background:#f3f4f6; margin-bottom:10px;">
        <button onclick="window.print()" style="padding:8px 16px; background:#2563eb; color:#fff; border:0; border-radius:8px; cursor:pointer;">
            🖨️ Cetak / Save as PDF
        </button>
        <button onclick="window.close()" style="padding:8px 16px; margin-left:8px; border:1px solid #ccc; border-radius:8px; cursor:pointer;">
            Tutup
        </button>
    </div>

    @php
        $first = $data->first();
        $rekapNo = $first->rekap_number ?? '-';
        $tgl = $first->tgl_turun_pl ?? now();
    @endphp

    <h1>{{ $title ?? 'Rekap Aktual Manual Sertifikat' }}</h1>
    <div class="sub">
        No Rekap: <strong>{{ $rekapNo }}</strong> &nbsp;|&nbsp;
        Tanggal: {{ \Carbon\Carbon::parse($tgl)->format('d/m/Y') }} &nbsp;|&nbsp;
        Total: {{ $data->count() }} order
    </div>

    <table>
        <thead>
            <tr>
                <th width="40">NO</th>
                <th width="110">NO PL</th>
                <th class="left">NAMA UNIT</th>
                <th width="90">CABANG</th>
                <th width="100">KATEGORI</th>
                <th class="left">NAMA BARANG</th>
                <th width="50">QTY</th>
                <th width="90">SKU</th>
                <th width="100">DISTRIBUSI</th>
                <th width="90">SERVICE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td class="center">{{ $item->no_pl }}</td>
                <td class="left">{{ $item->nama_unit }}</td>
                <td class="center">{{ $item->billing_last_name ?? '-' }}</td>
                <td class="center">{{ $item->kategori_order }}</td>
                <td class="left">{{ $item->nama_barang }}</td>
                <td class="center">{{ $item->qty ?? '-' }}</td>
                <td class="center">{{ $item->product_sku ?? '-' }}</td>
                <td class="center">{{ $item->status_kirim ?? $item->ekspedisi ?? '-' }}</td>
                <td class="center">{{ $item->service_pengiriman ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="sign">
        <tr>
            @foreach(['Pricing','Picking','Checking','Packing','Finishing'] as $bagian)
            <td>
                <div><strong>{{ $bagian }}</strong></div>
                <div class="line">Nama __________ &nbsp; Tgl __________</div>
            </td>
            @endforeach
        </tr>
    </table>

    <script>
        // Opsional: auto print
        // window.onload = () => window.print();
    </script>
</body>
</html>