@extends('layouts.panel')

@section('title', 'Jakarta Aktif')

@section('content')

    @include('partials.page-header', [
        'title' => 'Jakarta Aktif',
    ])

    @php
        $menus = [
            [
                'route' => 'order.jakarta-aktif.realisasi',
                'title' => 'Realisasi',
                'desc'  => 'Data realisasi order Jakarta Aktif',
                'icon'  => '<rect x="5" y="4" width="14" height="17" rx="2"/><path d="M9 4V3h6v1M9 11h6M9 15h6"/>',
            ],
            [
                'route' => 'order.jakarta-aktif',
                'title' => 'Rekap Aktual',
                'desc'  => 'Monitoring proses picking & persiapan',
                'icon'  => '<path d="M4 20V10M10 20V4M16 20v-7M22 20H2"/>',
            ],
        ];
    @endphp

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
        @foreach ($menus as $menu)
            <a href="{{ route($menu['route']) }}"
               class="group bg-white rounded-2xl shadow-card p-6 hover:shadow-lg transition-all flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-navy-800/10 text-navy-800 flex items-center justify-center mb-4 group-hover:bg-navy-800 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        {!! $menu['icon'] !!}
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-navy-950">{{ $menu['title'] }}</h3>
                <p class="text-sm text-gray-500 mt-1 break-words">{{ $menu['desc'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        <a href="{{ route('order.unit-pasif') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-navy-800 text-gray-700 hover:text-navy-800 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
            Kembali
        </a>
    </div>

@endsection