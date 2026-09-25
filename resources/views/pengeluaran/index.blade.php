@extends('layouts.panel')

@section('title', 'Pengeluaran')

@push('styles')
<style>
    .nominal { font-variant-numeric: tabular-nums; }
    .data-table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 0.8125rem; }
    .data-table th, .data-table td { padding: 10px 12px; text-align: left; vertical-align: middle; border-bottom: 1px solid rgba(15, 27, 51, 0.06); }
    .data-table thead th { background: #F4F6FA; color: rgba(15, 27, 51, 0.6); font-weight: 600; font-size: 0.75rem; border-bottom: 1px solid rgba(15, 27, 51, 0.1); }
    .data-table tbody tr:hover td { background-color: #F7F9FC; }
    .data-table .ctr { text-align: center; }
    .data-table .rgt { text-align: right; }
</style>
@endpush

@section('content')

    @php
        $inp = 'w-full bg-white border border-navy-950/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
        $lbl = 'block text-sm text-navy-950/60 mb-1.5';

        $rekapBlocks = [
            ['id' => 'ra-aktif', 'title' => 'Rekap RA Aktif', 'subtitle' => 'realisasi_aktif — sudah diproses gudang (unit Aktif / biMBA Shop)', 'rows' => $aktifRekap],
            ['id' => 'ra-pasif', 'title' => 'Rekap RA Pasif', 'subtitle' => 'realisasi_pasif — sudah diproses gudang (unit Pasif / Majalah)', 'rows' => $pasifRekap],
            ['id' => 'rekap-manual', 'title' => 'Rekap Manual', 'subtitle' => 'manual_realisasi — order manual (Modul, Sertifikat, dll)', 'rows' => $manualRekap],
        ];
    @endphp

    {{-- ============ JUDUL ============ --}}
    @include('partials.page-header', [
        'title'    => 'Pengeluaran',
        'subtitle' => 'Rekap gabungan RA (Rekap Aktual): Aktif, Pasif & Manual',
    ])

    {{-- ============ FILTER PERIODE ============ --}}
    <div class="bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <form method="GET" class="flex flex-wrap items-end gap-4">
            <div>
                <label for="start_date" class="{{ $lbl }}">Dari Tanggal</label>
                <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="{{ $inp }}">
            </div>
            <div>
                <label for="end_date" class="{{ $lbl }}">Sampai Tanggal</label>
                <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="{{ $inp }}">
            </div>
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                Terapkan
            </button>
        </form>
    </div>

    {{-- ============ KARTU RINGKASAN ============ --}}
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-2xl shadow-card p-5">
            <p class="text-xs font-semibold text-navy-950/50 uppercase tracking-wide">RA Aktif</p>
            <p class="mt-2 text-2xl font-bold text-navy-950 nominal">Rp {{ number_format($aktifTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-navy-950/45 mt-1">{{ $aktifOrderCount }} order &middot; {{ $aktifRekapCount }} rekap</p>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-5">
            <p class="text-xs font-semibold text-navy-950/50 uppercase tracking-wide">RA Pasif</p>
            <p class="mt-2 text-2xl font-bold text-navy-950 nominal">Rp {{ number_format($pasifTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-navy-950/45 mt-1">{{ $pasifOrderCount }} order &middot; {{ $pasifRekapCount }} rekap</p>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-5">
            <p class="text-xs font-semibold text-navy-950/50 uppercase tracking-wide">Manual</p>
            <p class="mt-2 text-2xl font-bold text-navy-950 nominal">Rp {{ number_format($manualTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-navy-950/45 mt-1">{{ $manualOrderCount }} order &middot; {{ $manualRekapCount }} rekap</p>
        </div>

        <div class="bg-navy-800 rounded-2xl shadow-card p-5">
            <p class="text-xs font-semibold text-white/60 uppercase tracking-wide">Total Pengeluaran</p>
            <p class="mt-2 text-2xl font-bold text-white nominal">Rp {{ number_format($grandTotal, 0, ',', '.') }}</p>
            <p class="text-xs text-white/60 mt-1">{{ $startDate->format('d M Y') }} &ndash; {{ $endDate->format('d M Y') }}</p>
        </div>

    </div>

    {{-- ============ TABEL REKAP PER SUMBER ============ --}}
    @foreach ($rekapBlocks as $block)
        <div id="{{ $block['id'] }}" class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden scroll-mt-24">
            <div class="px-5 sm:px-6 py-4 border-b border-navy-950/10">
                <h2 class="text-base font-semibold text-navy-950">{{ $block['title'] }}</h2>
                <p class="text-xs text-navy-950/45">{{ $block['subtitle'] }}</p>
            </div>
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No. Rekap</th>
                            <th>Tanggal Bayar</th>
                            <th class="ctr">Jumlah Order</th>
                            <th class="rgt">Total Bayar</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($block['rows'] as $rekap)
                            <tr>
                                <td class="font-medium text-navy-950">{{ $rekap->rekap_number }}</td>
                                <td>{{ $rekap->tgl_bayar ? \Carbon\Carbon::parse($rekap->tgl_bayar)->format('d M Y') : '-' }}</td>
                                <td class="ctr">{{ $rekap->jumlah_order }}</td>
                                <td class="rgt nominal">Rp {{ number_format($rekap->total_bayar, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-navy-950/35 py-6">
                                    Belum ada rekap pada periode ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endforeach

@endsection