<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Penyesuaian Harga Jual</title>
<style>
    @page { margin: 24px 28px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #111; }
    h1 { font-size: 13pt; margin: 0 0 3px; }
    .sub { color: #555; margin-bottom: 8px; font-size: 8pt; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.ringkas { margin-bottom: 8px; }
    table.ringkas td { border: 1px solid #cfcfcf; text-align: center; padding: 5px 4px; font-size: 8.5pt; }
    table.ringkas .judul { color: #555; font-size: 7.5pt; }
    table.ringkas .nilai { font-size: 11pt; font-weight: bold; }
    th { background: #162749; color: #fff; padding: 5px 4px; text-align: center; vertical-align: middle; font-size: 8.5pt; }
    th.l, td.l { text-align: left; }
    td.c { text-align: center; }
    td { padding: 4px 4px; border-bottom: 1px solid #ddd; text-align: right; font-size: 9pt; }
    tr.grup td { background: #f1f1f1; font-weight: bold; text-align: left; }
    .merah { color: #c00000; font-weight: bold; }
    th.g { border-left: 1px solid #ffffff; }
    td.g { border-left: 1px solid #888888; }
    td, th { border-right: 0; }
    .kecil { color: #888; font-size: 7pt; }
</style>
</head>
<body>
@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $tglBerlaku = ($batch->berlaku_mulai ?? $batch->created_at)->translatedFormat('j F Y');
@endphp
<h1>Penyesuaian Harga Jual &mdash; {{ $batch->namaTampil() }}</h1>
<div class="sub">
    No RA {{ $batch->no_ra ?: '#' . $batch->id }} &middot; Supplier {{ $batch->supplier->nama_supplier ?? '-' }} &middot; Berlaku mulai {{ $tglBerlaku }} &middot; {{ $batch->labelStatus() }}
    @if($batch->nomor_surat) &middot; Surat {{ $batch->nomor_surat }} @endif
    @if($batch->tanggal_surat) ({{ $batch->tanggal_surat->format('d/m/Y') }}) @endif
    @if(request('kelompok') === 'modul') &middot; <b>Filter: Modul</b> @elseif(request('kelompok') === 'lainnya') &middot; <b>Filter: Selain Modul</b> @endif
    @if(request('selisih') === 'naik') &middot; <b>Filter: Lebih tinggi dari 2024</b> @elseif(request('selisih') === 'turun') &middot; <b>Filter: Lebih rendah dari 2024</b> @endif
    &middot; harga jual per satuan &middot; dicetak {{ now()->format('d/m/Y H:i') }}
</div>
@php $rs = $ringkasSelisih; $fmtRp = fn ($x) => 'Rp ' . number_format(abs($x), 0, ',', '.'); @endphp
<table class="ringkas">
    <tr>
        <td><div class="judul">Lebih tinggi dari harga jual 2024 ({{ $rs['naik'] }} produk)</div><div class="nilai" style="color:#1a7f37">&#9650; +{{ $fmtRp($rs['total_naik']) }}</div></td>
        <td><div class="judul">Lebih rendah dari harga jual 2024 ({{ $rs['turun'] }} produk)</div><div class="nilai" style="color:#d32f2f">&#9660; &minus;{{ $fmtRp($rs['total_turun']) }}</div></td>
        <td><div class="judul">Sama dengan harga jual 2024</div><div class="nilai" style="color:#555">{{ $rs['sama'] }} produk</div></td>
        <td><div class="judul">Total selisih bersih (naik &minus; turun)</div><div class="nilai" style="color:{{ $rs['bersih'] > 0 ? '#1a7f37' : ($rs['bersih'] < 0 ? '#d32f2f' : '#555') }}">{{ $rs['bersih'] > 0 ? '+' : ($rs['bersih'] < 0 ? '&minus;' : '') }}{{ $fmtRp($rs['bersih']) }}</div></td>
    </tr>
</table>
<table>
    <colgroup>
        <col style="width:3%"><col style="width:20%">
        <col style="width:7.5%"><col style="width:7.5%">
        <col style="width:8%"><col style="width:8.5%"><col style="width:8%">
        <col style="width:8.5%"><col style="width:8%">
        <col style="width:16%"><col style="width:5%">
    </colgroup>
    <thead>
        <tr>
            <th rowspan="2" class="l">No</th><th rowspan="2" class="l">Nama Produk</th>
            <th colspan="2" class="g" style="text-align:center">Harga Lama 2024</th>
            <th colspan="3" class="g" style="text-align:center">Harga Baru 2026</th>
            <th rowspan="2" class="g">Selisih<br><span style="font-weight:normal; font-size:7.5pt">(Bulat &minus;<br>Lama 2024)</span></th><th rowspan="2">Kenaikan %<br><span style="font-weight:normal; font-size:7.5pt">(Selisih &divide;<br>Jual Lama)</span></th>
            <th rowspan="2" class="l" style="padding-left:8px">Keterangan</th>
            <th rowspan="2">Status</th>
        </tr>
        <tr>
            <th class="g">Harga Beli</th><th>Harga Jual</th><th class="g">Harga Beli</th><th>Harga Jual<br>Normal</th><th>Harga Jual<br>Bulat</th>
        </tr>
    </thead>
    <tbody>
    @php
        $grupSebelum = null;
        $rows = $rows->sortBy(fn ($x) => ($jual[$x->id]['modul'] ?? false) ? 0 : 1)->values();
    @endphp
    @forelse($rows as $r)
        @php $labelGrup = ($jual[$r->id]['modul'] ?? false) ? 'Modul' : 'Selain Modul'; @endphp
        @if($grupSebelum !== $labelGrup)
            @php $grupSebelum = $labelGrup; @endphp
            <tr class="grup"><td colspan="11">{{ $labelGrup }}</td></tr>
        @endif
        @php
            $p = $produk[$r->id] ?? null;
            $j = $jual[$r->id];
            $lama = $j['lama_satuan']; $baru = $j['baru_satuan'];
            $selisih = ($lama !== null && $baru !== null) ? $baru - $lama : null;
            $persen = ($lama !== null && $lama > 0 && $baru !== null) ? (($baru - $lama) / $lama) * 100 : null;
            $warnaSel = $selisih === null || $selisih == 0 ? '#6b7280' : ($selisih > 0 ? '#1a7f37' : '#d32f2f');
            $panahSel = $selisih === null || $selisih == 0 ? '' : ($selisih > 0 ? '▲ ' : '▼ ');
            $warnaPct = $persen === null || round($persen, 2) == 0 ? '#6b7280' : ($persen > 0 ? '#1a7f37' : '#d32f2f');
            $panahPct = $persen === null || round($persen, 2) == 0 ? '' : ($persen > 0 ? '▲ ' : '▼ ');
        @endphp
        <tr>
            <td class="l">{{ $r->no }}</td>
            <td class="l">{{ $r->nama }}</td>
            <td class="g">{{ $rp($j['beli_lama']) }}</td>
            <td>{{ $rp($lama) }}</td>
            <td class="g"><b>{{ $rp($r->harga_2026) }}</b></td>
            <td>{{ $j['asli_satuan'] === null ? '-' : 'Rp ' . rtrim(rtrim(number_format($j['asli_satuan'], 2, ',', '.'), '0'), ',') }}</td>
            <td><b>{{ $rp($baru) }}</b></td>
            <td class="g" style="color:{{ $warnaSel }}; font-weight:bold">{{ $selisih === null ? '-' : $panahSel . ($selisih > 0 ? '+' : '') . number_format($selisih, 0, ',', '.') }}</td>
            <td style="color:{{ $warnaPct }}">{{ $persen === null ? '-' : $panahPct . ($persen > 0 ? '+' : '') . number_format($persen, 2, ',', '.') . '%' }}</td>
            <td class="l" style="color:{{ $warnaSel }}; font-size:7.5pt; padding-left:8px">
                @if($selisih === null) -
                @elseif($selisih > 0) Lebih tinggi Rp {{ number_format($selisih, 0, ',', '.') }}
                @elseif($selisih < 0) Lebih rendah Rp {{ number_format(abs($selisih), 0, ',', '.') }}
                @else Sama dengan 2024
                @endif
            </td>
            <td class="c" style="font-size: 11pt;">
                @if(! $p) -
                @elseif($r->diterapkan_pada || ($sudah26[$r->id] ?? false)) <span style="color:#1a7f37">&#10003;</span>
                @else -
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="11" class="l">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
<div class="sub" style="margin-top:8px">
    Keterangan: <b>Lebih tinggi/rendah</b> dibanding Harga Jual Lama 2024 (per satuan). <b>Selisih</b> = Harga Jual Bulat (2026) &minus; Harga Jual Lama (2024). <b>Kenaikan %</b> = Selisih &divide; Harga Jual Lama &times; 100.
    Contoh: Rp 4.000 &minus; Rp 3.220 = <b>+780</b>; 780 &divide; 3.220 = <b>+24,22%</b>. <span style="color:#1a7f37">&#9650; hijau = naik</span>, <span style="color:#d32f2f">&#9660; merah = turun</span>.
</div>
</body>
</html>
