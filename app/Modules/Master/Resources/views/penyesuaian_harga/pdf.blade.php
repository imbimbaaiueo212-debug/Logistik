<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Penyesuaian Harga Beli</title>
<style>
    @page { margin: 24px 28px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #111; }
    h1 { font-size: 13pt; margin: 0 0 3px; }
    .sub { color: #555; margin-bottom: 8px; font-size: 8pt; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #162749; color: #fff; padding: 5px 4px; text-align: right; font-size: 8.5pt; }
    th.l, td.l { text-align: left; }
    td { padding: 4px 4px; border-bottom: 1px solid #ddd; text-align: right; font-size: 9pt; }
    tr.grup td { background: #f1f1f1; font-weight: bold; text-align: left; }
    th.g { border-left: 1px solid #ffffff; }
    td.g { border-left: 1px solid #888888; }
    .merah { color: #c00000; font-weight: bold; }
    .kecil { color: #888; font-size: 7pt; }
</style>
</head>
<body>
@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => $v === null ? '-' : ($v > 0 ? '+' : '') . number_format($v, 2, ',', '.') . '%';
    $label = ['sama_beli' => 'Sama dengan 2024', 'sama_jual' => 'Sama (Harga Jual)', 'beda' => 'Beda dari 2024', 'belum_cocok' => 'Produk belum ketemu'];
@endphp
<h1>Penyesuaian Harga Beli{{ $batch ? ' — ' . $batch->namaTampil() : '' }}</h1>
<div class="sub">
    @if($batch)
        No RA {{ $batch->no_ra ?: '#' . $batch->id }} &middot; Supplier {{ $batch->supplier->nama_supplier ?? '-' }} &middot; {{ $batch->labelStatus() }}
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
            <th rowspan="2" class="l">No</th><th rowspan="2" class="l">Nama Produk</th>
            <th colspan="5" class="g" style="text-align:center">Harga Beli</th>
            <th rowspan="2" class="g" style="text-align:center">Status</th>
        </tr>
        <tr>
            <th class="g">Harga 2024<br>Percetakan</th><th>Harga 2024<br>Aplikasi</th>
            <th class="g">Selisih</th>
            <th class="g">Harga 2026<br>(Beli Baru)</th><th>Kenaikan %</th>
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
            <td class="g">{{ $rp($h24) }}</td>
            <td class="{{ $beda ? 'merah' : '' }}">{{ $rp($app) }}</td>
            <td class="g {{ $beda ? 'merah' : '' }}">{{ $app === null ? '-' : ($app - $h24 > 0 ? '+' : '') . number_format($app - $h24, 0, ',', '.') }}</td>
            <td class="g"><b>{{ $rp($h26) }}</b></td>
            <td>{{ $pct($naikPdf) }}</td>
            <td style="text-align:center; font-size: 11pt;" class="g {{ $beda ? 'merah' : '' }}">
                @if(($status[$r->id] ?? '') === 'belum_cocok') -
                @elseif($beda || ($status[$r->id] ?? '') === 'beda') &#10007;
                @else <span style="color:#1a7f37">&#10003;</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
