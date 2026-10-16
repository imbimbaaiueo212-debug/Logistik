@extends('layouts.panel')

@section('title', 'Data DLC')

@push('styles')
<style>
    .data-table-wrap { overflow: auto; max-height: calc(100vh - 250px); }
    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .data-table thead th {
        position: sticky; top: 0; z-index: 20;
        background: #162749; color: #fff; text-align: left;
        padding: 12px 16px; font-weight: 600; white-space: nowrap;
    }
    .data-table tbody td, .data-table tfoot td {
        padding: 11px 16px; border-bottom: 1px solid #eef0f5;
        background: #fff; white-space: nowrap;
    }
    .data-table tbody tr:hover td { background: #f7f8fb; }
    .data-table tfoot td { background: #f7f8fb; font-weight: 700; }
    .data-table th:first-child, .data-table td:first-child { position: sticky; left: 0; z-index: 10; }
    .data-table thead th:first-child { z-index: 30; }
    .data-table tfoot td:first-child { position: static; }
    .data-table th.col-aksi, .data-table td.col-aksi { position: sticky; right: 0; z-index: 10; }
    .data-table thead th.col-aksi { z-index: 30; }
    .cell-clip { display: block; max-width: 260px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pager nav p { display: none; }
</style>
@endpush

@section('content')
@php
    $kolom = [
        ['key' => 'edisi',           'label' => 'Edisi',       'class' => 'font-semibold text-[#162749]'],
        ['key' => 'judul',           'label' => 'Judul',       'clip' => true],
        ['key' => 'periode',         'label' => 'Periode'],
        ['key' => 'no_ps',           'label' => 'No PS'],
        ['key' => 'pesanan_count',   'label' => 'Jumlah Unit', 'align' => 'center', 'type' => 'number'],
        ['key' => 'pesanan_sum_qty', 'label' => 'Total Qty',   'align' => 'center', 'type' => 'number', 'class' => 'font-semibold'],
        ['key' => 'status',          'label' => 'Status',      'align' => 'center', 'type' => 'badge'],
    ];
@endphp

<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <h2 class="text-2xl font-bold text-[#162749]">Data Pemesanan Majalah DLC</h2>
    <div class="flex items-center gap-2">
        <a href="{{ route('order-manual.index') }}"
           class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Kembali
        </a>
        <a href="{{ route('import.dlc.create') }}"
           class="inline-flex items-center gap-2 bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Tambah Data DLC
        </a>
    </div>
</div>

@if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-xl mb-4">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-xl mb-4">{{ session('error') }}</div>
@endif

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    @foreach($kolom as $k)
                        <th style="text-align: {{ $k['align'] ?? 'left' }}">{{ $k['label'] }}</th>
                    @endforeach
                    <th class="col-aksi" style="text-align: center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($periodes as $periode)
                    <tr>
                        @foreach($kolom as $k)
                            @php $v = data_get($periode, $k['key']); @endphp
                            <td class="{{ $k['class'] ?? '' }}" style="text-align: {{ $k['align'] ?? 'left' }}">
                                @if(($k['type'] ?? null) === 'badge')
                                    <span class="px-2.5 py-1 text-xs rounded-full font-medium {{ $v === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($v) }}
                                    </span>
                                @elseif(($k['type'] ?? null) === 'number')
                                    {{ number_format((int) $v) }}
                                @elseif(!empty($k['clip']))
                                    <span class="cell-clip" title="{{ $v }}">{{ filled($v) ? $v : '-' }}</span>
                                @else
                                    {{ filled($v) ? $v : '-' }}
                                @endif
                            </td>
                        @endforeach

                        <td class="col-aksi" style="text-align: center">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('import.dlc.show', $periode->id) }}" title="Detail" aria-label="Detail"
                                   class="p-1.5 rounded-lg text-[#28447F] hover:bg-[#28447F]/10 transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                                </a>
                                <a href="{{ route('import.dlc.edit', $periode->id) }}" title="Edit" aria-label="Edit"
                                   class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 transition">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg>
                                </a>
                                <form action="{{ route('import.dlc.destroy', $periode->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" aria-label="Hapus"
                                            class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M9 7V4h6v3"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($kolom) + 1 }}" style="text-align: center" class="!py-14 text-gray-500">
                            Belum ada data DLC.
                            <a href="{{ route('import.dlc.create') }}" class="text-[#E85D2A] hover:underline ml-1">Tambah sekarang</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($periodes->hasPages())
    <div class="pager mt-4">
        {{ $periodes->links('pagination::tailwind') }}
    </div>
@endif
@endsection