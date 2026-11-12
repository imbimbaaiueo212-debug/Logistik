@extends('layouts.panel')

@section('title', 'Transfer Gudang')

@section('content')
@include('partials.page-header', [
    'title'     => 'Transfer Gudang',
    'subtitle'  => 'Input manual, hanya qty tanpa nominal uang. Ikut dihitung di Report Pengeluaran Logistik.',
    'back'      => route('pengeluaran-logistik.index'),
    'backLabel' => 'Kembali ke Report',
])

@include('partials.flash')

@php
    $inp = 'w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';
@endphp

@if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl mb-4">
        @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
    </div>
@endif

<form method="POST" action="{{ route('pengeluaran-logistik.transfer.store') }}"
      class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5 grid grid-cols-1 md:grid-cols-6 gap-3 items-end">
    @csrf
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal</label>
        <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" class="{{ $inp }}" required>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Dari</label>
        <input type="text" name="dari" value="{{ old('dari', 'Logistik') }}" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Tujuan</label>
        <input type="text" name="tujuan" list="daftar-tujuan" value="{{ old('tujuan') }}" class="{{ $inp }}" placeholder="Gudang / pihak tujuan" required>
        <datalist id="daftar-tujuan">
            @foreach($tujuanLama as $t) <option value="{{ $t }}"> @endforeach
        </datalist>
    </div>
    <div class="md:col-span-2">
        <label class="block text-xs font-medium text-gray-600 mb-1">Produk</label>
        <select name="product_id" class="{{ $inp }}" required>
            <option value="">Pilih produk</option>
            @foreach($produk as $p)
                <option value="{{ $p->id }}" {{ (string) old('product_id') === (string) $p->id ? 'selected' : '' }}>
                    {{ $p->name }}{{ $p->label ? ' (' . $p->label . ')' : '' }}{{ $p->kategori ? ' - ' . $p->kategori : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
        <input type="number" name="qty" min="1" value="{{ old('qty') }}" class="{{ $inp }}" required>
    </div>
    <div class="md:col-span-5">
        <label class="block text-xs font-medium text-gray-600 mb-1">Catatan (opsional)</label>
        <input type="text" name="catatan" value="{{ old('catatan') }}" class="{{ $inp }}">
    </div>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#E85D2A] text-white text-sm font-semibold hover:bg-[#D14E1F]">Simpan</button>
</form>

<form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
    <input type="date" name="dari" value="{{ request('dari') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white">
    <input type="date" name="sampai" value="{{ request('sampai') }}" class="border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white">
    <input type="text" name="tujuan" value="{{ request('tujuan') }}" placeholder="Cari tujuan" class="border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white">
    <button type="submit" class="px-5 py-2 rounded-xl bg-[#162749] text-white text-sm font-semibold">Filter</button>
</form>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[#162749] text-white text-xs">
                <tr>
                    <th class="text-left px-4 py-2.5">Tanggal</th>
                    <th class="text-left px-4 py-2.5">Dari</th>
                    <th class="text-left px-4 py-2.5">Tujuan</th>
                    <th class="text-left px-4 py-2.5">Produk</th>
                    <th class="text-left px-4 py-2.5">Kategori</th>
                    <th class="text-right px-4 py-2.5">Qty</th>
                    <th class="text-left px-4 py-2.5">Catatan</th>
                    <th class="px-4 py-2.5"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($data as $t)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2">{{ $t->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $t->dari }}</td>
                        <td class="px-4 py-2">{{ $t->tujuan }}</td>
                        <td class="px-4 py-2">{{ $t->nama_barang }} <span class="text-gray-400 text-xs">{{ $t->sku }}</span></td>
                        <td class="px-4 py-2">{{ $t->kategori ?: '-' }}</td>
                        <td class="px-4 py-2 text-right tabular-nums font-semibold">{{ number_format($t->qty) }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $t->catatan }}</td>
                        <td class="px-4 py-2 text-right">
                            <form method="POST" action="{{ route('pengeluaran-logistik.transfer.destroy', $t->id) }}"
                                  onsubmit="return confirm('Hapus transfer ini?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-gray-400">Belum ada transfer gudang.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $data->links() }}</div>
@endsection
