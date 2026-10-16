@extends('layouts.panel')

@section('title', 'Report Angka Cetak')

@section('content')
@include('partials.page-header', [
    'title'     => 'Report Angka Cetak Majalah',
    'subtitle'  => 'Ringkasan qty pemesanan berdasarkan edisi',
    'back'      => route('import.pasif.index'),
    'backLabel' => 'Kembali ke Unit Pasif',
])

{{-- Filter edisi --}}
<form method="GET" class="flex items-center gap-2 mb-4">
    <label for="edisi" class="text-sm font-medium text-gray-600">Edisi</label>
    <select name="edisi" id="edisi" onchange="this.form.submit()"
            class="border border-gray-300 rounded-xl px-3.5 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]">
        <option value="">Pilih Edisi</option>
        @foreach($edisiList as $edisi)
            <option value="{{ $edisi }}" {{ $selectedEdisi === $edisi ? 'selected' : '' }}>{{ $edisi }}</option>
        @endforeach
    </select>
</form>

@if(!$report)
    <div class="bg-white rounded-2xl border border-dashed border-gray-300 py-16 px-6 text-center text-sm text-gray-400">
        Pilih edisi terlebih dahulu untuk melihat report.
    </div>
@else
    @php
        $grup = [
            [
                'label'       => 'Unit Aktif',
                'rows'        => $report['rows_aktif'],
                'total'       => $report['total_aktif'],
                'total_label' => 'Total Pemesanan Unit Aktif',
                'hint'        => [],
            ],
            [
                'label'       => 'Unit Pasif',
                'rows'        => $report['rows_pasif'],
                'total'       => $report['total_pasif'],
                'total_label' => 'Total Pemesanan Unit Pasif',
                'hint'        => ['P1', 'P3'],   // kode yang diberi keterangan bila qty 0
            ],
        ];
    @endphp

    @include('partials.stat-cards', ['stats' => [
        ['Total Unit Aktif', number_format($report['total_aktif'])],
        ['Total Unit Pasif', number_format($report['total_pasif'])],
        ['Grand Total',      number_format($report['grand_total']), true],
        ['Edisi',            $report['edisi']],
    ]])

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 items-start">
        @foreach($grup as $g)
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-[#162749]">{{ $g['label'] }}</h3>
                    <span class="text-xs text-gray-500">Order Majalah Sahabat biMBA, Edisi {{ $report['edisi'] }}</span>
                </div>

                {{-- Daftar (scroll sendiri, header tetap) --}}
                <div class="overflow-auto" style="max-height: calc(100vh - 470px); min-height: 240px;">
                    <table class="w-full text-sm">
                        <thead>
                            <tr>
                                <th class="sticky top-0 z-10 bg-[#162749] text-white text-left font-semibold py-2.5 px-5 w-24">Kode</th>
                                <th class="sticky top-0 z-10 bg-[#162749] text-white text-left font-semibold py-2.5 px-5">Kategori Pemesanan</th>
                                <th class="sticky top-0 z-10 bg-[#162749] text-white text-right font-semibold py-2.5 px-5 w-28">Qty</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($g['rows'] as $row)
                                <tr class="hover:bg-gray-50">
                                    <td class="py-2 px-5 font-medium text-gray-700 whitespace-nowrap">{{ $row['kode'] }}</td>
                                    <td class="py-2 px-5 text-gray-700">
                                        {{ $row['label'] }}
                                        @if($row['qty'] == 0 && in_array($row['kode'], $g['hint']))
                                            <span class="text-xs text-gray-400">(belum ada data)</span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-5 text-right font-semibold {{ $row['qty'] > 0 ? 'text-gray-800' : 'text-gray-400' }}">
                                        {{ number_format($row['qty']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Subtotal (selalu terlihat) --}}
                <div class="flex items-center justify-between px-5 py-3 bg-gray-50 border-t border-gray-100 font-semibold text-sm">
                    <span class="text-gray-800">{{ $g['total_label'] }}</span>
                    <span class="text-[#28447F]">{{ number_format($g['total']) }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Grand total --}}
    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 bg-[#162749] text-white rounded-2xl px-5 py-4">
        <span class="font-bold">GRAND TOTAL PEMESANAN</span>
        <span class="text-xl font-bold">{{ number_format($report['grand_total']) }}</span>
    </div>
    <div class="mt-2 flex flex-wrap items-center justify-between gap-3 bg-[#E85D2A]/10 rounded-2xl px-5 py-3 font-semibold">
        <span class="text-[#162749]">ORDER CETAK MAJALAH</span>
        <span class="text-[#D14E1F]">{{ number_format($report['grand_total']) }}</span>
    </div>
@endif
@endsection