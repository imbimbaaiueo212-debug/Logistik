@extends('layouts.panel')

@section('title', 'Penyesuaian Harga Jual')

@section('content')
@include('partials.page-header', [
    'title'     => $batch->namaTampil(),
    'subtitle'  => 'Harga jual berlaku mulai ' . ($batch->berlaku_mulai ?? $batch->created_at)->translatedFormat('j F Y') . '. Dihitung otomatis dari Harga Beli baru, dan ikut berubah saat perubahan harga beli di-ACC.',
    'back'      => route('penyesuaian-harga-jual.index'),
    'backLabel' => 'Kembali',
])

@include('partials.flash')

@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $inp = 'border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40';
    $card = 'bg-white rounded-2xl shadow-sm border border-gray-100';
    $fmtPengali = fn ($v) => number_format($v, 2, ',', '.');
    $statusBatch = [
        'menunggu'    => 'bg-amber-100 text-amber-800',
        'dipublikasi' => 'bg-green-100 text-green-800',
        'ditolak'     => 'bg-red-100 text-red-800',
    ];
@endphp

<div class="{{ $card }} px-4 py-3 mb-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="text-xs text-gray-600 space-y-0.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-block px-2 py-0.5 rounded-full text-xs {{ $statusBatch[$batch->status] ?? '' }}">{{ $batch->labelStatus() }}</span>
                <span>Surat: <b>{{ $batch->nomor_surat ?: '-' }}</b>@if($batch->tanggal_surat) &middot; {{ $batch->tanggal_surat->format('d/m/Y') }}@endif</span>
            </div>
            @if($batch->isMenunggu())
                <div class="text-amber-700">Harga jual di Master Product belum berubah. Perubahan baru berlaku setelah harga beli di-ACC di menu Penyesuaian Harga &rarr; Beli.</div>
            @endif
        </div>
        <a href="{{ route('penyesuaian-harga.show', $batch->id) }}" class="border border-[#162749] text-[#162749] px-4 py-2 rounded-xl text-sm hover:bg-gray-50">
            {{ $batch->isMenunggu() ? 'Buka di Beli untuk ACC' : 'Lihat di Beli' }}
        </a>
    </div>
</div>

<form method="GET" action="{{ route('penyesuaian-harga-jual.show', $batch->id) }}" class="{{ $card }} p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Kelompok</label>
        <select name="grup" class="{{ $inp }}">
            <option value="">Semua</option>
            @foreach($grupList as $g) <option value="{{ $g }}" @selected(request('grup') === $g)>{{ $g }}</option> @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Cari nama</label>
        <input type="text" name="cari" value="{{ request('cari') }}" class="{{ $inp }}" placeholder="mis. Modul Baca">
    </div>
    <button class="bg-[#162749] text-white px-4 py-2 rounded-xl text-sm">Filter</button>
    <a href="{{ route('penyesuaian-harga-jual.show', $batch->id) }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-xl text-sm">Reset</a>
    <div class="ml-auto">
        <a href="{{ route('penyesuaian-harga-jual.pdf', ['batch' => $batch->id] + request()->query()) }}" target="_blank"
           class="bg-[#E85D2A] text-white px-4 py-2 rounded-xl text-sm">Unduh PDF</a>
    </div>
</form>

<div class="{{ $card }} overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-[#162749] text-white text-xs uppercase">
            <tr>
                <th class="px-3 py-3 text-left">No</th>
                <th class="px-3 py-3 text-left">Nama Produk</th>
                <th class="px-3 py-3 text-right">Harga Beli Baru<br><span class="normal-case font-normal">2026</span></th>
                <th class="px-3 py-3 text-right">Harga Jual<br><span class="normal-case font-normal">Lama</span></th>
                <th class="px-3 py-3 text-right">Harga Jual<br><span class="normal-case font-normal">Baru</span></th>
                <th class="px-3 py-3 text-right">Kenaikan<br><span class="normal-case font-normal">%</span></th>
                <th class="px-3 py-3 text-left">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php $grupSebelum = null; @endphp
            @forelse($rows as $r)
                @if($grupSebelum !== $r->grup)
                    @php $grupSebelum = $r->grup; @endphp
                    <tr class="bg-gray-50"><td colspan="7" class="px-3 py-2 font-semibold text-[#162749]">{{ $r->grup }}</td></tr>
                @endif
                @php
                    $p = $produk[$r->id] ?? null;
                    $j = $jual[$r->id];
                    $lama = $j['sekarang'];
                    $baru = $j['baru'];
                    $sama = $p && ($sudah26[$r->id] ?? false);
                    $persen = ($lama !== null && $lama > 0 && $baru !== null) ? (($baru - $lama) / $lama) * 100 : null;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2 text-gray-500">{{ $r->no }}</td>
                    <td class="px-3 py-2">
                        <div class="font-medium">{{ $r->nama }}</div>
                        @if($p) <div class="text-[11px] text-gray-400">{{ $p->name }} ({{ $p->label }})</div> @endif
                    </td>
                    <td class="px-3 py-2 text-right">{{ $rp($r->harga_2026) }}</td>
                    <td class="px-3 py-2 text-right text-gray-500">{{ $rp($lama) }}</td>
                    <td class="px-3 py-2 text-right">
                        <div class="font-semibold">{{ $rp($baru) }}</div>
                        @if($j['pengali'] !== null)
                            <div class="text-[10px] text-gray-400">&times;{{ $fmtPengali($j['pengali']) }}@if($j['isi'] > 1) &times; isi {{ $j['isi'] }}@endif</div>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right {{ $persen === null ? 'text-gray-400' : ($persen >= 0 ? 'text-red-600' : 'text-green-700') }}">
                        {{ $persen === null ? '-' : ($persen >= 0 ? '+' : '') . number_format($persen, 2, ',', '.') . '%' }}
                    </td>
                    <td class="px-3 py-2">
                        @if(! $p)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Produk belum ketemu</span>
                        @elseif($r->diterapkan_pada)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-800">Sudah berlaku</span>
                            <div class="text-[10px] text-green-700 mt-0.5">&#10003; {{ $r->diterapkan_pada->format('d/m/Y') }}</div>
                        @elseif($batch->status === 'ditolak')
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-800">Ditolak</span>
                        @elseif($batch->isMenunggu())
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800">Menunggu ACC</span>
                        @elseif($sama)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">Tidak diubah</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Tidak dipilih</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-8 text-center text-gray-400">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-gray-500 mt-3">
    Harga Jual = Harga Beli &times; {{ $fmtPengali($pengaliModul) }} untuk Modul, &times; {{ $fmtPengali($pengaliLain) }} untuk selain Modul, dibulatkan ke atas per Rp 50 lalu dikali isi.
    Kenaikan % = (harga jual baru &minus; lama) / lama. Untuk mengubah harga, import dan ACC di menu Penyesuaian Harga &rarr; Beli.
</p>
@endsection
