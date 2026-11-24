<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Pengajuan Harga {{ $req->nomor }}</title>
<style>
    @page { size: A4 portrait; margin: 14mm 12mm 16mm 12mm; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 10px; color: #1f2937; line-height: 1.4; }
    h1 { font-size: 15px; margin: 0; color: #162749; }
    .sub { color: #6b7280; margin: 2px 0 10px; }
    .bar { border-bottom: 2px solid #162749; margin-bottom: 10px; padding-bottom: 6px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: #162749; color: #fff; padding: 5px 6px; font-size: 9px; text-align: left; }
    td { border-bottom: 1px solid #e5e7eb; padding: 5px 6px; vertical-align: top; }
    .r { text-align: right; } .c { text-align: center; }
    .lama { color: #9ca3af; text-decoration: line-through; font-size: 8.5px; }
    .naik { color: #b91c1c; } .turun { color: #15803d; } .tetap { color: #9ca3af; }
    .meta td { border: 0; padding: 2px 6px 2px 0; }
    .judul { font-size: 11px; font-weight: bold; color: #162749; margin: 14px 0 5px; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 8px; font-size: 9px; font-weight: bold; background: #fef3c7; color: #92400e; }
    .badge.ok { background: #dcfce7; color: #166534; } .badge.no { background: #fee2e2; color: #991b1b; } .badge.x { background: #f3f4f6; color: #4b5563; }
    .ttd td { border: 0; text-align: center; padding-top: 4px; width: 33%; }
    tr { page-break-inside: avoid; }
</style>
</head>
<body>
@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    [$lbl] = \App\Modules\Master\Models\PriceChangeRequest::STATUS[$req->status] ?? [$req->status];
    $kls = ['diterapkan' => 'ok', 'ditolak' => 'no', 'dibatalkan' => 'x'][$req->status] ?? '';
    $sel = function ($lama, $baru) {
        if ($baru === null) return null;
        $d = (float) $baru - (float) $lama;
        return [$d, (float) $lama > 0 ? $d / (float) $lama * 100 : null];
    };
@endphp

<div class="bar">
    <h1>PENGAJUAN PERUBAHAN HARGA PRODUK</h1>
    <div class="sub">biMBA AIUEO · Logistik &nbsp;|&nbsp; Dicetak {{ now()->format('d/m/Y H:i') }}</div>
</div>

<table class="meta">
    <tr><td width="18%">Nomor pengajuan</td><td width="32%"><b>{{ $req->nomor }}</b></td><td width="18%">Status</td><td><span class="badge {{ $kls }}">{{ $lbl }}</span></td></tr>
    <tr><td>Diajukan oleh</td><td>{{ $req->creator->name ?? '-' }}</td><td>Tanggal pengajuan</td><td>{{ $req->created_at->format('d/m/Y H:i') }}</td></tr>
    <tr><td>Nomor surat</td><td>{{ $req->nomor_surat ?: '-' }}</td><td>Tanggal surat</td><td>{{ $req->tanggal_surat?->format('d/m/Y') ?? '-' }}</td></tr>
    @if($req->reviewed_at)
    <tr><td>Diputuskan oleh</td><td>{{ $req->reviewer->name ?? '-' }}</td><td>Tanggal keputusan</td><td>{{ $req->reviewed_at->format('d/m/Y H:i') }}</td></tr>
    @endif
    <tr><td>Alasan</td><td colspan="3">{{ $req->alasan }}</td></tr>
    @if($req->catatan_review)
    <tr><td>Catatan admin</td><td colspan="3">{{ $req->catatan_review }}</td></tr>
    @endif
</table>

<div class="judul">Daftar perubahan harga ({{ $req->items->count() }} produk)</div>
<table>
    <thead>
        <tr>
            <th width="4%" class="c">No</th>
            <th>Produk</th>
            <th width="22%" class="r">Harga Beli</th>
            <th width="22%" class="r">Harga Jual</th>
        </tr>
    </thead>
    <tbody>
    @foreach($req->items as $it)
        <tr>
            <td class="c">{{ $loop->iteration }}</td>
            <td><b>{{ $it->product->name ?? '(produk dihapus)' }}</b><br><span style="color:#9ca3af">{{ $it->product->label ?? '' }}</span></td>
            @foreach([['harga_beli_lama', 'harga_beli_baru'], ['harga_jual_lama', 'harga_jual_baru']] as [$a, $b])
                @php $s = $sel($it->$a, $it->$b); @endphp
                <td class="r">
                    @if($s)
                        <span class="lama">{{ $rp($it->$a) }}</span><br>
                        <b>{{ $rp($it->$b) }}</b><br>
                        <span class="{{ $s[0] >= 0 ? 'naik' : 'turun' }}">{{ $s[0] >= 0 ? '+' : '-' }}{{ $rp(abs($s[0])) }}@if($s[1] !== null) ({{ $s[0] >= 0 ? '+' : '' }}{{ number_format($s[1], 2, ',', '.') }}%)@endif</span>
                    @else
                        <span class="tetap">tidak diubah<br>{{ $rp($it->$a) }}</span>
                    @endif
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<div class="judul">Berkas terlampir ({{ $req->files->count() }})</div>
<table>
    <tbody>
    @forelse($req->files as $f)
        <tr><td width="4%" class="c">{{ $loop->iteration }}</td><td>{{ $f->nama }}</td><td width="20%">{{ $f->jenis === 'surat' ? 'Surat permohonan' : 'Berkas pendukung' }}</td></tr>
    @empty
        <tr><td colspan="3">{{ $req->surat_nama ?: '-' }}</td></tr>
    @endforelse
    </tbody>
</table>

<div class="judul">Kronologi</div>
<table>
    <tbody>
    @foreach($req->logs as $log)
        <tr>
            <td width="20%">{{ $log->created_at->format('d/m/Y H:i') }}</td>
            <td width="14%"><b>{{ ucfirst($log->aksi) }}</b></td>
            <td width="18%">{{ $log->user->name ?? 'Sistem' }}</td>
            <td>{{ $log->catatan }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table class="ttd" style="margin-top:28px">
    <tr><td>Diajukan oleh,</td><td>Diperiksa oleh,</td><td>Disetujui oleh,</td></tr>
    <tr><td style="height:50px"></td><td></td><td></td></tr>
    <tr><td>( {{ $req->creator->name ?? '................' }} )</td><td>( ................ )</td><td>( {{ $req->reviewer->name ?? '................' }} )</td></tr>
</table>
</body>
</html>
