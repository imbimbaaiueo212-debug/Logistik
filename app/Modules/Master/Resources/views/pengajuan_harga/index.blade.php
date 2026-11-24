@extends('layouts.panel')

@section('title', 'Pengajuan Harga')

@section('content')
@include('partials.page-header', [
    'title'    => 'Pengajuan Perubahan Harga',
    'subtitle' => 'Setiap perubahan harga produk diajukan dulu beserta surat permohonan, lalu disetujui admin sebelum harga di Master Product berubah.',
])

@include('partials.flash')

@php
    $inp = 'border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40';
@endphp

<div class="flex flex-wrap gap-3 items-end justify-between mb-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
            <select name="status" class="{{ $inp }}">
                <option value="">Semua</option>
                @foreach(\App\Modules\Master\Models\PriceChangeRequest::STATUS as $k => [$label])
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-600 mb-1">Cari nomor</label>
            <input type="text" name="cari" value="{{ request('cari') }}" placeholder="PH-2026-0001 / no. surat" class="{{ $inp }}">
        </div>
        <button class="bg-[#162749] text-white px-4 py-2 rounded-xl text-sm">Filter</button>
        <a href="{{ route('pengajuan-harga.index') }}" class="border border-gray-300 px-4 py-2 rounded-xl text-sm">Reset</a>
    </form>

    <a href="{{ route('pengajuan-harga.create') }}" class="bg-[#E85D2A] text-white px-4 py-2 rounded-xl text-sm font-medium hover:opacity-90">+ Buat Pengajuan</a>
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-[#162749] text-white text-xs uppercase">
            <tr>
                <th class="p-3 text-left">Nomor</th>
                <th class="p-3 text-left">Tanggal</th>
                <th class="p-3 text-left">Diajukan oleh</th>
                <th class="p-3 text-center">Produk</th>
                <th class="p-3 text-left">No. Surat</th>
                <th class="p-3 text-left">Status</th>
                <th class="p-3"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($list as $r)
                @php [$lbl, $cls] = \App\Modules\Master\Models\PriceChangeRequest::STATUS[$r->status] ?? [$r->status, 'bg-gray-100']; @endphp
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-3 font-semibold">{{ $r->nomor }}</td>
                    <td class="p-3">{{ $r->created_at->format('d/m/Y H:i') }}</td>
                    <td class="p-3">{{ $r->creator->name ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $r->items_count }}</td>
                    <td class="p-3">{{ $r->nomor_surat ?: '-' }}</td>
                    <td class="p-3"><span class="px-3 py-1 rounded-full text-xs font-medium {{ $cls }}">{{ $lbl }}</span></td>
                    <td class="p-3 text-right"><a href="{{ route('pengajuan-harga.show', $r->id) }}" class="text-[#E85D2A] font-medium">Lihat</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="p-8 text-center text-gray-500">Belum ada pengajuan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $list->links() }}</div>
@endsection
