@extends('layouts.panel')

@section('title', 'biMBA Operasional 2 (OPS2)')

@section('content')

    @php
        // Kartu menu: [judul, keterangan, url]
        $menu = [
            ['KORWIL', 'Pesanan Majalah', route('pesanan-majalah.index')],
            ['PINWIL', 'Pesanan Majalah Kotamadya', route('pesanan-majalah-kotamadya.index')],
            ['JABODETABEK (PUW1)', 'Pesanan Majalah PUW1', route('pesanan-majalah-puw1.index')],
        ];
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">biMBA Operasional 2 (OPS2)</h1>
            <p class="mt-1 text-sm text-navy-950/55">Pilih wilayah pesanan majalah</p>
        </div>

        <a href="{{ route('order-manual.index') }}"
           class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Kembali
        </a>
    </div>

    {{-- ============ KARTU WILAYAH ============ --}}
    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($menu as [$judul, $ket, $url])
            <a href="{{ $url }}" class="group block">
                <div class="h-full bg-white rounded-2xl shadow-card p-6 transition-shadow group-hover:shadow-lg">
                    <div class="w-12 h-12 rounded-xl bg-navy-700/10 text-navy-700 flex items-center justify-center">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v15"/><path d="M14 10h5a1 1 0 0 1 1 1v9"/><path d="M8 8h2M8 12h2M8 16h2"/><path d="M3 20h18"/></svg>
                    </div>
                    <h3 class="mt-4 text-xl font-semibold text-navy-950 group-hover:text-rust-600 transition-colors">{{ $judul }}</h3>
                    <p class="mt-1 text-sm text-navy-950/55">{{ $ket }}</p>
                </div>
            </a>
        @endforeach
    </div>

@endsection