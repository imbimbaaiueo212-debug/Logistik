@extends('layouts.panel')

@section('title', 'Spare Pasif 3%')

@section('content')
@php $n = fn ($v) => number_format((int) $v, 0, '.', ','); @endphp

@include('partials.page-header', [
    'title'     => 'Spare Pasif 3%',
    'subtitle'  => 'Dibuat sendiri, tidak masuk dari import Kalender. ID Order diambil dari biMBA Shop.',
    'back'      => route('kalender.pasif.index'),
    'backLabel' => 'Kembali ke Menu Pasif',
])
@include('partials.flash')

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

@include('partials.stat-cards', ['stats' => [
    ['Jumlah Transaksi', $n($totalTrx)],
    ['Total Qty Spare', $n($totalQty), true],
]])

{{-- ===== Form create ===== --}}
<form method="POST" action="{{ route('kalender.pasif.spare.store') }}"
      class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-5">
    @csrf
    <h3 class="text-base font-semibold text-[#162749] mb-3">Tambah Spare Pasif 3%</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-7 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID Order</label>
            <input type="text" value="" disabled placeholder="dari biMBA Shop"
                   class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 text-gray-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID Manual</label>
            <input type="text" name="id_manual" value="{{ old('id_manual', $idUsulan) }}" maxlength="50"
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 uppercase">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Order</label>
            <input type="date" name="tgl_order" value="{{ old('tgl_order', now()->format('Y-m-d')) }}" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" name="nama" value="{{ old('nama') }}" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Barang / SKU</label>
            <input type="text" name="sku" value="{{ old('sku') }}" list="opsi-sku" required placeholder="KLD27"
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 uppercase">
            <datalist id="opsi-sku">
                @foreach($opsiSku as $o)
                    <option value="{{ $o['sku'] }}">{{ $o['name'] }}</option>
                @endforeach
            </datalist>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
            <input type="number" name="qty" value="{{ old('qty') }}" min="1" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cetak Tahap ke</label>
            <input type="number" name="tahap" value="{{ old('tahap', 1) }}" min="1" max="20" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
    </div>
    <div class="mt-4">
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#E85D2A] text-white font-semibold hover:opacity-90">Simpan</button>
    </div>
</form>

{{-- ===== Filter ===== --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Edisi</label>
        <select name="edisi" onchange="this.form.submit()" class="border border-gray-300 rounded-xl px-3.5 py-2.5 bg-white">
            <option value="">Semua</option>
            @foreach($daftarEdisi as $e)
                <option value="{{ $e }}" {{ request('edisi') === $e ? 'selected' : '' }}>{{ $e }}</option>
            @endforeach
        </select>
    </div>
    <div class="flex-1 min-w-[220px]">
        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama, ID Manual, ID Order, SKU"
               class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
    </div>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#162749] text-white font-semibold">Terapkan</button>
    <a href="{{ route('kalender.pasif.spare') }}" class="py-2.5 text-gray-500 hover:text-gray-700">Reset</a>
</form>

{{-- ===== Daftar ===== --}}
<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="overflow-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-[#162749] text-white text-left">
                    <th class="py-2.5 px-4 font-semibold">No</th>
                    <th class="py-2.5 px-4 font-semibold">ID Order</th>
                    <th class="py-2.5 px-4 font-semibold">ID Manual</th>
                    <th class="py-2.5 px-4 font-semibold">Tanggal Order</th>
                    <th class="py-2.5 px-4 font-semibold">Nama</th>
                    <th class="py-2.5 px-4 font-semibold">Barang / SKU</th>
                    <th class="py-2.5 px-4 font-semibold text-right">Jumlah</th>
                    <th class="py-2.5 px-4 font-semibold text-center">Tahap</th>
                    <th class="py-2.5 px-4 font-semibold text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($rows as $i => $r)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 text-gray-500">{{ $rows->firstItem() + $i }}</td>
                        <td class="py-2 px-4 text-gray-400">{{ $r->order_id ?: '-' }}</td>
                        <td class="py-2 px-4 font-medium text-gray-800 whitespace-nowrap">{{ $r->id_manual }}</td>
                        <td class="py-2 px-4 whitespace-nowrap">{{ $r->tgl_order->format('d/m/Y') }}</td>
                        <td class="py-2 px-4">{{ $r->nama }}</td>
                        <td class="py-2 px-4 whitespace-nowrap">{{ $r->sku }}</td>
                        <td class="py-2 px-4 text-right font-semibold text-gray-800">{{ $n($r->qty) }}</td>
                        <td class="py-2 px-4 text-center whitespace-nowrap">Tahap {{ $r->tahap }}</td>
                        <td class="py-2 px-4 text-right whitespace-nowrap">
                            <a href="{{ route('kalender.pasif.spare.edit', $r->id) }}" class="text-[#28447F] hover:underline mr-3">Edit</a>
                            <form method="POST" action="{{ route('kalender.pasif.spare.destroy', $r->id) }}" class="inline"
                                  onsubmit="return confirm('Hapus {{ $r->id_manual }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="py-10 text-center text-gray-400">Belum ada data. Tambahkan lewat form di atas.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 font-semibold">
                    <td colspan="6" class="py-2.5 px-4 text-gray-800">TOTAL</td>
                    <td class="py-2.5 px-4 text-right text-[#28447F]">{{ $n($totalQty) }}</td>
                    <td></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="mt-4 pager">{{ $rows->links() }}</div>
@endsection
