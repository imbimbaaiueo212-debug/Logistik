@extends('layouts.panel')

@section('title', 'Data Order Unit Stokis Pasif')

@section('content')

    @include('partials.page-header', [
        'title' => 'Data Order Unit Stokis Pasif',
    ])

    @php
        $menus = [
            ['route' => 'order.jakarta-aktif', 'title' => 'Jakarta Aktif'],
            ['route' => 'order.jakarta-pasif',      'title' => 'Jakarta Pasif'],
            ['route' => null, 'title' => 'Logistik'],
            ['route' => null, 'title' => 'Semarang'],
            ['route' => null, 'title' => 'Surabaya'],
            ['route' => null, 'title' => 'Inventaris'],
            ['route' => null, 'title' => 'InterVio (DLC)'],
            ['route' => null, 'title' => 'English biMBA Talk (EBT)'],
            ['route' => null, 'title' => 'Soccer School (biMBA SS)'],
        ];
    @endphp

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($menus as $menu)
            <a href="{{ $menu['route'] ? route($menu['route']) : '#' }}"
               class="group bg-white rounded-2xl shadow-card p-6 hover:shadow-lg transition-all flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-navy-800/10 text-navy-800 flex items-center justify-center mb-4 group-hover:bg-navy-800 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="4" y="4" width="16" height="16" rx="2"/><path d="M9 9h6M9 13h6M9 17h3"/>
                    </svg>
                </div>
                <h3 class="text-base font-semibold text-navy-950 break-words">{{ $menu['title'] }}</h3>
            </a>
        @endforeach
    </div>

    <div class="mt-8">
        <a href="{{ route('order.index') }}"
           class="inline-flex items-center gap-2 bg-white border border-gray-200 hover:border-navy-800 text-gray-700 hover:text-navy-800 px-5 py-2.5 rounded-xl text-sm font-medium transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 18l-6-6 6-6"/>
            </svg>
            Kembali ke Home
        </a>
    </div>

@endsection