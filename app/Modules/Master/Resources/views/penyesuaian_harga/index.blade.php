@extends('layouts.panel')

@section('title', 'Penyesuaian Harga ' . ($modeJual ? 'Jual' : 'Beli'))

@section('content')
@include('partials.page-header', [
    'title'    => $modeJual ? 'Penyesuaian Harga Jual' : 'Penyesuaian Harga Beli',
    'subtitle' => $modeJual
        ? 'Perubahan harga jual mengikuti perubahan harga beli dari percetakan. Klik salah satu untuk melihat harga jual lama, baru, dan persentase kenaikannya.'
        : 'Daftar perubahan harga dari percetakan. Klik tanggal berlaku untuk melihat perbandingan harga dan data perubahannya.',
    'primary'  => $modeJual ? null : ['url' => route('penyesuaian-harga.create'), 'label' => 'Import Perubahan Harga'],
])

@include('partials.flash')

@php
    $inp = 'border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40';
    $bulanNama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    $statusBadge = [
        'menunggu'    => ['Menunggu ACC', 'bg-amber-100 text-amber-800'],
        'dipublikasi' => ['Dipublikasikan', 'bg-green-100 text-green-800'],
        'ditolak'     => ['Ditolak', 'bg-red-100 text-red-800'],
    ];
@endphp

<form method="GET" class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm text-gray-600 mb-1">Tahun</label>
            <select name="tahun" class="{{ $inp }} w-full">
                <option value="">Semua tahun</option>
                @foreach($tahunList as $t) <option value="{{ $t }}" @selected((string) request('tahun') === (string) $t)>{{ $t }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Bulan berlaku</label>
            <select name="bulan" class="{{ $inp }} w-full">
                <option value="">Semua bulan</option>
                @foreach($bulanNama as $n => $nm) <option value="{{ $n }}" @selected((string) request('bulan') === (string) $n)>{{ $nm }}</option> @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm text-gray-600 mb-1">Status</label>
            <select name="status" class="{{ $inp }} w-full">
                <option value="">Semua status</option>
                @foreach($statusBadge as $k => $b) <option value="{{ $k }}" @selected(request('status') === $k)>{{ $b[0] }}</option> @endforeach
            </select>
        </div>
    </div>
    <div class="mt-4 flex items-center gap-3">
        <button class="bg-[#162749] text-white px-5 py-2.5 rounded-xl text-sm">Terapkan filter</button>
        <a href="{{ route($modeJual ? 'penyesuaian-harga-jual.index' : 'penyesuaian-harga.index') }}" class="text-sm text-gray-500">Reset</a>
    </div>
</form>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
    <table class="min-w-full text-sm">
        <thead class="bg-gray-50 text-gray-600 text-xs">
            <tr>
                <th class="px-4 py-3 text-left">No</th>
                <th class="px-4 py-3 text-left">Perubahan Harga</th>
                <th class="px-4 py-3 text-left">Tanggal Berlaku</th>
                <th class="px-4 py-3 text-left">Nomor Surat</th>
                <th class="px-4 py-3 text-left">Tanggal Surat</th>
                <th class="px-4 py-3 text-right">Jumlah Item</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-left">Dipublikasi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($batches as $b)
                @php $tgl = $b->berlaku_mulai ?? $b->created_at; @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-500">{{ $batches->firstItem() + $loop->index }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route($modeJual ? 'penyesuaian-harga-jual.show' : 'penyesuaian-harga.show', $b->id) }}" class="font-semibold text-[#162749] hover:text-[#E85D2A]">
                            {{ strtoupper($b->namaTampil()) }}
                        </a>
                        <div class="text-[11px] text-gray-400">Perubahan ke-{{ $urutBatch[$b->id] ?? '-' }} tahun {{ $tgl->format('Y') }}</div>
                    </td>
                    <td class="px-4 py-3 font-semibold">{{ $tgl->translatedFormat('j F Y') }}</td>
                    <td class="px-4 py-3">{{ $b->nomor_surat ?: '-' }}</td>
                    <td class="px-4 py-3">{{ $b->tanggal_surat?->format('d/m/Y') ?? '-' }}</td>
                    <td class="px-4 py-3 text-right font-semibold">{{ $b->rows_count }}</td>
                    <td class="px-4 py-3"><span class="inline-block px-2 py-0.5 rounded-full text-xs {{ $statusBadge[$b->status][1] ?? '' }}">{{ $statusBadge[$b->status][0] ?? $b->status }}</span></td>
                    <td class="px-4 py-3 text-gray-600">{{ $b->published_at?->format('d/m/Y') ?? '-' }}</td>
                </tr>
            @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Belum ada perubahan harga. Klik "Import Perubahan Harga" di kanan atas.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 text-sm text-gray-500">
    Menampilkan {{ $batches->firstItem() ?? 0 }} sampai {{ $batches->lastItem() ?? 0 }} dari {{ $batches->total() }} perubahan
</div>
<div class="mt-2">{{ $batches->links() }}</div>
@endsection
