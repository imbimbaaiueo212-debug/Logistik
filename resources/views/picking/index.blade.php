@extends('layouts.panel')

@section('title', 'Picking - biMBA Logistik')

@section('content')
<div class="p-8">
    <div class="flex justify-between items-center mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Picking List</h2>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

        {{-- Jakarta Aktif --}}
        <a href="{{ route('picking.jakarta.aktif') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.5 8.5 12 4l8.5 4.5-8.5 4.5-8.5-4.5Z"/><path d="M3.5 8.5v7L12 20l8.5-4.5v-7"/><path d="M12 13v7"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">Jakarta Aktif</h3>
                <p class="text-gray-500 text-sm">Picking List Jakarta Aktif</p>
            </div>
        </a>

        {{-- Jakarta Pasif --}}
        <a href="{{ route('picking.jakarta.pasif') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.5 8.5 12 4l8.5 4.5-8.5 4.5-8.5-4.5Z"/><path d="M3.5 8.5v7L12 20l8.5-4.5v-7"/><path d="M12 13v7"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">Jakarta Pasif</h3>
                <p class="text-gray-500 text-sm">Picking List Jakarta Pasif</p>
            </div>
        </a>

        {{-- InterVio (DLC) --}}
        <a href="#" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.5 8.5 12 4l8.5 4.5-8.5 4.5-8.5-4.5Z"/><path d="M3.5 8.5v7L12 20l8.5-4.5v-7"/><path d="M12 13v7"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">InterVio (DLC)</h3>
            </div>
        </a>

        {{-- English biMBA Talk --}}
        <a href="#" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.5 8.5 12 4l8.5 4.5-8.5 4.5-8.5-4.5Z"/><path d="M3.5 8.5v7L12 20l8.5-4.5v-7"/><path d="M12 13v7"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">English biMBA Talk</h3>
            </div>
        </a>

        {{-- Order Manual --}}
        <a href="{{ route('picking.order-manual') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all">
                <svg class="w-10 h-10 mb-4 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 4h8a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/><path d="M9 8h6M9 12h6M9 16h4"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">Order Manual</h3>
            </div>
        </a>

    </div>

    <!-- Tombol Kembali -->
    <div class="mt-10 flex justify-center">
        <a href="{{ route('home') }}"
           class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-blue-600 text-gray-700 hover:text-blue-700 px-8 py-3 rounded-2xl font-medium transition-all">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Kembali ke Menu Utama
        </a>
    </div>
</div>
@endsection