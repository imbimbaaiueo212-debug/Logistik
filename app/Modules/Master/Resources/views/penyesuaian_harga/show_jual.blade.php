@extends('layouts.panel')

@section('title', 'Penyesuaian Harga Jual')

@section('content')
@include('partials.page-header', [
    'title'     => $batch->namaTampil(),
    'subtitle'  => 'Harga jual berlaku mulai ' . ($batch->berlaku_mulai ?? $batch->created_at)->translatedFormat('j F Y') . '. Dihitung otomatis dari Harga Beli baru. Masuk ke Master Product setelah ACC Beli dan ACC Jual.',
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
        'menunggu_jual' => 'bg-blue-100 text-blue-800',
        'dipublikasi' => 'bg-green-100 text-green-800',
        'ditolak'     => 'bg-red-100 text-red-800',
    ];
@endphp

<div class="{{ $card }} px-4 py-3 mb-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="text-xs text-gray-600 space-y-0.5">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="inline-block px-2 py-0.5 rounded-full text-xs {{ $statusBatch[$batch->status] ?? '' }}">{{ $batch->labelStatus() }}</span>
                <span>No RA: <b>{{ $batch->no_ra ?: '-' }}</b> &middot; Supplier: <b>{{ $batch->supplier->nama_supplier ?? '-' }}</b> &middot;</span>
                <span>Surat: <b>{{ $batch->nomor_surat ?: '-' }}</b>@if($batch->tanggal_surat) &middot; {{ $batch->tanggal_surat->format('d/m/Y') }}@endif</span>
            </div>
            @if($batch->isMenunggu())
                <div class="text-amber-700">Menunggu ACC Beli dulu di menu Penyesuaian Harga &rarr; Beli. Harga di Master Product belum berubah.</div>
            @elseif($batch->isMenungguJual())
                <div class="text-blue-700">ACC Beli sudah selesai. Harga di Master Product <b>belum berubah</b> sampai ACC Jual di bawah.</div>
            @endif
        </div>
        <a href="{{ route('penyesuaian-harga.show', $batch->id) }}" class="border border-[#162749] text-[#162749] px-4 py-2 rounded-xl text-sm hover:bg-gray-50">
            {{ $batch->isMenunggu() ? 'Buka di Beli untuk ACC' : 'Lihat di Beli' }}
        </a>
    </div>
</div>

{{-- ===================== RUMUS HARGA JUAL (hanya tampil di layar, tidak ikut PDF) ===================== --}}
<div class="{{ $card }} px-4 py-3 mb-4 border-l-4 border-l-[#E85D2A]">
    <div class="font-semibold text-[#162749] text-sm mb-2">Rumus Harga Jual</div>

    @if($batch->isBelumFinal())
        <form method="POST" action="{{ route('penyesuaian-harga-jual.pengali', $batch->id) }}" class="flex flex-wrap items-end gap-3 mb-2">
            @csrf
            @if($errors->any()) <div class="w-full text-xs text-red-700">{{ $errors->first() }}</div> @endif
            <div>
                <label class="block text-[11px] text-gray-500 mb-0.5">Pengali <b>Modul</b> (mis. 1,49)</label>
                <input type="text" inputmode="decimal" name="pengali_modul" value="{{ old('pengali_modul', rtrim(rtrim(number_format($pengaliModul, 3, '.', ''), '0'), '.')) }}" class="{{ $inp }} w-28">
            </div>
            <div>
                <label class="block text-[11px] text-gray-500 mb-0.5">Pengali <b>selain Modul</b> (mis. 1,2)</label>
                <input type="text" inputmode="decimal" name="pengali_lain" value="{{ old('pengali_lain', rtrim(rtrim(number_format($pengaliLain, 3, '.', ''), '0'), '.')) }}" class="{{ $inp }} w-28">
            </div>
            <button class="bg-[#162749] text-white px-4 py-2 rounded-xl text-sm">Simpan &amp; Hitung Ulang</button>
        </form>
        <div class="text-[11px] text-gray-500 mb-2">Kosongkan atau isi 0 = tanpa pengali, harga jual mengikuti harga beli (&times;1).</div>
    @endif

    <div class="text-xs text-gray-700 space-y-0.5">
        <div>&bull; <b>Modul</b> (semua produk Modul): Harga Beli &times; <b>{{ $fmtPengali($pengaliModul) }}</b>@if($pengaliModul == 1) <span class="text-gray-500">(tanpa pengali, sama dengan harga beli)</span>@endif</div>
        <div>&bull; <b>Selain Modul</b>: Harga Beli &times; <b>{{ $fmtPengali($pengaliLain) }}</b>@if($pengaliLain == 1) <span class="text-gray-500">(tanpa pengali, sama dengan harga beli)</span>@endif</div>
        <div>&bull; <b>Pembulatan</b> ke ribuan terdekat per satuan: sisa 500 ke atas naik, di bawah 500 turun.
            Contoh: 3.350 &rarr; 3.000 &middot; 3.499 &rarr; 3.000 &middot; 3.500 &rarr; 4.000.
            Di tabel, kolom Harga Jual Normal = sebelum dibulatkan, Harga Jual Bulat = sesudah dibulatkan.</div>
        <div class="text-gray-500">Harga paket = harga jual satuan &times; isi, dihitung saat di-ACC dan tersimpan di Master Product.</div>
    </div>
</div>

@if($batch->isMenungguJual())
<div class="{{ $card }} px-4 py-3 mb-4 border-l-4 border-l-green-600">
    <div class="font-semibold text-[#162749] text-sm mb-1">ACC Jual</div>
    <div class="text-xs text-gray-600 mb-2">
        Harga beli sudah di-ACC. Centang produk yang harga beli &amp; harga jualnya mau dipindah ke Master Product (bulk). Yang tidak dicentang tidak berubah.
        @if(request()->hasAny(['grup', 'cari']))
            <b class="text-[#E85D2A]">Filter sedang aktif: hanya baris yang tampil yang ikut dipilih.</b>
        @endif
    </div>
    @if($bolehReviewJual)
        <div class="flex flex-wrap gap-4 items-start">
            <form method="POST" id="formSetujui" action="{{ route('penyesuaian-harga-jual.setujui', $batch->id) }}" onsubmit="return konfirmasiSetujui()" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="text" name="catatan" placeholder="Catatan (opsional)" maxlength="500" class="{{ $inp }}">
                <button class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm">ACC Jual &amp; Publikasikan (<span id="jmlDipilih">0</span> dipilih)</button>
            </form>
            <form method="POST" action="{{ route('penyesuaian-harga-jual.tolak', $batch->id) }}" onsubmit="return confirm('Tolak import ini? Harga di Master Product tidak akan berubah.')" class="flex flex-wrap items-center gap-2">
                @csrf
                <input type="text" name="catatan" placeholder="Alasan penolakan" required minlength="5" maxlength="500" class="{{ $inp }}">
                <button class="bg-red-600 text-white px-4 py-2 rounded-xl text-sm">Tolak</button>
            </form>
        </div>
    @endif
</div>
@endif

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
                <th rowspan="2" class="px-3 py-3 text-left">@if($bolehReviewJual)<input type="checkbox" id="pilihSemua" checked>@endif</th>
                <th rowspan="2" class="px-3 py-3 text-left">No</th>
                <th rowspan="2" class="px-3 py-3 text-left">Nama Produk</th>
                <th colspan="2" class="px-3 pt-3 pb-1 text-center border-l border-white/20">Harga Lama 2024</th>
                <th colspan="3" class="px-3 pt-3 pb-1 text-center border-l border-white/20">Harga Baru 2026</th>
                <th rowspan="2" class="px-3 py-3 text-right border-l border-white/20">Kenaikan<br><span class="normal-case font-normal">harga jual %</span></th>
                <th rowspan="2" class="px-3 py-3 text-left">Status</th>
            </tr>
            <tr>
                <th class="px-3 pb-3 text-right border-l border-white/20">Harga Beli</th>
                <th class="px-3 pb-3 text-right">Harga Jual</th>
                <th class="px-3 pb-3 text-right border-l border-white/20">Harga Beli</th>
                <th class="px-3 pb-3 text-right">Harga Jual<br><span class="normal-case font-normal">Normal</span></th>
                <th class="px-3 pb-3 text-right">Harga Jual<br><span class="normal-case font-normal">Bulat</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php $grupSebelum = null; @endphp
            @forelse($rows as $r)
                @if($grupSebelum !== $r->grup)
                    @php $grupSebelum = $r->grup; @endphp
                    <tr class="bg-gray-50"><td colspan="10" class="px-3 py-2 font-semibold text-[#162749]">{{ $r->grup }}</td></tr>
                @endif
                @php
                    $p = $produk[$r->id] ?? null;
                    $j = $jual[$r->id];
                    $lama = $j['lama_satuan'];
                    $baru = $j['baru_satuan'];
                    $sama = $p && ($sudah26[$r->id] ?? false);
                    $persen = ($lama !== null && $lama > 0 && $baru !== null) ? (($baru - $lama) / $lama) * 100 : null;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                        @if($bolehReviewJual && $p && ! $r->diterapkan_pada && $r->disetujui_beli_pada && ! ($sudah26[$r->id] ?? false))
                            <input type="checkbox" class="cekBaris" name="ids[]" value="{{ $r->id }}" form="formSetujui" checked>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-gray-500">{{ $r->no }}</td>
                    <td class="px-3 py-2">
                        <div class="font-medium">{{ $r->nama }}</div>
                        @if($p)
                            <div class="text-[11px] text-gray-400">{{ $p->name }} ({{ $p->label }})
                                &middot; <span class="{{ ($j['modul'] ?? false) ? 'text-[#162749]' : 'text-[#E85D2A]' }} font-medium">{{ ($j['modul'] ?? false) ? 'Modul' : 'Bukan Modul' }} &times;{{ $fmtPengali($j['pengali']) }}</span>
                            </div>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right border-l border-gray-100">{{ $rp($j['beli_lama']) }}</td>
                    <td class="px-3 py-2 text-right text-gray-600">{{ $rp($lama) }}</td>
                    <td class="px-3 py-2 text-right border-l border-gray-100 font-semibold">{{ $rp($r->harga_2026) }}</td>
                    <td class="px-3 py-2 text-right text-gray-600">
                        @if($j['asli_satuan'] !== null)
                            Rp {{ rtrim(rtrim(number_format($j['asli_satuan'], 2, ',', '.'), '0'), ',') }}
                        @else - @endif
                    </td>
                    <td class="px-3 py-2 text-right font-semibold">{{ $rp($baru) }}</td>
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
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-800">Menunggu ACC Beli</span>
                        @elseif($batch->isMenungguJual() && $r->disetujui_beli_pada)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">Menunggu ACC Jual</span>
                        @elseif($batch->isMenungguJual() && ! $sama)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Tidak disetujui di Beli</span>
                        @elseif($sama)
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-800">Tidak diubah</span>
                        @else
                            <span class="inline-block px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600">Tidak dipilih</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="px-3 py-8 text-center text-gray-400">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-gray-500 mt-3">
    Harga jual di halaman ini per <b>satuan</b>. Harga paket (dikali isi) dihitung otomatis saat di-ACC dan tersimpan di Master Product.
    Untuk mengubah harga, import dan ACC di menu Penyesuaian Harga &rarr; Beli.
</p>

@if($bolehReviewJual)
<script>
    (function () {
        var semua = document.getElementById('pilihSemua');
        function baris() { return document.querySelectorAll('.cekBaris'); }
        function hitung() {
            var n = 0; baris().forEach(function (c) { if (c.checked) n++; });
            var el = document.getElementById('jmlDipilih'); if (el) el.textContent = n;
            if (semua) semua.checked = n > 0 && n === baris().length;
            return n;
        }
        baris().forEach(function (c) { c.addEventListener('change', hitung); });
        if (semua) semua.addEventListener('change', function () { baris().forEach(function (c) { c.checked = semua.checked; }); hitung(); });
        window.konfirmasiSetujui = function () {
            var n = hitung();
            if (n === 0) { alert('Centang minimal satu produk yang harganya mau diubah.'); return false; }
            return confirm('ACC Jual untuk ' + n + ' produk terpilih? Harga beli dan harga jual produk itu di Master Product akan berubah.');
        };
        hitung();
    })();
</script>
@endif
@endsection
