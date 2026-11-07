@extends('layouts.panel')

@section('title', 'Edit Spare Pasif 3%')

@section('content')
@include('partials.page-header', [
    'title'     => 'Edit Spare Pasif 3%',
    'subtitle'  => $item->id_manual,
    'back'      => route('kalender.pasif.spare'),
    'backLabel' => 'Kembali',
])
@include('partials.flash')

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 px-4 py-3 text-sm">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<form method="POST" action="{{ route('kalender.pasif.spare.update', $item->id) }}"
      class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 max-w-3xl">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID Order</label>
            <input type="text" value="{{ $item->order_id }}" disabled placeholder="dari biMBA Shop"
                   class="w-full border border-gray-200 bg-gray-50 rounded-xl px-3.5 py-2.5 text-gray-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID Manual</label>
            <input type="text" name="id_manual" value="{{ old('id_manual', $item->id_manual) }}" maxlength="50"
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 uppercase">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Order</label>
            <input type="date" name="tgl_order" value="{{ old('tgl_order', $item->tgl_order->format('Y-m-d')) }}" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" name="nama" value="{{ old('nama', $item->nama) }}" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Barang / SKU</label>
            <input type="text" name="sku" value="{{ old('sku', $item->sku) }}" list="opsi-sku" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5 uppercase">
            <datalist id="opsi-sku">
                @foreach($opsiSku as $o)
                    <option value="{{ $o['sku'] }}">{{ $o['name'] }}</option>
                @endforeach
            </datalist>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
            <input type="number" name="qty" value="{{ old('qty', $item->qty) }}" min="1" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cetak Tahap ke</label>
            <input type="number" name="tahap" value="{{ old('tahap', $item->tahap) }}" min="1" max="20" required
                   class="w-full border border-gray-300 rounded-xl px-3.5 py-2.5">
        </div>
    </div>
    <div class="mt-5 flex gap-3">
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#E85D2A] text-white font-semibold hover:opacity-90">Simpan Perubahan</button>
        <a href="{{ route('kalender.pasif.spare') }}" class="px-5 py-2.5 text-gray-500 hover:text-gray-700">Batal</a>
    </div>
</form>
@endsection
