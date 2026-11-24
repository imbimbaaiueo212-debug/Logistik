@extends('layouts.panel')

@section('title', 'Penyesuaian Harga')

@section('content')
@include('partials.page-header', [
    'title'     => $batch->namaTampil(),
    'subtitle'  => 'Berlaku ' . ($batch->berlaku_mulai ?? $batch->created_at)->translatedFormat('j F Y') . '. Harga 2024 percetakan dibandingkan dengan harga di aplikasi (Harga Beli), lalu persentase penyesuaian ke harga 2026 dihitung ulang dari harga aplikasi.',
    'back'      => route('penyesuaian-harga.index'),
    'backLabel' => 'Kembali',
])

@include('partials.flash')

@php
    $rp = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $pct = fn ($v) => $v === null ? '-' : ($v > 0 ? '+' : '') . number_format($v, 2, ',', '.') . '%';
    $badge = [
        'sama_beli'   => ['Sama dengan 2024', 'bg-green-100 text-green-800'],
        'sama_jual'   => ['Sama (Harga Jual)', 'bg-blue-100 text-blue-800'],
        'beda'        => ['Beda dari 2024', 'bg-red-100 text-red-800'],
        'belum_cocok' => ['Produk belum ketemu', 'bg-gray-100 text-gray-600'],
    ];
    $statusBatch = [
        'menunggu'    => 'bg-amber-100 text-amber-800',
        'dipublikasi' => 'bg-green-100 text-green-800',
        'ditolak'     => 'bg-red-100 text-red-800',
    ];
    $inp = 'border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40';
    $card = 'bg-white rounded-2xl shadow-sm border border-gray-100';
    $fmtPengali = fn ($v) => number_format($v, 2, ',', '.');
@endphp

