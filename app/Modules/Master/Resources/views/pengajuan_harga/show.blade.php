@extends('layouts.panel')

@section('title', 'Pengajuan ' . $req->nomor)

@section('content')
@php
    [$lbl, $cls] = \App\Modules\Master\Models\PriceChangeRequest::STATUS[$req->status] ?? [$req->status, 'bg-gray-100'];
    $rp  = fn ($v) => $v === null ? '-' : 'Rp ' . number_format((float) $v, 0, ',', '.');
    $sel = function ($lama, $baru) {
        if ($baru === null) return null;
        $d = (float) $baru - (float) $lama;
        $pct = (float) $lama > 0 ? $d / (float) $lama * 100 : null;
        return [$d, $pct];
    };
    $aksiStyle = [
        'diajukan'   => ['bg-blue-500',  'Diajukan'],
        'disetujui'  => ['bg-green-500', 'Disetujui'],
        'diterapkan' => ['bg-green-700', 'Harga diterapkan ke Master Product'],
        'ditolak'    => ['bg-red-500',   'Ditolak'],
        'dibatalkan' => ['bg-gray-400',  'Dibatalkan'],
    ];
    $inp = 'w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40';
@endphp

@include('partials.page-header', [
    'title'    => 'Pengajuan ' . $req->nomor,
    'subtitle' => 'Diajukan ' . $req->created_at->format('d/m/Y H:i') . ' oleh ' . ($req->creator->name ?? '-'),
])

@include('partials.flash')

