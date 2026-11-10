@extends('layouts.panel')

@section('title', 'Report Angka Cetak')

@section('content')
@include('partials.page-header', [
    'title'     => 'Report Angka Cetak Kalender',
    'subtitle'  => 'Proses pesanan kalender per edisi, susunan sama dengan Excel KLD',
    'back'      => route('kalender.pasif.index'),
    'backLabel' => 'Kembali ke Unit Pasif',
])

@php
    $inp = 'border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';
@endphp

{{-- Filter edisi dan dua periode tanggal order --}}
<form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
    <div>
        <label for="edisi" class="block text-xs font-medium text-gray-600 mb-1">Edisi</label>
        <select name="edisi" id="edisi" class="{{ $inp }}">
            <option value="">Pilih Edisi</option>
            @foreach($edisiList as $edisi)
                <option value="{{ $edisi }}" {{ $selectedEdisi === $edisi ? 'selected' : '' }}>{{ $edisi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <span class="block text-xs font-medium text-gray-600 mb-1">Periode 1 (tgl order dari - sampai)</span>
        <div class="flex items-center gap-2">
            <input type="date" name="p1_dari" value="{{ $filter['p1_dari'] }}" class="{{ $inp }}">
            <span class="text-gray-400">-</span>
            <input type="date" name="p1_sampai" value="{{ $filter['p1_sampai'] }}" class="{{ $inp }}">
        </div>
    </div>

    <div>
        <span class="block text-xs font-medium text-gray-600 mb-1">Periode 2 (tgl order dari - sampai)</span>
        <div class="flex items-center gap-2">
            <input type="date" name="p2_dari" value="{{ $filter['p2_dari'] }}" class="{{ $inp }}">
            <span class="text-gray-400">-</span>
            <input type="date" name="p2_sampai" value="{{ $filter['p2_sampai'] }}" class="{{ $inp }}">
        </div>
    </div>

    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#162749] text-white text-sm font-semibold hover:bg-[#1D3361]">Terapkan</button>
</form>

@if(!$report)
    <div class="bg-white rounded-2xl border border-dashed border-gray-300 py-16 px-6 text-center text-sm text-gray-400">
        Pilih edisi terlebih dahulu untuk melihat report.
    </div>
@else
    @include('partials.stat-cards', ['stats' => [
        ['Total A (Unit Aktif)', number_format($report['total_aktif'])],
        ['Total P (Unit Pasif)', number_format($report['total_pasif'])],
        ['Grand Total',          number_format($report['grand_total']), true],
        ['Edisi',                $report['edisi']],
    ]])

    @if($filter['semua_tanggal'])
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-800">
            Rentang tanggal periode untuk edisi {{ $report['edisi'] }} belum diisi. Isi tanggal Periode 1 dan Periode 2 di atas, lalu klik Terapkan.
            Sementara seluruh order berstatus processing dimasukkan ke kolom pertama.
        </div>
    @endif

    @if(($report['dinaikkan'] ?? 0) > 0)
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs text-emerald-800">
            {{ number_format($report['dinaikkan']) }} baris order biMBA Shop yang masih on-hold diubah menjadi processing karena sudah SETTLED / SUCCESS di Casdana.
        </div>
    @endif

    @if(($report['manual_menunggu'] ?? 0) > 0)
        <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-2.5 text-xs text-amber-800">
            {{ number_format($report['manual_menunggu']) }} qty Create Manual (unit aktif) masih on-hold, belum ada di biMBA Shop dan Kasdana, sehingga belum dihitung.
            Setelah lengkap, angkanya terhitung lewat order biMBA Shop (bukan SOUV).
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="text-sm font-bold uppercase tracking-wide text-[#162749]">Proses Pesanan Kalender biMBA ({{ $report['edisi'] }})</h3>
            <p class="mt-1 text-xs text-gray-500 leading-relaxed">
                Kode A: qty order biMBA Shop berstatus processing per kode stokis di SKU, dipecah menurut tanggal order di dalam rentang periode,
                sama seperti rumus SUMIFS di Excel. Order on-hold yang sudah SETTLED / SUCCESS di Casdana otomatis menjadi processing.
                SOUV: PUW dan Unit Pasif. Order Shop = order biMBA Shop yang sudah dibayar (on-hold tidak dihitung); Selisih = Order Shop dikurangi kolom periode
                (tanggal order di luar periode).
            </p>
        </div>

        <div class="overflow-auto" style="max-height: calc(100vh - 330px); min-height: 280px;">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-[#162749] text-white">
                        <th class="sticky top-0 z-10 bg-[#162749] text-left font-semibold py-2.5 px-4 w-20">Kode</th>
                        <th class="sticky top-0 z-10 bg-[#162749] text-left font-semibold py-2.5 px-4">Kategori Pemesanan</th>
                        <th class="sticky top-0 z-10 bg-[#162749] text-right font-semibold py-2.5 px-4 whitespace-nowrap">{{ $filter['semua_tanggal'] ? 'Semua tanggal' : 'Periode 1' }}</th>
                        <th class="sticky top-0 z-10 bg-[#162749] text-right font-semibold py-2.5 px-4 whitespace-nowrap">Periode 2</th>
                        <th class="sticky top-0 z-10 bg-[#162749] text-right font-semibold py-2.5 px-4">SOUV</th>
                        <th class="sticky top-0 z-10 bg-[#162749] text-right font-semibold py-2.5 px-4 border-l border-white/20">TOTAL</th>
                        <th class="sticky top-0 z-10 bg-[#162749] text-right font-semibold py-2.5 px-4 whitespace-nowrap">Order Shop</th>
                        <th class="sticky top-0 z-10 bg-[#162749] text-right font-semibold py-2.5 px-4">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach([['rows' => $report['rows_aktif'], 'judul' => null], ['rows' => $report['rows_pasif'], 'judul' => 'Unit Pasif (P) - SOUV']] as $blok)
                        @if($blok['judul'])
                            <tr>
                                <td colspan="8" class="py-2 px-4 text-xs font-semibold uppercase tracking-wide text-white bg-[#28447F]">{{ $blok['judul'] }}</td>
                            </tr>
                        @endif

                        @foreach($blok['rows'] as $row)
                            @php
                                $shop    = $row['shop'];
                                $selisih = $shop === null ? null : $shop - ($row['p1'] + $row['p2']);
                                $nol     = fn ($v) => $v == 0 ? 'text-gray-300' : 'text-gray-700';
                            @endphp
                            <tr class="{{ $row['subtotal'] ? 'bg-[#EADFC4] font-semibold' : 'hover:bg-gray-50' }}">
                                <td class="py-2 px-4 font-medium text-gray-700 whitespace-nowrap">{{ $row['kode'] }}</td>
                                <td class="py-2 px-4 text-gray-700 {{ $row['level'] ? 'pl-9' : '' }}">
                                    {{ $row['label'] }}
                                    @if($row['catatan'])
                                        <span class="text-xs text-gray-400">({{ $row['catatan'] }})</span>
                                    @endif
                                </td>
                                <td class="py-2 px-4 text-right {{ $nol($row['p1']) }}">{{ number_format($row['p1']) }}</td>
                                <td class="py-2 px-4 text-right {{ $nol($row['p2']) }}">{{ number_format($row['p2']) }}</td>
                                <td class="py-2 px-4 text-right {{ $nol($row['souv']) }}">{{ number_format($row['souv']) }}</td>
                                <td class="py-2 px-4 text-right font-semibold border-l border-gray-100 {{ $row['total'] > 0 ? 'text-gray-900' : 'text-gray-300' }}">{{ number_format($row['total']) }}</td>
                                <td class="py-2 px-4 text-right {{ $shop === null ? '' : $nol($shop) }}">{{ $shop === null ? '' : number_format($shop) }}</td>
                                <td class="py-2 px-4 text-right font-semibold {{ $selisih === null || $selisih === 0 ? 'text-gray-300' : 'text-[#D14E1F]' }}">
                                    {{ $selisih === null ? '' : number_format($selisih) }}
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap items-center justify-between gap-2 px-5 py-3 bg-gray-50 border-t border-gray-100 text-sm">
            <span class="font-semibold text-gray-800">Grand Total Pemesanan</span>
            <span class="font-bold text-[#162749]">{{ number_format($report['grand_total']) }}</span>
        </div>
    </div>
@endif
@endsection
