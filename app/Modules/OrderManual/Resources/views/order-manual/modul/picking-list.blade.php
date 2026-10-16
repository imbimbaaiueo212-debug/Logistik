<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Picking List - {{ $no_pl ?? '' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; background: #f3f4f6; }
        .sheet {
            width: 148mm; /* A5-ish */
            min-height: 210mm;
            margin: 20px auto;
            background: #fff;
            padding: 16px 18px;
            box-shadow: 0 2px 12px rgba(0,0,0,.12);
        }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th, table.items td { border: 1px solid #333; padding: 5px 6px; text-align: left; font-size: 11px; }
        table.items th { background: #f0f0f0; }
        .center { text-align: center; }
        .info td { padding: 1px 4px; vertical-align: top; }
        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .sheet { margin: 0; box-shadow: none; width: 100%; }
        }
    </style>
</head>
<body>

    {{-- Toolbar --}}
    <div class="no-print" style="position:fixed; top:16px; right:16px; z-index:50;">
        <button onclick="window.print()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-xl font-semibold shadow">
            🖨️ CETAK PDF
        </button>
        <button onclick="window.close()"
                class="ml-2 bg-white border px-4 py-2.5 rounded-xl shadow">
            Tutup
        </button>
    </div>

    <div class="sheet">
        <div style="text-align:center; margin-bottom:12px;">
            <div style="font-size:15px; font-weight:bold;">PICKING LIST | {{ $kategori_order ?? 'MODUL' }}</div>
            <div style="font-size:12px; color:#555;">Manual Modul</div>
        </div>

        <table class="info" style="width:100%; margin-bottom:10px;">
            <tr>
                <td width="55%">
                    <div><strong>{{ $no_pl ?? '-' }}</strong></div>
                    <div>Tgl PL: {{ $tgl_order ? \Carbon\Carbon::parse($tgl_order)->format('d/m/Y') : '-' }}</div>
                    <div>{{ $item->nama_unit ?? $billing_company ?? '-' }}</div>
                    <div style="font-size:11px; color:#444;">
                        {{ $item->status_kirim ?? $item->ekspedisi ?? '-' }}
                        @if(!empty($item->service_pengiriman))
                            | {{ $item->service_pengiriman }}
                        @endif
                    </div>
                </td>
                <td width="45%" style="text-align:right; vertical-align:top;">
                    <div style="display:inline-block; border:1px solid #333; width:90px; height:60px; text-align:center; line-height:60px; font-size:11px;">
                        PARAF
                    </div>
                </td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th width="36" class="center">NO</th>
                    <th>NAMA PRODUK</th>
                    <th width="90">SKU</th>
                    <th width="50" class="center">QTY</th>
                    <th width="50" class="center">CEK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $row)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $row->item_name ?? '-' }}</td>
                        <td>{{ $row->item_sku ?? '-' }}</td>
                        <td class="center">{{ $row->qty ?? 0 }}</td>
                        <td class="center"></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="center">Tidak ada item</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:28px; text-align:center; font-size:10px; color:#666;">
            No. PL: {{ $no_pl ?? '-' }} | Dicetak: {{ now()->format('d/m/Y H:i') }}<br>
            biMBA LOGISTIK
        </div>
    </div>

</body>
</html>