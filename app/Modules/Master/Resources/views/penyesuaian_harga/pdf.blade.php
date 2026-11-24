<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Penyesuaian Harga</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #111; }
    h1 { font-size: 14px; margin: 0 0 2px; }
    .sub { color: #555; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #162749; color: #fff; padding: 4px; text-align: right; font-size: 8px; }
    th.l, td.l { text-align: left; }
    td { padding: 3px 4px; border-bottom: 1px solid #ddd; text-align: right; }
    tr.grup td { background: #f1f1f1; font-weight: bold; text-align: left; }
    .merah { color: #c00000; font-weight: bold; }
    .kecil { color: #888; font-size: 7px; }
</style>
</head>
<body>
@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => $v === null ? '-' : ($v > 0 ? '+' : '') . number_format($v, 2, ',', '.') . '%';
    $label = ['sama_beli' => 'Sama dengan 2024', 'sama_jual' => 'Sama (Harga Jual)', 'beda' => 'Beda dari 2024', 'belum_cocok' => 'Produk belum ketemu'];
@endphp
<h1>Penyesuaian Harga</h1>
<div class="sub">
    @if($batch)
        Import #{{ $batch->id }} &middot; {{ $batch->labelStatus() }}
        @if($batch->nomor_surat) &middot; Surat {{ $batch->nomor_surat }} @endif
        @if($batch->tanggal_surat) ({{ $batch->tanggal_surat->format('d/m/Y') }}) @endif
    @else
        Belum ada import
    @endif
    &middot; dicetak {{ now()->format('d/m/Y H:i') }}
</div>
<table>
    <thead>
        <tr>
            <th class="l">No</th><th class="l">Nama Produk</th>
            <th>Harga 2024 Percetakan</th><th>Harga 2024 Aplikasi</th><th>Selisih</th>
            <th>Harga 2026 (Beli Baru)</th><th>Kenaikan %</th>
            <th class="l">Status</th>
        </tr>
    </thead>
    <tbody>
    @php $grupSebelum = null; @endphp
    @foreach($rows as $r)
        @if($grupSebelum !== $r->grup)
            @php $grupSebelum = $r->grup; @endphp
            <tr class="grup"><td colspan="8">{{ $r->grup }}</td></tr>
        @endif
        @php
            $p = $produk[$r->id] ?? null;
            $j = $jual[$r->id];
            $beliApp = $p ? ($r->diterapkan_pada ? $r->harga_beli_sebelum : $p->harga_beli) : null;
            $app = $beliApp !== null ? (float) $beliApp : null;
            $h24 = (float) $r->harga_2024; $h26 = (float) $r->harga_2026;
            $naikPdf = $h24 > 0 ? (($h26 - $h24) / $h24) * 100 : null;
            $beda = $app !== null && abs($app - $h24) > 0.5;
        @endphp
        <tr>
            <td class="l">{{ $r->no }}</td>
            <td class="l">{{ $r->nama }}</td>
            <td>{{ $rp($h24) }}</td>
            <td class="{{ $beda ? 'merah' : '' }}">{{ $rp($app) }}</td>
            <td class="{{ $beda ? 'merah' : '' }}">{{ $app === null ? '-' : ($app - $h24 > 0 ? '+' : '') . number_format($app - $h24, 0, ',', '.') }}</td>
            <td><b>{{ $rp($h26) }}</b></td>
            <td>{{ $pct($naikPdf) }}</td>
            <td class="l {{ $beda ? 'merah' : '' }}">{{ $label[$status[$r->id]] ?? '' }}@if($r->diterapkan_pada) <span class="kecil">(dipublikasi)</span>@elseif($sudah26[$r->id] ?? false) <span class="kecil">(sudah mengikuti 2026)</span>@endif</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
