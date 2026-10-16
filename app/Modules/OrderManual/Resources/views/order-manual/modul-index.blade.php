@extends('layouts.panel')

@section('title', 'Order Manual Modul')

@section('content')

    @php
        // Kartu menu: [judul, keterangan, url, ikon]. Ikon: edit
        // Kartu lain (OPS2, DLC, Pasif) masih coming soon — nonaktif dulu
        $menu = [
            ['Manual Pemesanan Modul', 'Input data pemesanan modul secara manual, termasuk produk dan jumlah.', route('order-manual-modul.manual'), 'edit'],
        ];
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="min-w-0">
        <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Order Manual Modul</h1>
        <p class="mt-1 text-sm text-navy-950/55">Kelola data pemesanan modul secara manual</p>
    </div>

    {{-- ============ KARTU MENU ============ --}}
    <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-5">
        @foreach ($menu as [$judul, $ket, $url, $ikon])
            <a href="{{ $url }}" class="group block">
                <div class="h-full bg-white rounded-2xl shadow-card p-6 transition-shadow group-hover:shadow-lg">
                    <div class="w-12 h-12 rounded-xl bg-navy-700/10 text-navy-700 flex items-center justify-center">
                        @if ($ikon === 'edit')
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17l-1 3Z"/><path d="m14.5 7.5 2 2"/></svg>
                        @else
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v15"/><path d="M14 10h5a1 1 0 0 1 1 1v9"/><path d="M8 8h2M8 12h2M8 16h2"/><path d="M3 20h18"/></svg>
                        @endif
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-navy-950 group-hover:text-rust-600 transition-colors">{{ $judul }}</h3>
                    <p class="mt-1 text-sm text-navy-950/55">{{ $ket }}</p>
                </div>
            </a>
        @endforeach
    </div>

    {{-- ============ KEMBALI ============ --}}
    <div class="mt-8">
        <a href="{{ route('home') }}"
           class="inline-flex items-center gap-2 bg-white border border-navy-950/10 hover:border-navy-700 text-navy-950/70 hover:text-navy-700 text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
    </div>

@endsection