<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Penyesuaian Harga Jual</title>
<style>
    @page { margin: 20px 28px 32px 28px; }
    body { font-family: DejaVu Sans, sans-serif; font-size: 9pt; color: #111; }
    .merek { font-size: 11pt; font-weight: bold; color: #E85D2A; margin: 0 0 4px; }
    h1 { font-size: 13pt; margin: 0 0 3px; }
    .sub { color: #555; margin-bottom: 8px; font-size: 8pt; }
    table { width: 100%; border-collapse: collapse; table-layout: fixed; }
    table.ringkas { margin-bottom: 8px; }
    table.ringkas td { border: 1px solid #cfcfcf; text-align: center; padding: 5px 4px; font-size: 9pt; }
    table.ringkas .judul { color: #555; font-size: 7.5pt; }
    table.ringkas .nilai { font-size: 11pt; font-weight: bold; }
    th { background: #162749; color: #fff; padding: 5px 4px; text-align: center; vertical-align: middle; font-size: 8.5pt; }
    tr.spacer th { padding: 0; border: 0; height: 0; line-height: 0; font-size: 0; background: transparent; }
    th.l, td.l { text-align: left; }
    td.c { text-align: center; }
    td { padding: 3px 3px; text-align: right; font-size: 10pt; white-space: nowrap; }
    td.l { white-space: normal; }
    tr.grup td { background: #f1f1f1; font-weight: bold; text-align: left; }
    .merah { color: #c00000; font-weight: bold; }
    th.g { border-left: 1px solid #ffffff; }
    td.g { border-left: 1px solid #888888; }
    td { border: 1px solid #c8c8c8; }
    th { border: 1px solid #44557a; }
    td.nomor { text-align: center; }
    .kecil { color: #888; font-size: 7pt; }
</style>
</head>
<body>
@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $tglBerlaku = ($batch->berlaku_mulai ?? $batch->created_at)->translatedFormat('j F Y');
@endphp
<div class="merek">biMBA-AIUEO Logistik</div>
<h1>Penyesuaian Harga Jual &mdash; {{ $batch->namaTampil() }}</h1>
<div class="sub">
    No RA {{ $batch->no_ra ?: '#' . $batch->id }} &middot; Supplier {{ $batch->supplier->nama_supplier ?? '-' }} &middot; Berlaku mulai {{ $tglBerlaku }} &middot; {{ $batch->labelStatus() }}
    @if($batch->nomor_surat) &middot; Surat {{ $batch->nomor_surat }} @endif
    @if($batch->tanggal_surat) ({{ $batch->tanggal_surat->format('d/m/Y') }}) @endif
    @if(request('kelompok') === 'modul') &middot; <b>Filter: Modul</b> @elseif(request('kelompok') === 'lainnya') &middot; <b>Filter: Selain Modul</b> @endif
    @if(request('selisih') === 'naik') &middot; <b>Filter: Lebih tinggi dari 2024</b> @elseif(request('selisih') === 'turun') &middot; <b>Filter: Lebih rendah dari 2024</b> @endif
    &middot; harga jual per satuan
</div>
@php $rs = $ringkasSelisih; $fmtRp = fn ($x) => 'Rp ' . number_format(abs($x), 0, ',', '.'); @endphp
<table class="ringkas">
    <tr>
        <td><div class="judul">Lebih tinggi dari harga jual 2024</div><div class="nilai" style="color:#1a7f37">&#9650; {{ $rs['naik'] }} produk</div></td>
        <td><div class="judul">Lebih rendah dari harga jual 2024</div><div class="nilai" style="color:#d32f2f">&#9660; {{ $rs['turun'] }} produk</div></td>
        <td><div class="judul">Sama dengan harga jual 2024</div><div class="nilai" style="color:#555">{{ $rs['sama'] }} produk</div></td>
        <td><div class="judul">Total produk</div><div class="nilai" style="color:#162749">{{ $rs['naik'] + $rs['turun'] + $rs['sama'] }} produk</div></td>
    </tr>
</table>
@php
    $rows = $rows->sortBy(fn ($x) => ($jual[$x->id]['modul'] ?? false) ? 0 : 1)->values();
    $kelompokRows = $rows->groupBy(fn ($x) => ($jual[$x->id]['modul'] ?? false) ? 'Modul' : 'Selain Modul');
    $kelompokKe = 0;
@endphp
@forelse($kelompokRows as $labelGrup => $barisGrup)
    @php $kelompokKe++; $nomor = 0; @endphp
    {{-- Tiap kelompok mulai di halaman baru supaya judul kelompok tidak terpisah dari barisnya --}}
    <table @if($kelompokKe > 1) style="page-break-before: always" @endif>
    @include('master::penyesuaian_harga.pdf_jual_head')
    <tbody>
        <tr class="grup"><td colspan="11">{{ $labelGrup }} <span style="font-weight:normal; color:#555">| (harga jual = harga beli &times; {{ number_format($labelGrup === 'Modul' ? $batch->pengaliModul() : $batch->pengaliLain(), 2, ',', '.') }})</span></td></tr>
    @foreach($barisGrup as $r)
        @php $nomor++; @endphp
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
            <td class="nomor">{{ $nomor }}</td>
            <td class="l">{{ $r->nama }}</td>
            <td class="g">{{ $rp($j['beli_lama']) }}</td>
            <td>{{ $rp($lama) }}</td>
            <td class="g"><b>{{ $rp($r->harga_2026) }}</b></td>
            <td>{{ $j['asli_satuan'] === null ? '-' : 'Rp ' . rtrim(rtrim(number_format($j['asli_satuan'], 2, ',', '.'), '0'), ',') }}</td>
            <td><b>{{ $rp($baru) }}</b></td>
            <td class="g" style="color:{{ $warnaSel }}; font-weight:bold">{{ $selisih === null ? '-' : $panahSel . ($selisih > 0 ? '+' : '') . number_format($selisih, 0, ',', '.') }}</td>
            <td style="color:{{ $warnaPct }}">{{ $persen === null ? '-' : $panahPct . ($persen > 0 ? '+' : '') . number_format($persen, 2, ',', '.') . '%' }}</td>
            <td class="l" style="color:{{ $warnaSel }}; font-size:8pt; padding-left:6px">
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
    @endforeach
    </tbody>
    </table>
@empty
    <table>
    @include('master::penyesuaian_harga.pdf_jual_head')
    <tbody><tr><td colspan="11" class="l">Tidak ada data.</td></tr></tbody>
    </table>
@endforelse
<div class="sub" style="margin-top:8px">
    Keterangan: <b>Lebih tinggi/rendah</b> dibanding Harga Jual Lama 2024 (per satuan). <b>Selisih</b> = Harga Jual Bulat (2026) &minus; Harga Jual Lama (2024). <b>Kenaikan %</b> = Selisih &divide; Harga Jual Lama &times; 100.
    Contoh: Rp 4.000 &minus; Rp 3.220 = <b>+780</b>; 780 &divide; 3.220 = <b>+24,22%</b>. <span style="color:#1a7f37">&#9650; hijau = naik</span>, <span style="color:#d32f2f">&#9660; merah = turun</span>.
</div>
<script type="text/php">
    if (isset($pdf)) {
        $font  = $fontMetrics->get_font('DejaVu Sans', 'normal');
        $size  = 8;
        $warna = [0.35, 0.35, 0.35];
        $y     = $pdf->get_height() - 22;
        $lebarKanan = $fontMetrics->get_text_width('Halaman 99 dari 99', $font, $size);

        $pdf->line(28, $y - 6, $pdf->get_width() - 28, $y - 6, [0.75, 0.75, 0.75], 0.5);
        $pdf->page_text(28, $y, 'Dibuat {{ now()->format('d/m/Y H:i') }}', $font, $size, $warna);
        $pdf->page_text($pdf->get_width() - 28 - $lebarKanan, $y, 'Halaman {PAGE_NUM} dari {PAGE_COUNT}', $font, $size, $warna);
    }
</script>
</body>
</html>
