@extends('layouts.panel')

@section('title', 'Report Pengeluaran Logistik')

@section('content')
@include('partials.page-header', [
    'title'    => 'Report Pengeluaran Logistik',
    'subtitle' => 'Stokis yang order ke Logistik, isi ordernya per kategori, dan unit yang membeli ke stokis (di luar Kalender dan Majalah)',
    'primary'  => ['url' => route('pengeluaran-logistik.transfer.index'), 'label' => 'Transfer Gudang', 'icon' => 'plus'],
])

@include('partials.flash')

@php
    $inp = 'border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';
@endphp

<form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal order dari</label>
        <input type="date" name="dari" value="{{ $filter['dari'] }}" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Sampai</label>
        <input type="date" name="sampai" value="{{ $filter['sampai'] }}" class="{{ $inp }}">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Stokis</label>
        <select name="stokis" class="{{ $inp }}">
            <option value="">Semua stokis</option>
            @foreach($daftarStokis as $kode => $nama)
                <option value="{{ $kode }}" {{ $filter['stokis'] === $kode ? 'selected' : '' }}>{{ $nama }} ({{ $kode }})</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori produk</label>
        <select name="kategori" class="{{ $inp }}">
            <option value="">Semua kategori</option>
            @foreach($daftarKategori as $k)
                <option value="{{ $k }}" {{ $filter['kategori'] === $k ? 'selected' : '' }}>{{ $k }}</option>
            @endforeach
            <option value="Tanpa Kategori" {{ $filter['kategori'] === 'Tanpa Kategori' ? 'selected' : '' }}>Tanpa Kategori</option>
        </select>
    </div>
    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#162749] text-white text-sm font-semibold hover:bg-[#1D3361]">Terapkan</button>
    <a href="{{ route('pengeluaran-logistik.index') }}" class="px-3 py-2.5 text-sm text-gray-500 hover:text-gray-800">Reset</a>
</form>

@include('partials.stat-cards', ['stats' => [
    ['Logistik ke Stokis (qty)',  number_format($report['total_lg'])],
    ['Stokis ke Unit (qty)',      number_format($report['total_unit'])],
    ['Transfer Gudang (qty)',     number_format($report['total_transfer'])],
    ['Total Pengeluaran Logistik', number_format($report['total_keluar']), true],
    ['Jumlah Stokis',             number_format($report['jumlah_stokis'])],
]])

<div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-2.5 text-xs text-blue-800">
    Hanya order berstatus processing / completed. Total Pengeluaran Logistik = Logistik ke Stokis + Transfer Gudang.
    Stokis ke Unit adalah penjualan stokis, jadi tidak dijumlahkan ke pengeluaran Logistik.
    Selisih = qty masuk dari Logistik dikurangi qty terjual ke unit pada rentang tanggal yang sama.
    @if($dinaikkan > 0) {{ $dinaikkan }} order on-hold otomatis dinaikkan ke processing. @endif
</div>

@if(count($report['tidak_dikenal']) > 0)
    <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-900">
        <p class="font-semibold mb-1">{{ count($report['tidak_dikenal']) }} order LG belum dihitung karena stokisnya tidak terbaca dari nama billing</p>
        <ul class="list-disc ml-5 space-y-0.5">
            @foreach(array_slice($report['tidak_dikenal'], 0, 10) as $x)
                <li>#{{ $x['order_id'] }} - {{ $x['billing'] ?: '(billing kosong)' }} - {{ $x['sku'] }} - {{ number_format($x['qty']) }}</li>
            @endforeach
        </ul>
        @if(count($report['tidak_dikenal']) > 10)
            <p class="mt-1">dan {{ count($report['tidak_dikenal']) - 10 }} order lainnya.</p>
        @endif
    </div>
@endif