{{-- ===================== STATUS IMPORT / ACC ===================== --}}
@if($batch)
    @php $belumCocokBatch = collect($status)->filter(fn ($s) => $s === 'belum_cocok')->count(); @endphp
    <div class="{{ $card }} px-4 py-3 mb-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold text-[#162749]">{{ $batch->namaTampil() }}</span>
                    <span class="inline-block px-2 py-0.5 rounded-full text-xs {{ $statusBatch[$batch->status] ?? '' }}">{{ $batch->labelStatus() }}</span>
                </div>
                <div class="text-xs text-gray-600 mt-1 space-y-0.5">
                    <div>
                        Surat: <b>{{ $batch->nomor_surat ?: '-' }}</b>
                        @if($batch->tanggal_surat) &middot; {{ $batch->tanggal_surat->format('d/m/Y') }} @endif
                        @if($batch->berlaku_mulai) &middot; berlaku mulai <b>{{ $batch->berlaku_mulai->format('d/m/Y') }}</b> @endif
                    </div>
                    <div>Diimport oleh {{ $batch->creator->name ?? '-' }} pada {{ $batch->created_at->format('d/m/Y H:i') }}</div>
                    <details class="pt-1" @if($errors->any()) open @endif>
                        <summary class="cursor-pointer text-[#E85D2A] underline">Ubah info (judul, nomor surat, tanggal)</summary>
                        <form method="POST" action="{{ route('penyesuaian-harga.ubah-info', $batch->id) }}" class="mt-2 grid grid-cols-1 md:grid-cols-4 gap-2 max-w-4xl">
                            @csrf
                            @if($errors->any()) <div class="md:col-span-4 text-red-700">{{ $errors->first() }}</div> @endif
                            <div class="md:col-span-4">
                                <label class="block text-[11px] text-gray-500 mb-0.5">Judul (kosong = otomatis)</label>
                                <input type="text" name="judul" value="{{ old('judul', $batch->judul) }}" maxlength="150" placeholder="{{ 'Perubahan Harga ' . ($batch->berlaku_mulai ?? $batch->created_at)->translatedFormat('F Y') }}" class="{{ $inp }} w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-0.5">Nomor surat</label>
                                <input type="text" name="nomor_surat" value="{{ old('nomor_surat', $batch->nomor_surat) }}" maxlength="100" class="{{ $inp }} w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-0.5">Tanggal surat</label>
                                <input type="date" name="tanggal_surat" value="{{ old('tanggal_surat', $batch->tanggal_surat?->format('Y-m-d')) }}" class="{{ $inp }} w-full">
                            </div>
                            <div>
                                <label class="block text-[11px] text-gray-500 mb-0.5">Berlaku mulai</label>
                                <input type="date" name="berlaku_mulai" value="{{ old('berlaku_mulai', ($batch->berlaku_mulai ?? $batch->created_at)->format('Y-m-d')) }}" required class="{{ $inp }} w-full">
                            </div>
                            <div class="flex items-end"><button class="bg-[#162749] text-white px-4 py-2 rounded-xl text-sm">Simpan</button></div>
                        </form>
                    </details>
                    @if($batch->catatan) <div>Catatan: {{ $batch->catatan }}</div> @endif
                    @if($batch->reviewed_at)
                        <div>
                            {{ $batch->status === 'ditolak' ? 'Ditolak' : 'Disetujui' }} oleh {{ $batch->reviewer->name ?? '-' }}
                            pada {{ $batch->reviewed_at->format('d/m/Y H:i') }}
                            @if($batch->catatan_review) &middot; {{ $batch->catatan_review }} @endif
                        </div>
                    @endif
                    <div class="pt-1">
                        @foreach($batch->files as $f)
                            <a href="{{ route('penyesuaian-harga.berkas', [$batch->id, $f->id]) }}" target="_blank"
                               class="inline-block mr-2 text-[#E85D2A] underline">{{ $f->labelJenis() }}@if($f->jenis === 'data') ({{ $f->nama }})@endif</a>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>

        @if($batch->status !== 'dipublikasi')
            <form method="POST" action="{{ route('penyesuaian-harga.hapus', $batch->id) }}" class="mt-2"
                  onsubmit="return confirm('Hapus import #{{ $batch->id }} beserta berkasnya? Harga di Master Product tidak berubah.')">
                @csrf
                <button class="text-xs text-red-600 underline">Hapus import ini</button>
            </form>
        @endif

        @if($batch->isMenunggu())
            <div class="mt-3 border-t border-gray-100 pt-3">
                <div class="text-sm text-gray-700 mb-2">
                    Harga di Master Product <b>belum berubah</b>. Jika disetujui: Harga Beli = harga 2026 percetakan, Harga Jual otomatis
                    (Modul &times; {{ $fmtPengali($pengaliModul) }}, selain Modul &times; {{ $fmtPengali($pengaliLain) }}, dibulatkan ke atas per Rp 50).
                </div>
                @if($belumCocokBatch > 0)
                    <div class="text-xs bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-3 py-2 mb-2">
                        {{ $belumCocokBatch }} item belum ketemu produknya di Master Product, jadi tidak ikut dipublikasikan.
                    </div>
                @endif

                @if($bolehReview)
                    <div class="text-xs text-gray-600 mb-2">
                        Centang produk yang harganya mau diubah (bulk). Yang tidak dicentang tidak berubah. Produk yang Harga Belinya sudah sama dengan 2026 tidak masuk bulk dan diberi keterangan "sudah mengikuti 2026".
                        @if(request()->hasAny(['grup', 'status', 'kesesuaian', 'cari']))
                            <b class="text-[#E85D2A]">Filter sedang aktif: hanya baris yang tampil yang ikut dipilih.</b>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-4 items-start">
                        <form method="POST" id="formSetujui" action="{{ route('penyesuaian-harga.setujui', $batch->id) }}"
                              onsubmit="return konfirmasiSetujui()"
                              class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="text" name="catatan" placeholder="Catatan (opsional)" maxlength="500" class="{{ $inp }}">
                            <button class="bg-green-600 text-white px-4 py-2 rounded-xl text-sm">Setujui &amp; Publikasikan (<span id="jmlDipilih">0</span> dipilih)</button>
                        </form>
                        <form method="POST" action="{{ route('penyesuaian-harga.tolak', $batch->id) }}"
                              onsubmit="return confirm('Tolak import ini? Harga di Master Product tidak akan berubah.')"
                              class="flex flex-wrap items-center gap-2">
                            @csrf
                            <input type="text" name="catatan" placeholder="Alasan penolakan" required minlength="5" maxlength="500" class="{{ $inp }}">
                            <button class="bg-red-600 text-white px-4 py-2 rounded-xl text-sm">Tolak</button>
                        </form>
                    </div>
                @else
                    <div class="text-xs text-gray-500">Menunggu ACC dari admin lain.</div>
                @endif
            </div>
        @endif
    </div>
@endif


@php
    $suratFile = $batch->files->firstWhere('jenis', 'surat');
    $suratExt  = $suratFile ? strtolower(pathinfo($suratFile->path, PATHINFO_EXTENSION)) : null;
    $suratUrl  = $suratFile ? route('penyesuaian-harga.berkas', [$batch->id, $suratFile->id]) : null;
@endphp

