@extends('layouts.panel')

@section('title', 'Edit Data DLC')

@section('content')
@php
    $input = 'w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';
    $fields = [
        ['name' => 'edisi',   'label' => 'Edisi',   'required' => true],
        ['name' => 'judul',   'label' => 'Judul'],
        ['name' => 'periode', 'label' => 'Periode'],
        ['name' => 'no_ps',   'label' => 'No PS', 'placeholder' => 'Contoh: PS/DLC/2026/001'],
        ['name' => 'bulan',   'label' => 'Bulan'],
    ];
@endphp

<div class="max-w-4xl">
    <div class="flex items-center justify-between mb-5">
        <h2 class="text-2xl font-bold text-[#162749]">Edit Data DLC</h2>
        <a href="{{ route('import.dlc.index') }}"
           class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Kembali
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl mb-4">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('import.dlc.update', $periode->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Data periode --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
            <h3 class="text-base font-semibold text-[#162749] mb-4">Data Periode</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($fields as $f)
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            {{ $f['label'] }} @if(!empty($f['required']))<span class="text-red-500">*</span>@endif
                        </label>
                        <input type="text" name="{{ $f['name'] }}" value="{{ old($f['name'], $periode->{$f['name']}) }}"
                               placeholder="{{ $f['placeholder'] ?? '' }}" {{ !empty($f['required']) ? 'required' : '' }}
                               class="{{ $input }}">
                    </div>
                @endforeach

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="{{ $input }}">
                        @foreach(['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif'] as $val => $lbl)
                            <option value="{{ $val }}" {{ old('status', $periode->status) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        {{-- Daftar unit --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-[#162749]">Daftar Unit</h3>
                <button type="button" id="btn-tambah-unit"
                        class="inline-flex items-center gap-1.5 bg-[#162749] hover:bg-[#1D3361] text-white px-3.5 py-2 rounded-xl text-sm font-medium transition">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                    Tambah Unit
                </button>
            </div>

            <div id="container-units" class="space-y-3">
                @foreach($periode->pesanan as $index => $item)
                    <div class="unit-row flex items-end gap-3">
                        <input type="hidden" name="units[{{ $index }}][id]" value="{{ $item->id }}">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Unit</label>
                            <input type="text" name="units[{{ $index }}][nama_unit]" required
                                   value="{{ old("units.$index.nama_unit", $item->nama_unit) }}" class="{{ $input }}">
                        </div>
                        <div class="w-28">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
                            <input type="number" name="units[{{ $index }}][qty]" min="0" required
                                   value="{{ old("units.$index.qty", $item->qty) }}" class="{{ $input }} text-right">
                        </div>
                        <button type="button" title="Hapus unit" aria-label="Hapus unit"
                                class="btn-hapus-unit p-2.5 rounded-xl text-red-600 hover:bg-red-50 transition">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M9 7V4h6v3"/></svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('import.dlc.index') }}"
               class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Batal</a>
            <button type="submit"
                    class="px-6 py-2.5 bg-[#E85D2A] hover:bg-[#D14E1F] text-white rounded-xl text-sm font-medium transition">
                Simpan Perubahan
            </button>
        </div>
    </form>
</div>

{{-- Template baris unit baru --}}
<template id="tpl-unit">
    <div class="unit-row flex items-end gap-3">
        <input type="hidden" name="units[__I__][id]" value="">
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-600 mb-1">Nama Unit</label>
            <input type="text" name="units[__I__][nama_unit]" required class="{{ $input }}">
        </div>
        <div class="w-28">
            <label class="block text-xs font-medium text-gray-600 mb-1">Qty</label>
            <input type="number" name="units[__I__][qty]" min="0" value="0" required class="{{ $input }} text-right">
        </div>
        <button type="button" title="Hapus unit" aria-label="Hapus unit"
                class="btn-hapus-unit p-2.5 rounded-xl text-red-600 hover:bg-red-50 transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M9 7V4h6v3"/></svg>
        </button>
    </div>
</template>
@endsection

@push('scripts')
<script>
    (function () {
        var unitIndex = {{ $periode->pesanan->count() }};
        var container = document.getElementById('container-units');
        var tpl = document.getElementById('tpl-unit').innerHTML;

        document.getElementById('btn-tambah-unit').addEventListener('click', function () {
            container.insertAdjacentHTML('beforeend', tpl.replace(/__I__/g, unitIndex++));
        });

        container.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-hapus-unit');
            if (btn) btn.closest('.unit-row').remove();
        });
    })();
</script>
@endpush