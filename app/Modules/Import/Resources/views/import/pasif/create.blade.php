@extends('layouts.panel')

@section('title', 'Import Unit Pasif')

@section('content')
@php
    $ctl = 'w-full border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';
    $fields = [
        ['name' => 'edisi',   'label' => 'Edisi',   'default' => 'M159',      'ph' => 'M159', 'req' => true, 'span' => 'sm:col-span-3'],
        ['name' => 'no_ps',   'label' => 'No PS',   'default' => '',          'ph' => 'Opsional',            'span' => 'sm:col-span-3'],
        ['name' => 'periode', 'label' => 'Periode', 'default' => 'Juni 2026', 'ph' => 'Juni 2026',           'span' => 'sm:col-span-2'],
        ['name' => 'bulan',   'label' => 'Bulan',   'default' => 'Juni',      'ph' => '',                    'span' => 'sm:col-span-2'],
        ['name' => 'tahun',   'label' => 'Tahun',   'default' => '2026',      'ph' => '',                    'span' => 'sm:col-span-2'],
    ];
@endphp

<div class="max-w-2xl">
    @include('partials.page-header', [
        'title'    => 'Import Unit Pasif',
        'subtitle' => 'Upload file Excel rekap pemesanan majalah Unit Pasif',
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

    <form action="{{ route('import.pasif.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-6 gap-4">
            @foreach($fields as $f)
                <div class="{{ $f['span'] }}">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ $f['label'] }} @if(!empty($f['req']))<span class="text-red-500">*</span>@endif
                    </label>
                    <input type="text" name="{{ $f['name'] }}" value="{{ old($f['name'], $f['default']) }}"
                           placeholder="{{ $f['ph'] }}" {{ !empty($f['req']) ? 'required' : '' }} class="{{ $ctl }}">
                </div>
            @endforeach
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">File Excel <span class="text-red-500">*</span></label>
            <input type="file" name="import_file" accept=".xlsx,.xls,.csv" required
                   class="w-full border border-gray-300 rounded-xl px-3 py-2.5 text-sm text-gray-600
                          file:mr-3 file:py-1.5 file:px-3.5 file:rounded-lg file:border-0 file:text-sm file:font-medium
                          file:bg-[#162749]/5 file:text-[#28447F] hover:file:bg-[#162749]/10">
            <p class="text-xs text-gray-400 mt-2">
                Format kolom: NO | CABANG | biMBA-AIUEO UNIT | MAJALAH | Bacaan Unit | NO TELP | ALAMAT
            </p>
        </div>

        <div class="flex gap-3 pt-4 border-t border-gray-100">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 15V4M8 8l4-4 4 4"/><path d="M5 19h14"/></svg>
                Import Sekarang
            </button>
            <a href="{{ route('import.pasif.index') }}"
               class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                Batal
            </a>
        </div>
    </form>
</div>
@endsection
