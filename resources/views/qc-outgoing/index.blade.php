@extends('layouts.panel')

@section('title', 'QC Outgoing - biMBA Logistik')

@section('content')
<div class="p-8">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h2 class="text-3xl font-bold text-gray-800">QC Outgoing</h2>
            <p class="text-gray-500 mt-1">Quality Control Barang Keluar</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-5 gap-6">

        {{-- Jakarta Aktif --}}
        <a href="{{ route('qc-outgoing.jakarta-aktif') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">Jakarta Aktif</h3>
                <p class="text-gray-500 text-sm">QC Outgoing Jakarta Aktif</p>
            </div>
        </a>

        {{-- Jakarta Pasif --}}
        <a href="{{ route('qc-outgoing.jakarta-pasif') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">Jakarta Pasif</h3>
                <p class="text-gray-500 text-sm">QC Outgoing Jakarta Pasif</p>
            </div>
        </a>

        {{-- InterVio (DLC) --}}
        <a href="#" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">InterVio (DLC)</h3>
                <p class="text-gray-500 text-sm">QC Outgoing DLC</p>
            </div>
        </a>

        {{-- English biMBA Talk --}}
        <a href="#" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full">
                <svg class="w-10 h-10 mb-4 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3.5 19 6v6c0 4.5-3 7.5-7 8.5-4-1-7-4-7-8.5V6Z"/><path d="m9 12 2.2 2.2L15.5 10"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">English biMBA Talk</h3>
                <p class="text-gray-500 text-sm">QC Outgoing EBT</p>
            </div>
        </a>

        {{-- Order Manual --}}
        <a href="{{ route('qc-outgoing.order-manual') }}" class="group">
            <div class="bg-white rounded-3xl shadow p-8 hover:shadow-xl transition-all h-full border-2 border-indigo-100 hover:border-indigo-400">
                <svg class="w-10 h-10 mb-4 text-indigo-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 4h8a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H8a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z"/><path d="M9 8h6M9 12h6M9 16h4"/>
                </svg>
                <h3 class="text-2xl font-semibold mb-2">Order Manual</h3>
                <p class="text-gray-500 text-sm">QC Outgoing dari Picking Manual</p>
            </div>
        </a>

    </div>

    <div class="mt-12 flex justify-center">
        <a href="{{ route('home') }}"
           class="flex items-center justify-center gap-2 bg-white border border-gray-300 hover:border-blue-600 text-gray-700 hover:text-blue-700 px-8 py-3 rounded-2xl font-medium transition-all">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
            Kembali ke Menu Utama
        </a>
    </div>

</div>
@endsection