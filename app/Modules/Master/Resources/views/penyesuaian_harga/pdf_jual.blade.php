<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Penyesuaian Harga Jual</title>
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
    h1 { font-size: 14px; margin: 0 0 2px; }
    .sub { color: #555; margin-bottom: 8px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #162749; color: #fff; padding: 4px; text-align: right; font-size: 8.5px; }
    th.l, td.l { text-align: left; }
    td { padding: 3px 4px; border-bottom: 1px solid #ddd; text-align: right; }
    tr.grup td { background: #f1f1f1; font-weight: bold; text-align: left; }
    .kecil { color: #888; font-size: 7px; }
</style>
</head>
<body>
@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $tglBerlaku = ($batch->berlaku_mulai ?? $batch->created_at)->translatedFormat('j F Y');
@endphp
<h1>Penyesuaian Harga Jual &mdash; {{ $batch->namaTampil() }}</h1>
<div class="sub">
    Berlaku mulai {{ $tglBerlaku }} &middot; {{ $batch->labelStatus() }}
    @if($batch->nomor_surat) &middot; Surat {{ $batch->nomor_surat }} @endif
    @if($batch->tanggal_surat) ({{ $batch->tanggal_surat->format('d/m/Y') }}) @endif
    &middot; harga jual per satuan &middot; dicetak {{ now()->format('d/m/Y H:i') }}
</div>
<table>
    <thead>
        <tr>
            <th rowspan="2" class="l">No</th><th rowspan="2" class="l">Nama Produk</th>
            <th colspan="2" style="text-align:center">Harga Lama 2024</th>
            <th colspan="2" style="text-align:center">Harga Baru 2026</th>
            <th rowspan="2">Selisih Jual</th><th rowspan="2">Kenaikan %</th>
            <th rowspan="2" class="l">Status</th>
        </tr>
        <tr>
            <th>Harga Beli</th><th>Harga Jual</th><th>Harga Beli</th><th>Harga Jual</th>
        </tr>
    </thead>
    <tbody>
    @php $grupSebelum = null; @endphp
    @forelse($rows as $r)
        @if($grupSebelum !== $r->grup)
            @php $grupSebelum = $r->grup; @endphp
            <tr class="grup"><td colspan="9">{{ $r->grup }}</td></tr>
        @endif
        @php
            $p = $produk[$r->id] ?? null;
            $j = $jual[$r->id];
            $lama = $j['lama_satuan']; $baru = $j['baru_satuan'];
            $selisih = ($lama !== null && $baru !== null) ? $baru - $lama : null;
            $persen = ($lama !== null && $lama > 0 && $baru !== null) ? (($baru - $lama) / $lama) * 100 : null;
        @endphp
        <tr>
            <td class="l">{{ $r->no }}</td>
            <td class="l">{{ $r->nama }}</td>
            <td>{{ $rp($j['beli_lama']) }}</td>
            <td>{{ $rp($lama) }}</td>
            <td><b>{{ $rp($r->harga_2026) }}</b></td>
            <td><b>{{ $rp($baru) }}</b></td>
            <td>{{ $selisih === null ? '-' : ($selisih > 0 ? '+' : '') . number_format($selisih, 0, ',', '.') }}</td>
            <td>{{ $persen === null ? '-' : ($persen >= 0 ? '+' : '') . number_format($persen, 2, ',', '.') . '%' }}</td>
            <td class="l">
                @if(! $p) Produk belum ketemu
                @elseif($r->diterapkan_pada) Sudah berlaku <span class="kecil">({{ $r->diterapkan_pada->format('d/m/Y') }})</span>
                @elseif($batch->status === 'ditolak') Ditolak
                @elseif($batch->isMenunggu()) Menunggu ACC
                @elseif($sudah26[$r->id] ?? false) Tidak diubah
                @else Tidak dipilih
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="9" class="l">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
</body>
</html>
