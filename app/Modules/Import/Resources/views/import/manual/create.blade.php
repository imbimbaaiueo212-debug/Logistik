@extends('layouts.panel')

@section('title', 'Tambah Manual Pemesanan')

@section('content')
@php
    $ctl = 'w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';

    $fields = [
        ['name' => 'order_date',    'label' => 'Tanggal Order', 'type' => 'date'],
        ['name' => 'customer_name', 'label' => 'Nama Customer', 'type' => 'text'],
        ['name' => 'product_sku',   'label' => 'SKU',           'type' => 'text'],
        ['name' => 'product_name',  'label' => 'Produk',        'type' => 'text'],
        ['name' => 'qty',           'label' => 'Qty',           'type' => 'number', 'default' => 1],
        ['name' => 'price',         'label' => 'Harga',         'type' => 'number'],
    ];

    $statusOpt = ['pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed'];
@endphp

<div class="max-w-3xl">
    @include('partials.page-header', [
        'title'    => 'Tambah Manual Pemesanan',
        'subtitle' => 'Input data order manual',
        'back'     => route('import.manual'),
    ])
    @include('partials.flash')

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('import.manual.store') }}" method="POST"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($fields as $f)
                <div>
                    <label for="{{ $f['name'] }}" class="block text-sm font-medium text-gray-700 mb-1">{{ $f['label'] }}</label>
                    <input type="{{ $f['type'] }}" id="{{ $f['name'] }}" name="{{ $f['name'] }}"
                           value="{{ old($f['name'], $f['default'] ?? '') }}" class="{{ $ctl }}">
                </div>
            @endforeach

            <div class="sm:col-span-2">
                <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select id="status" name="status" class="{{ $ctl }}">
                    @foreach($statusOpt as $val => $lbl)
                        <option value="{{ $val }}" {{ old('status', 'pending') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
            <a href="{{ route('import.manual') }}"
               class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Batal
            </a>
            <button type="submit"
                    class="bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-6 py-2.5 rounded-xl text-sm font-medium transition">
                Simpan Data
            </button>
        </div>
    </form>
</div>
@endsection