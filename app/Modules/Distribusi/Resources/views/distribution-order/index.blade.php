@extends('layouts.panel')

@section('title', 'Distribution Order')

@section('content')

    @include('partials.page-header', [
        'title'    => 'Distribution Order',
        'subtitle' => 'Distribusi Barang Keluar',
    ])

    @php
        $menus = [
            ['route' => 'distribution-order.jakarta-aktif', 'title' => 'Jakarta Aktif', 'subtitle' => 'Distribution Order Jakarta Aktif'],
            ['route' => 'distribution-order.jakarta-pasif', 'title' => 'Jakarta Pasif', 'subtitle' => 'Distribution Order Jakarta Pasif'],
            ['route' => 'distribution-order.intervio', 'title' => 'InterVio (DLC)', 'subtitle' => 'Distribution Order DLC'],
            ['route' => 'distribution-order.ebt', 'title' => 'English biMBA Talk', 'subtitle' => 'Distribution Order EBT'],
            ['route' => 'distribution-order.manual', 'title' => 'Manual', 'subtitle' => 'Distribution Order Manual (Majalah / Modul / Sertifikat)', 'icon' => 'box'],
        ];
    @endphp

    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
        @foreach ($menus as $menu)
            <a href="{{ route($menu['route']) }}"
               class="group bg-white rounded-2xl shadow-card p-6 hover:shadow-lg transition-all flex flex-col">
                <div class="w-12 h-12 rounded-xl bg-navy-800/10 text-navy-800 flex items-center justify-center mb-4 group-hover:bg-navy-800 group-hover:text-white transition-colors">
                    @if (($menu['icon'] ?? 'truck') === 'box')
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 8l-9-5-9 5 9 5 9-5Z"/><path d="M3 8v8l9 5 9-5V8"/><path d="M12 13v8"/>
                        </svg>
                    @else
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 7h11v8H3z"/><path d="M14 10h4l3 3v2h-7z"/><circle cx="7" cy="17" r="1.5"/><circle cx="17" cy="17" r="1.5"/>
                        </svg>
                    @endif
                </div>
                <h3 class="text-base font-semibold text-navy-950">{{ $menu['title'] }}</h3>
                <p class="text-xs text-navy-950/45 mt-1">{{ $menu['subtitle'] }}</p>
            </a>
        @endforeach
    </div>

@endsection