<div class="mb-4 flex items-center gap-3">
    <span class="px-3 py-1 rounded-full text-xs font-medium {{ $cls }}">{{ $lbl }}</span>
    <a href="{{ route('pengajuan-harga.index') }}" class="text-sm text-gray-500">&larr; Kembali ke daftar</a>
    <a href="{{ route('pengajuan-harga.pdf', $req->id) }}" target="_blank"
       class="ml-auto inline-flex items-center gap-1.5 bg-[#162749] text-white px-4 py-2 rounded-xl text-sm font-medium hover:opacity-90">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12m0 0l-4-4m4 4l4-4M4 21h16"/></svg>
        Cetak PDF
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-5">
    <div class="lg:col-span-2 space-y-5">

        {{-- Apa yang berubah --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
            <div class="p-4 border-b font-semibold">Apa yang berubah ({{ $req->items->count() }} produk)</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-600">
                    <tr>
                        <th class="p-3 text-left">Produk</th>
                        <th class="p-3 text-right">Harga Beli</th>
                        <th class="p-3 text-right">Harga Jual</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($req->items as $it)
                    <tr class="border-t align-top">
                        <td class="p-3">
                            <div class="font-medium">{{ $it->product->name ?? '(produk dihapus)' }}</div>
                            <div class="text-xs text-gray-400">{{ $it->product->label ?? '' }}</div>
                        </td>
                        @foreach([['harga_beli_lama','harga_beli_baru'], ['harga_jual_lama','harga_jual_baru']] as [$a, $b])
                            @php $s = $sel($it->$a, $it->$b); @endphp
                            <td class="p-3 text-right">
                                @if($s)
                                    <div class="text-gray-400 line-through text-xs">{{ $rp($it->$a) }}</div>
                                    <div class="font-semibold">{{ $rp($it->$b) }}</div>
                                    <div class="text-xs {{ $s[0] >= 0 ? 'text-red-600' : 'text-green-700' }}">
                                        {{ $s[0] >= 0 ? '+' : '-' }}{{ $rp(abs($s[0])) }}
                                        @if($s[1] !== null) ({{ $s[0] >= 0 ? '+' : '' }}{{ number_format($s[1], 2, ',', '.') }}%) @endif
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">tidak diubah<br>{{ $rp($it->$a) }}</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- Surat & berkas --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="font-semibold mb-3">Surat permohonan &amp; berkas</div>
            <dl class="grid grid-cols-2 gap-3 text-sm mb-4">
                <div><dt class="text-xs text-gray-500">Nomor surat</dt><dd>{{ $req->nomor_surat ?: '-' }}</dd></div>
                <div><dt class="text-xs text-gray-500">Tanggal surat</dt><dd>{{ $req->tanggal_surat?->format('d/m/Y') ?? '-' }}</dd></div>
                <div class="col-span-2"><dt class="text-xs text-gray-500">Alasan</dt><dd>{{ $req->alasan }}</dd></div>
            </dl>

            @foreach(['surat' => 'Surat permohonan', 'pendukung' => 'Berkas pendukung'] as $jenis => $judul)
                @php $grup = $req->files->where('jenis', $jenis); @endphp
                @if($grup->isNotEmpty())
                    <div class="text-xs font-medium text-gray-500 mb-1.5 mt-3">{{ $judul }} ({{ $grup->count() }})</div>
                    <div class="flex flex-wrap gap-2">
                        @foreach($grup as $f)
                            <a href="{{ route('pengajuan-harga.berkas', [$req->id, $f->id]) }}" target="_blank"
                               class="inline-flex max-w-full items-center gap-1.5 border border-gray-300 rounded-xl px-3 py-1.5 text-sm hover:bg-gray-50">
                                <span>📎</span><span class="truncate">{{ $f->nama }}</span>
                            </a>
                        @endforeach
                    </div>
                    @foreach($grup->filter->isImage() as $f)
                        <img src="{{ route('pengajuan-harga.berkas', [$req->id, $f->id]) }}" alt="{{ $f->nama }}" class="mt-3 max-h-96 rounded-xl border">
                    @endforeach
                @endif
            @endforeach
        </div>

        {{-- Aksi --}}
        @if($bolehReview)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="font-semibold mb-3">Keputusan admin</div>
            <div class="grid md:grid-cols-2 gap-4">
                <form method="POST" action="{{ route('pengajuan-harga.approve', $req->id) }}" onsubmit="return confirm('Setujui dan terapkan harga baru ke Master Product?')">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan (opsional)</label>
                    <textarea name="catatan" rows="2" class="{{ $inp }}"></textarea>
                    <button class="mt-2 bg-green-600 text-white px-5 py-2 rounded-xl text-sm font-medium">✓ Setujui &amp; Terapkan</button>
                </form>
                <form method="POST" action="{{ route('pengajuan-harga.reject', $req->id) }}">
                    @csrf
                    <label class="block text-xs font-medium text-gray-600 mb-1">Alasan penolakan <span class="text-red-500">*</span></label>
                    <textarea name="catatan" rows="2" required minlength="5" class="{{ $inp }}"></textarea>
                    <button class="mt-2 bg-red-600 text-white px-5 py-2 rounded-xl text-sm font-medium">✕ Tolak</button>
                </form>
            </div>
        </div>
        @elseif($sendiri)
            <div class="p-3 bg-amber-50 text-amber-800 rounded-xl text-sm">Pengajuan ini dibuat oleh Anda sendiri, jadi harus disetujui admin lain.</div>
        @endif

        @if($bolehBatal)
            <form method="POST" action="{{ route('pengajuan-harga.cancel', $req->id) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                @csrf
                <button class="text-sm text-gray-500 underline">Batalkan pengajuan</button>
            </form>
        @endif
    </div>

    {{-- Kronologi --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 h-fit">
        <div class="font-semibold mb-4">Kronologi</div>
        <ol class="relative border-l-2 border-gray-200 ml-2 space-y-5">
            @foreach($req->logs as $log)
                @php [$warna, $judul] = $aksiStyle[$log->aksi] ?? ['bg-gray-400', ucfirst($log->aksi)]; @endphp
                <li class="ml-5">
                    <span class="absolute -left-[7px] w-3 h-3 rounded-full {{ $warna }}"></span>
                    <div class="text-sm font-semibold">{{ $judul }}</div>
                    <div class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i') }} · {{ $log->user->name ?? 'Sistem' }}</div>
                    @if($log->catatan)<div class="text-sm text-gray-700 mt-1">{{ $log->catatan }}</div>@endif
                </li>
            @endforeach
            @if($req->isMenunggu())
                <li class="ml-5">
                    <span class="absolute -left-[7px] w-3 h-3 rounded-full bg-amber-300 animate-pulse"></span>
                    <div class="text-sm text-amber-700">Menunggu keputusan admin…</div>
                </li>
            @endif
        </ol>
    </div>
</div>
@endsection
