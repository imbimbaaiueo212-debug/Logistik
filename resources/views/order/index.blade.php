@extends('layouts.panel')

@section('title', 'Realisasi Order')

@section('content')

    @include('partials.page-header', [
        'title'    => 'Realisasi Order',
    ])

    @php
        $menus = [
            ['route' => 'order.unit-aktif', 'title' => 'Data Order Unit Stokis Aktif'],
            ['route' => 'order.unit-pasif', 'title' => 'Data Order Unit Stokis Pasif'],
            ['route' => null, 'title' => 'Data Order Unit Distribution Point (Dropshipper)'],
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
                <h3 class="text-base font-semibold text-navy-950">{{ $menu['title'] }}</h3>
            </a>
        @endforeach
    </div>

@endsection