{{-- ===================== RINGKASAN ===================== --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    @foreach([['Total item', $ringkas['total'], 'text-[#162749]'], ['Sama dengan 2024', $ringkas['sama'], 'text-green-700'], ['Beda dari 2024', $ringkas['beda'], 'text-red-700'], ['Produk belum ketemu', $ringkas['belum_cocok'], 'text-gray-600']] as [$l, $v, $c])
        <div class="{{ $card }} px-4 py-3">
            <div class="text-xs text-gray-500">{{ $l }}</div>
            <div class="text-2xl font-bold {{ $c }}">{{ $v }}</div>
        </div>
    @endforeach
</div>

{{-- ===================== FILTER ===================== --}}
<form method="GET" action="{{ route('penyesuaian-harga.show', $batch->id) }}" class="{{ $card }} p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Kelompok</label>
        <select name="grup" class="{{ $inp }}">
            <option value="">Semua</option>
            @foreach($grupList as $g) <option value="{{ $g }}" @selected(request('grup') === $g)>{{ $g }}</option> @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
        <select name="status" class="{{ $inp }}">
            <option value="">Semua</option>
            @foreach($badge as $k => $b) <option value="{{ $k }}" @selected(request('status') === $k)>{{ $b[0] }}</option> @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Kesesuaian</label>
        <select name="kesesuaian" class="{{ $inp }}">
            <option value="">Semua</option>
            <option value="sesuai" @selected(request('kesesuaian') === 'sesuai')>Sudah sesuai</option>
            <option value="tidak" @selected(request('kesesuaian') === 'tidak')>Tidak sesuai</option>
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Cari nama</label>
        <input type="text" name="cari" value="{{ request('cari') }}" class="{{ $inp }}" placeholder="mis. Kaos Seragam">
    </div>
    <button class="bg-[#162749] text-white px-4 py-2 rounded-xl text-sm">Filter</button>
    <a href="{{ route('penyesuaian-harga.show', $batch->id) }}" class="border border-gray-300 text-gray-700 px-4 py-2 rounded-xl text-sm">Reset</a>
    <div class="ml-auto flex items-center gap-2">
        @if($suratFile)
            <button type="button" onclick="bukaSurat()" class="border border-[#162749] text-[#162749] px-4 py-2 rounded-xl text-sm hover:bg-gray-50">Lihat Surat</button>
        @endif
            <a href="{{ route('penyesuaian-harga.pdf', ['batch' => $batch->id] + request()->query()) }}" target="_blank"
       class="bg-[#E85D2A] text-white px-4 py-2 rounded-xl text-sm">Unduh PDF</a>
    </div>
</form>

{{-- ===================== TABEL ===================== --}}
<div class="{{ $card }} overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-[#162749] text-white text-xs uppercase">
            <tr>
                <th class="px-3 py-3 text-left w-8">
                    @if($bolehReview) <input type="checkbox" id="pilihSemua" title="Pilih semua" checked> @endif
                </th>
                <th class="px-3 py-3 text-left">No</th>
                <th class="px-3 py-3 text-left">Nama Produk</th>
                <th class="px-3 py-3 text-right">Harga 2024<br><span class="normal-case font-normal">Supplier</span></th>
                <th class="px-3 py-3 text-right">Harga 2024<br><span class="normal-case font-normal">Aplikasi (Harga Beli)</span></th>
                <th class="px-3 py-3 text-right">Selisih</th>
                <th class="px-3 py-3 text-right">Harga 2026<br><span class="normal-case font-normal">Pengajuan Supplier (Harga Beli Baru)</span></th>
                <th class="px-3 py-3 text-right">Kenaikan<br><span class="normal-case font-normal">%</span></th>
                <th class="px-3 py-3 text-left">Status</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @php $grupSebelum = null; @endphp
            @forelse($rows as $r)
                @if($grupSebelum !== $r->grup)
                    @php $grupSebelum = $r->grup; @endphp
                    <tr class="bg-gray-50"><td colspan="9" class="px-3 py-2 font-semibold text-[#162749]">{{ $r->grup }}</td></tr>
                @endif
                @php
                    $p   = $produk[$r->id] ?? null;
                    $st  = $status[$r->id];
                    $j   = $jual[$r->id];
                    // Setelah dipublikasi, harga beli aplikasi di produk sudah 2026; pakai harga sebelum diubah sebagai pembanding
                    $beliApp = $p ? ($r->diterapkan_pada ? $r->harga_beli_sebelum : $p->harga_beli) : null;
                    $app = $beliApp !== null ? (float) $beliApp : null;
                    $h24 = (float) $r->harga_2024;
                    $h26 = (float) $r->harga_2026;
                    $selisih  = $app !== null ? $app - $h24 : null;
                    $naikPdf  = $h24 > 0 ? (($h26 - $h24) / $h24) * 100 : null;
                @endphp
                @php $bisaPilih = $bolehReview && $p && ! $r->diterapkan_pada && ! ($sudah26[$r->id] ?? false); @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-3 py-2">
                        @if($bisaPilih)
                            <input type="checkbox" class="cekBaris" name="ids[]" value="{{ $r->id }}" form="formSetujui" checked>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-gray-500">{{ $r->no }}</td>
                    <td class="px-3 py-2">
                        <div class="font-medium">{{ $r->nama }}</div>
                        @if($p)
                            <div class="text-[11px] text-gray-400">{{ $p->name }} ({{ $p->label }})</div>
                        @endif
                    </td>
                    <td class="px-3 py-2 text-right">{{ $rp($h24) }}</td>
                    <td class="px-3 py-2 text-right">{{ $rp($app) }}</td>
                    <td class="px-3 py-2 text-right {{ $selisih === null ? 'text-gray-400' : ($selisih == 0 ? 'text-green-700' : 'text-red-700') }}">
                        {{ $selisih === null ? '-' : ($selisih > 0 ? '+' : '') . number_format($selisih, 0, ',', '.') }}
                    </td>
                    <td class="px-3 py-2 text-right font-semibold">{{ $rp($h26) }}</td>
                    <td class="px-3 py-2 text-right text-gray-500">{{ $pct($naikPdf) }}</td>
                    <td class="px-3 py-2">
                        <span class="inline-block px-2 py-0.5 rounded-full text-xs {{ $badge[$st][1] }}">{{ $badge[$st][0] }}</span>
                        @if($r->diterapkan_pada)
                            <div class="text-[10px] text-green-700 mt-0.5">&#10003; dipublikasi {{ $r->diterapkan_pada->format('d/m/Y') }}</div>
                        @elseif($sudah26[$r->id] ?? false)
                            <div class="text-[10px] text-blue-700 mt-0.5">&#10003; Harga sudah mengikuti 2026 (tidak diubah)</div>
                        @elseif($p && $batch->status === 'dipublikasi')
                            <div class="text-[10px] text-gray-500 mt-0.5">Tidak dipilih, harga tetap</div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="px-3 py-8 text-center text-gray-400">{{ $batch ? 'Tidak ada data.' : 'Belum ada data. Import PDF dari percetakan terlebih dahulu.' }}</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<p class="text-xs text-gray-500 mt-3">
    Selisih = Harga Beli aplikasi &minus; Harga 2024 percetakan. Kenaikan % = (2026 &minus; 2024) / 2024.
    Harga Beli baru = harga 2026 pengajuan percetakan. Harga Jual ikut berubah otomatis saat di-ACC, lihat rinciannya di menu Penyesuaian Harga &rarr; Jual.
    Harga di Master Product hanya berubah setelah import di-ACC.
</p>
{{-- ===================== MODAL LIHAT SURAT ===================== --}}
@if($suratFile)
<div id="modalSurat" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4" onclick="if(event.target===this)tutupSurat()">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl flex flex-col" style="height: 92vh;">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between gap-3">
            <div class="min-w-0">
                <div class="font-semibold text-[#162749]">Surat Permohonan / Bukti</div>
                <div class="text-[11px] text-gray-400 truncate">{{ $suratFile->nama }}</div>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ $suratUrl }}" target="_blank" class="text-xs text-[#E85D2A] underline">Buka di tab baru</a>
                <button type="button" onclick="tutupSurat()" class="text-gray-500 hover:text-gray-800 text-xl leading-none">&times;</button>
            </div>
        </div>
        <div class="flex-1 min-h-0 bg-gray-100 overflow-auto rounded-b-2xl">
            @if($suratExt === 'pdf')
                <iframe id="frameSurat" data-src="{{ $suratUrl }}#toolbar=0&navpanes=0&view=FitH" title="Surat permohonan" class="w-full h-full"></iframe>
            @elseif(in_array($suratExt, ['jpg', 'jpeg', 'png'], true))
                <img src="{{ $suratUrl }}" alt="Surat permohonan" class="w-full h-auto">
            @else
                <div class="p-8 text-sm text-gray-500 text-center">
                    File {{ strtoupper($suratExt) }} tidak bisa ditampilkan langsung.<br>
                    <a href="{{ $suratUrl }}" class="text-[#E85D2A] underline">Unduh surat</a>
                </div>
            @endif
        </div>
    </div>
</div>
<script>
    function bukaSurat() {
        var m = document.getElementById('modalSurat'), f = document.getElementById('frameSurat');
        if (f && !f.src) f.src = f.dataset.src;
        m.classList.remove('hidden'); m.classList.add('flex');
    }
    function tutupSurat() {
        var m = document.getElementById('modalSurat');
        m.classList.add('hidden'); m.classList.remove('flex');
    }
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') tutupSurat(); });
</script>
@endif

@if($bolehReview)
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
            return confirm('Setujui dan publikasikan ' + n + ' produk terpilih? Harga beli dan harga jual produk itu di Master Product akan berubah.');
        };
        hitung();
    })();
</script>
@endif
@endsection
