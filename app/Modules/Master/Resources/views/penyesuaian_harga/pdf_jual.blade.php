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
    table { width: 100%; border-collapse: collapse; }
    th { background: #162749; color: #fff; padding: 5px 4px; text-align: right; font-size: 8.5pt; }
    th.l, td.l { text-align: left; }
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
    &middot; harga jual per satuan &middot; dicetak {{ now()->format('d/m/Y H:i') }}
</div>
<table>
    <thead>
        <tr>
            <th rowspan="2" class="l">No</th><th rowspan="2" class="l">Nama Produk</th>
            <th colspan="2" class="g" style="text-align:center">Harga Lama 2024</th>
            <th colspan="3" class="g" style="text-align:center">Harga Baru 2026</th>
            <th rowspan="2" class="g">Selisih<br><span style="font-weight:normal; font-size:7.5pt">(Jual Bulat &minus;<br>Jual Lama 2024)</span></th><th rowspan="2">Kenaikan %<br><span style="font-weight:normal; font-size:7.5pt">(Selisih &divide;<br>Jual Lama)</span></th>
            <th rowspan="2" style="text-align:center">Status</th>
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
            <tr class="grup"><td colspan="10">{{ $labelGrup }}</td></tr>
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
            <td style="text-align:center; font-size: 11pt;">
                @if(! $p) -
                @elseif($r->diterapkan_pada || ($sudah26[$r->id] ?? false)) <span style="color:#1a7f37">&#10003;</span>
                @else -
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="10" class="l">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
<div class="sub" style="margin-top:8px">
    Keterangan: <b>Selisih</b> = Harga Jual Bulat (2026) &minus; Harga Jual Lama (2024). <b>Kenaikan %</b> = Selisih &divide; Harga Jual Lama &times; 100.
    Contoh: Rp 4.000 &minus; Rp 3.220 = <b>+780</b>; 780 &divide; 3.220 = <b>+24,22%</b>. <span style="color:#1a7f37">&#9650; hijau = naik</span>, <span style="color:#d32f2f">&#9660; merah = turun</span>.
</div>
</body>
</html>
