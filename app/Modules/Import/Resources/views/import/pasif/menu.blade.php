@extends('layouts.panel')

@section('title', 'Unit Pasif')

@section('content')
@php
    $menu = [
        [
            'route' => 'import.pasif.list',
            'title' => 'Unit Pasif',
            'desc'  => 'Data pemesanan majalah Unit Pasif (import Excel & sync ke Manual).',
            'icon'  => '<path d="M4 5.5A1.5 1.5 0 0 1 5.5 4H20v14H5.5A1.5 1.5 0 0 0 4 19.5v-14Z"/><path d="M4 19.5A1.5 1.5 0 0 0 5.5 21H20"/>',
        ],
        [
            'route' => 'import.pasif.spare',
            'title' => 'Spare Pasif 3%',
            'desc'  => 'Total (DLC + Pasif + Bacaan) × 3% dibulatkan. Digunakan untuk hitung lembar print (200/lembar).',
            'icon'  => '<path d="M4 8.5 12 4l8 4.5v7L12 20l-8-4.5Z"/><path d="M4 8.5 12 13l8-4.5M12 13v7"/>',
        ],
        [
            'route' => 'import.pasif.bacaan',
            'title' => 'Bacaan Unit',
            'desc'  => 'Data Bacaan Unit dari pemesanan majalah Pasif.',
            'icon'  => '<path d="M12 6c-2-1.5-4.5-2-8-2v14c3.5 0 6 .5 8 2 2-1.5 4.5-2 8-2V4c-3.5 0-6 .5-8 2Z"/><path d="M12 6v14"/>',
        ],
        [
            'route' => 'import.pasif.rekap',
            'title' => 'Import',
            'desc'  => 'Hasil import lengkap: Bacaan Unit + Qty Majalah.',
            'icon'  => '<path d="M12 4v11M8 11l4 4 4-4"/><path d="M5 19h14"/>',
        ],
        [
            'route' => 'import.pasif.manual.index',
            'title' => 'Create Manual',
            'desc'  => 'Input manual pesanan majalah pasif.',
            'icon'  => '<path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/>',
        ],
        [
            'route' => 'import.report-angka-cetak',
            'title' => 'Report Angka Cetak',
            'desc'  => 'Ringkasan qty cetak majalah per edisi (mirip Excel).',
            'icon'  => '<path d="M5 20V10M12 20V4M19 20v-7"/>',
        ],
    ];
@endphp

@include('partials.page-header', [
    'title'    => 'Unit Pasif',
    'subtitle' => 'Pilih jenis data yang ingin dikelola',
    'back'     => route('order-manual.index'),
])

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 max-w-5xl">
    @foreach($menu as $m)
        <a href="{{ route($m['route']) }}" class="group block h-full">
            <div class="h-full flex flex-col bg-white rounded-2xl border border-gray-100 shadow-sm p-5
                        hover:shadow-md hover:border-[#E85D2A]/50 transition">
                <div class="w-11 h-11 rounded-xl bg-[#162749]/5 text-[#28447F] flex items-center justify-center mb-4
                            group-hover:bg-[#E85D2A]/10 group-hover:text-[#E85D2A] transition">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $m['icon'] !!}</svg>
                </div>
                <h3 class="text-base font-semibold text-[#162749] mb-1.5">{{ $m['title'] }}</h3>
                <p class="text-sm text-gray-500 leading-relaxed flex-1">{{ $m['desc'] }}</p>
            </div>
        </a>
    @endforeach
</div>
@endsection