{{-- Ringkasan per kategori --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-5 overflow-hidden">
    <div class="px-5 py-3 border-b border-gray-100">
        <h3 class="font-semibold text-[#162749] text-sm">Ringkasan per kategori produk</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[#162749] text-white text-xs">
                <tr>
                    <th class="text-left px-4 py-2.5">Kategori</th>
                    <th class="text-right px-4 py-2.5">Logistik ke Stokis</th>
                    <th class="text-right px-4 py-2.5">Stokis ke Unit</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['kategori_semua'] as $kat => $v)
                    <tr class="border-t border-gray-100">
                        <td class="px-4 py-2">{{ $kat }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($v['lg'] ?? 0) }}</td>
                        <td class="px-4 py-2 text-right tabular-nums">{{ number_format($v['unit'] ?? 0) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Belum ada data pada filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Per stokis --}}
<h3 class="font-semibold text-[#162749] mb-2">Per stokis</h3>

@forelse(collect($report['stokis'])->groupBy('kelompok') as $kelompok => $daftar)
    <p class="text-[11px] font-bold uppercase tracking-wide text-gray-400 mt-4 mb-1.5">{{ $kelompok }}</p>

    @foreach($daftar as $s)
        <details class="bg-white rounded-2xl shadow-sm border border-gray-100 mb-2 group">
            <summary class="cursor-pointer list-none px-5 py-3 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <span class="font-semibold text-[#162749]">{{ $s['nama'] }}</span>
                    <span class="ml-1 text-xs text-gray-400">{{ $s['kode'] }}</span>
                </div>
                <div class="flex items-center gap-5 text-sm tabular-nums">
                    <span class="text-gray-500">Dari Logistik <b class="text-[#162749]">{{ number_format($s['qty_lg']) }}</b></span>
                    <span class="text-gray-500">Ke Unit <b class="text-[#162749]">{{ number_format($s['qty_unit']) }}</b></span>
                    <span class="{{ $s['selisih'] < 0 ? 'text-red-600' : 'text-gray-500' }}">Selisih <b>{{ number_format($s['selisih']) }}</b></span>
                </div>
            </summary>

            {{-- A. Logistik -> Stokis --}}
            <div class="border-t border-gray-100 p-5">
                <p class="text-sm font-semibold text-[#162749] mb-0.5">A. Order stokis ke Logistik</p>
                <p class="text-xs text-gray-400 mb-3">{{ $s['order_lg'] }} order, total {{ number_format($s['qty_lg']) }}. Klik kategori untuk melihat isinya.</p>

                @if(empty($s['kategori']))
                    <p class="text-xs text-gray-400">Tidak ada order LG pada filter ini.</p>
                @else
                    <div class="border border-gray-100 rounded-xl overflow-hidden">
                        @foreach($s['kategori'] as $kat => $k)
                            <details class="border-b border-gray-100 last:border-b-0">
                                <summary class="cursor-pointer list-none flex items-center justify-between px-3 py-2 bg-gray-50 hover:bg-gray-100 text-sm">
                                    <span class="font-semibold text-[#162749]">{{ $kat }} <span class="text-xs font-normal text-gray-400">({{ count($k['produk']) }} produk)</span></span>
                                    <span class="font-semibold tabular-nums">{{ number_format($k['qty']) }}</span>
                                </summary>
                                <table class="w-full text-xs">
                                    <tbody>
                                        @foreach($k['produk'] as $p)
                                            <tr class="border-t border-gray-50">
                                                <td class="px-3 py-1 pl-6 text-gray-600">{{ $p['nama'] }} <span class="text-gray-400">{{ $p['sku'] }}</span></td>
                                                <td class="px-3 py-1 text-right tabular-nums">{{ number_format($p['qty']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </details>
                        @endforeach
                        <div class="flex items-center justify-between px-3 py-2 bg-[#162749] text-white text-sm font-semibold">
                            <span>Total dari Logistik</span>
                            <span class="tabular-nums">{{ number_format($s['qty_lg']) }}</span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- B. Stokis -> Unit (matriks: satu kolom per kategori) --}}
            @php
                $kolomKat = collect($s['unit'])->flatMap(fn ($u) => array_keys($u['kategori']))->unique()->sort()->values();
            @endphp
            <div class="border-t border-gray-100 p-5">
                <p class="text-sm font-semibold text-[#162749] mb-0.5">B. Unit yang membeli ke stokis</p>
                <p class="text-xs text-gray-400 mb-3">{{ count($s['unit']) }} unit, total {{ number_format($s['qty_unit']) }}. Terurut dari qty terbesar.</p>

                @if(empty($s['unit']))
                    <p class="text-xs text-gray-400">Belum ada unit yang membeli pada filter ini.</p>
                @else
                    <div class="overflow-x-auto border border-gray-100 rounded-xl">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="text-left px-3 py-2 font-medium">Unit</th>
                                    <th class="text-left px-3 py-2 font-medium">Ref</th>
                                    @foreach($kolomKat as $kat)
                                        <th class="text-right px-3 py-2 font-medium">{{ $kat }}</th>
                                    @endforeach
                                    <th class="text-right px-3 py-2 font-semibold text-[#162749]">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($s['unit'] as $u)
                                    <tr class="border-t border-gray-100">
                                        <td class="px-3 py-1.5 text-[#162749] whitespace-nowrap">{{ $u['nama'] }}</td>
                                        <td class="px-3 py-1.5 text-gray-400">{{ $u['ref'] ?: '-' }}</td>
                                        @foreach($kolomKat as $kat)
                                            <td class="px-3 py-1.5 text-right tabular-nums {{ isset($u['kategori'][$kat]) ? '' : 'text-gray-300' }}">
                                                {{ isset($u['kategori'][$kat]) ? number_format($u['kategori'][$kat]) : '-' }}
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-1.5 text-right font-semibold tabular-nums">{{ number_format($u['qty']) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-[#162749] text-white font-semibold">
                                <tr>
                                    <td class="px-3 py-2" colspan="2">Total ke unit</td>
                                    @foreach($kolomKat as $kat)
                                        <td class="px-3 py-2 text-right tabular-nums">{{ number_format(collect($s['unit'])->sum(fn ($u) => $u['kategori'][$kat] ?? 0)) }}</td>
                                    @endforeach
                                    <td class="px-3 py-2 text-right tabular-nums">{{ number_format($s['qty_unit']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @endif
            </div>

            {{-- C. Selisih --}}
            <div class="border-t border-gray-100 px-5 py-3 bg-gray-50 rounded-b-2xl text-sm flex flex-wrap items-center justify-between gap-2">
                <span class="text-gray-500">Selisih = dari Logistik ({{ number_format($s['qty_lg']) }}) dikurangi ke unit ({{ number_format($s['qty_unit']) }})</span>
                <span class="font-bold tabular-nums {{ $s['selisih'] < 0 ? 'text-red-600' : 'text-[#162749]' }}">{{ number_format($s['selisih']) }}</span>
            </div>
        </details>
    @endforeach
@empty
    <div class="bg-white rounded-2xl border border-dashed border-gray-300 py-12 px-6 text-center text-sm text-gray-400">
        Belum ada order stokis pada filter ini.
    </div>
@endforelse

{{-- Transfer gudang --}}
<h3 class="font-semibold text-[#162749] mt-6 mb-2">Transfer gudang (tanpa nominal, hanya qty)</h3>
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-[#162749] text-white text-xs">
                <tr>
                    <th class="text-left px-4 py-2.5">Tujuan</th>
                    <th class="text-left px-4 py-2.5">Kategori</th>
                    <th class="text-right px-4 py-2.5">Qty</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report['transfer']['tujuan'] as $tujuan => $t)
                    <tr class="border-t border-gray-100 bg-gray-50">
                        <td class="px-4 py-2 font-semibold text-[#162749]" colspan="2">{{ $tujuan }}</td>
                        <td class="px-4 py-2 text-right font-semibold tabular-nums">{{ number_format($t['qty']) }}</td>
                    </tr>
                    @foreach($t['kategori'] as $kat => $qty)
                        <tr class="border-t border-gray-50">
                            <td></td>
                            <td class="px-4 py-1.5 text-gray-600">{{ $kat }}</td>
                            <td class="px-4 py-1.5 text-right tabular-nums">{{ number_format($qty) }}</td>
                        </tr>
                    @endforeach
                @empty
                    <tr><td colspan="3" class="px-4 py-8 text-center text-gray-400">Belum ada transfer gudang. <a class="text-[#E85D2A] underline" href="{{ route('pengeluaran-logistik.transfer.index') }}">Input transfer</a></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
