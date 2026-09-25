@extends('layouts.panel')

@section('title', 'Manual Pemesanan')

@push('styles')
<style>
    .data-table-wrap { overflow: auto; max-height: calc(100vh - 120px); }
    .data-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .data-table thead th {
        position: sticky; top: 0; z-index: 20;
        background: #162749; color: #fff; text-align: left;
        padding: 12px 14px; font-weight: 600; white-space: nowrap;
    }
    .data-table tbody td {
        padding: 10px 14px; border-bottom: 1px solid #eef0f5;
        background: #fff; white-space: nowrap; vertical-align: middle;
    }
    .data-table tbody tr:hover td { background: #f7f8fb; }
    .data-table tbody tr.is-processed td { background: #f1f5f9; color: #64748b; }
    .data-table th:first-child, .data-table td:first-child { position: sticky; left: 0; z-index: 10; }
    .data-table thead th:first-child { z-index: 30; }
    .data-table th.col-aksi, .data-table td.col-aksi { position: sticky; right: 0; z-index: 10; }
    .data-table thead th.col-aksi { z-index: 30; }
    .cell-clip { display: block; max-width: 240px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pager nav p { display: none; }
</style>
@endpush

@section('content')
@php
    $ctl = 'w-full border border-gray-300 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';

    $kolom = [
        ['key' => 'order_id',        'label' => 'ID Pesan'],
        ['key' => 'nama_unit',       'label' => 'Nama Unit'],
        ['key' => 'cabang',          'label' => 'Cabang'],
        ['key' => 'grup',            'label' => 'Group'],
        ['key' => 'alamat',          'label' => 'Alamat Kirim'],
        ['key' => 'kota',            'label' => 'Kab/Kota'],
        ['key' => 'kategori',        'label' => 'Kategori Pesanan'],
        ['key' => 'qty',             'label' => 'Qty', 'align' => 'center'],
        ['key' => 'order_date',      'label' => 'Order Date'],
        ['key' => 'payment_date',    'label' => 'Payment Date'],
        ['key' => 'est_print',       'label' => 'Estimasi Print PL | PS', 'align' => 'center'],
        ['key' => 'est_persiapan',   'label' => 'Estimasi Persiapan', 'align' => 'center'],
        ['key' => 'ekspedisi',       'label' => 'Jasa Kurir'],
        ['key' => 'service',         'label' => 'Service Kurir'],
        ['key' => 'distribusi',      'label' => 'Distribusi'],
        ['key' => 'ship_total',      'label' => 'Ship Total', 'align' => 'right'],
        ['key' => 'berat',           'label' => 'Berat (gr)', 'align' => 'right'],
        ['key' => 'total',           'label' => 'Order Total', 'align' => 'right'],
        ['key' => 'payment_method',  'label' => 'Payment Channel'],
        ['key' => 'status_bayar',    'label' => 'Status Bayar'],
        ['key' => 'status',          'label' => 'Status'],
        ['key' => 'tgl_proses',      'label' => 'Tanggal Proses', 'align' => 'center'],
    ];

    $grupOpt = collect($grups)->mapWithKeys(function ($g) { return [$g => $g]; })->all();

    $filter = [
        ['name' => 'order_id',       'label' => 'Order ID',  'type' => 'text', 'ph' => 'Cari Order ID...'],
        ['name' => 'grup',           'label' => 'Grup',      'type' => 'select', 'options' => $grupOpt, 'empty' => 'Semua Grup'],
        ['name' => 'customer_name',  'label' => 'Customer',  'type' => 'text', 'ph' => 'Nama Customer...'],
        ['name' => 'product_name',   'label' => 'Item Name', 'type' => 'text', 'ph' => 'Nama Produk...'],
        ['name' => 'product_sku',    'label' => 'SKU',       'type' => 'text', 'ph' => 'SKU...'],
        ['name' => 'payment_method', 'label' => 'Payment Method', 'type' => 'select',
            'options' => ['cash' => 'Cash', 'transfer' => 'Transfer', 'manual' => 'Manual'], 'empty' => 'Semua'],
        ['name' => 'status',         'label' => 'Status',    'type' => 'select',
            'options' => ['pending' => 'Pending', 'processing' => 'Processing', 'completed' => 'Completed'], 'empty' => 'Semua Status'],
        ['name' => 'start_date',     'label' => 'Dari Tanggal',   'type' => 'date'],
        ['name' => 'end_date',       'label' => 'Sampai Tanggal', 'type' => 'date'],
        ['name' => 'per_page',       'label' => 'Tampilkan', 'type' => 'select',
            'options' => [10 => '10', 25 => '25', 50 => '50', 100 => '100'], 'default' => 25, 'submit' => true],
    ];

    $grupColors = [
        'A' => 'bg-blue-100 text-blue-700 border border-blue-200',
        'B' => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
        'C' => 'bg-purple-100 text-purple-700 border border-purple-200',
        'D' => 'bg-amber-100 text-amber-700 border border-amber-200',
        'E' => 'bg-rose-100 text-rose-700 border border-rose-200',
        'F' => 'bg-cyan-100 text-cyan-700 border border-cyan-200',
    ];
    $grupDefault = 'bg-gray-100 text-gray-600 border border-gray-200';

    $rp = function ($n) { return 'Rp ' . number_format($n ?? 0, 0, ',', '.'); };
    $estClass = function ($jam, $ok, $warn) {
        return $jam <= $ok ? 'bg-emerald-100 text-emerald-800'
             : ($jam <= $warn ? 'bg-red-100 text-red-700' : 'bg-gray-800 text-gray-100');
    };
@endphp

{{-- Header --}}
<div class="flex flex-wrap items-start justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-[#162749]">Data Realisasi Majalah</h2>
        <p class="text-sm text-gray-500 mt-0.5">Kelola data pemesanan manual</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ route('order-manual.index') }}"
           class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
            Kembali ke Daftar Import
        </a>
        <a href="{{ route('import.manual-printed') }}"
           class="inline-flex items-center gap-2 bg-[#162749] hover:bg-[#1D3361] text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 20V10M12 20V4M19 20v-7"/></svg>
            Rekap Aktual Manual
        </a>
        <form action="{{ route('import.sync-pesanan-majalah') }}" method="POST"
              onsubmit="return confirm('Yakin Sync semua Pesanan Majalah ke Manual Pemesanan?')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11a8 8 0 0 0-14.5-4.5L4 8"/><path d="M4 4v4h4"/><path d="M4 13a8 8 0 0 0 14.5 4.5L20 16"/><path d="M20 20v-4h-4"/></svg>
                Sync Pesanan Majalah
            </button>
        </form>
    </div>
</div>

@include('partials.flash')

{{-- Bar proses massal (muncul bila filter tanggal terisi) --}}
<div id="bulkActionBar" class="hidden mb-4">
    <div class="flex flex-wrap items-center justify-between gap-3 bg-white rounded-2xl shadow-sm border border-[#28447F]/20 px-5 py-4">
        <span id="selectedCount" class="text-sm font-medium text-gray-700">Siap memproses data sesuai filter tanggal</span>
        <div class="flex items-center gap-2">
            <button type="button" id="processAllBtn"
                    class="inline-flex items-center gap-2 bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3.5" y="5" width="17" height="15" rx="2"/><path d="M3.5 10h17M8 3v4M16 3v4"/></svg>
                Proses & Edit Semua Sesuai Filter Tanggal
            </button>
            <button type="button" id="btnReset"
                    class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-gray-50 transition">Reset</button>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-4">
    <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-6 gap-3">
        @foreach($filter as $f)
            @php $val = request($f['name'], $f['default'] ?? ''); @endphp
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">{{ $f['label'] }}</label>
                @if($f['type'] === 'select')
                    <select name="{{ $f['name'] }}" class="{{ $ctl }}" {!! !empty($f['submit']) ? 'onchange="this.form.submit()"' : '' !!}>
                        @if(array_key_exists('empty', $f))
                            <option value="">{{ $f['empty'] }}</option>
                        @endif
                        @foreach($f['options'] as $ov => $ol)
                            <option value="{{ $ov }}" {{ (string) $val === (string) $ov ? 'selected' : '' }}>{{ $ol }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="{{ $f['type'] }}" name="{{ $f['name'] }}" value="{{ $val }}"
                           placeholder="{{ $f['ph'] ?? '' }}" class="{{ $ctl }}">
                @endif
            </div>
        @endforeach

        <div class="col-span-2 flex items-end gap-2">
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 flex-1 bg-[#162749] hover:bg-[#1D3361] text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="6"/><path d="m20 20-4-4"/></svg>
                Terapkan Filter
            </button>
            <a href="{{ route('import.manual') }}" class="px-3 py-2 text-sm font-medium text-gray-500 hover:text-red-600 whitespace-nowrap">Reset</a>
        </div>
    </form>
</div>

{{-- Tabel --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="data-table-wrap">
        <table class="data-table" id="dataTable">
            <thead>
                <tr>
                    @foreach($kolom as $k)
                        <th style="text-align: {{ $k['align'] ?? 'left' }}">{{ $k['label'] }}</th>
                    @endforeach
                    <th class="col-aksi" style="text-align: center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manualOrders as $order)
                    @php
                        $isProcessed = (bool) ($order->is_processed ?? false);

                        $paymentDate       = $order->payment_date ? \Carbon\Carbon::parse($order->payment_date) : null;
                        $estimasiPrint     = $order->estimasi_print_pl ? \Carbon\Carbon::parse($order->estimasi_print_pl) : null;
                        $estimasiPersiapan = $order->estimasi_persiapan ? \Carbon\Carbon::parse($order->estimasi_persiapan) : null;
                        $jamPrint      = $estimasiPrint ? now()->diffInHours($estimasiPrint, false) : 999;
                        $jamPersiapan  = $estimasiPersiapan ? now()->diffInHours($estimasiPersiapan, false) : 999;

                        $status = strtolower($order->status ?? 'pending');
                        $statusClass = match($status) {
                            'completed'  => 'bg-emerald-100 text-emerald-700',
                            'processing' => 'bg-blue-100 text-blue-700',
                            default      => 'bg-amber-100 text-amber-800',
                        };

                        $namaUnit = $order->customer_name
                            ?? trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? ''))
                            ?: '-';
                        $kategori = $order->product_name ?? $order->item_name ?? $order->product_sku ?? '-';

                        $noCab      = trim($order->billing_last_name ?? '');
                        $mismatch   = $mismatchMap[$noCab] ?? null;
                        $isMismatch = $mismatch
                            || str_contains($order->catatan ?? '', 'NAMA_MISMATCH')
                            || str_contains($order->notes ?? '', 'NAMA_MISMATCH');

                        $grup      = strtoupper(trim($order->grup ?? ''));
                        $grupClass = $grupColors[$grup] ?? $grupDefault;
                    @endphp
                    <tr data-row="1" data-processed="{{ $isProcessed ? 1 : 0 }}" class="{{ $isProcessed ? 'is-processed' : '' }}">
                        @foreach($kolom as $k)
                            @php $key = $k['key']; @endphp
                            <td style="text-align: {{ $k['align'] ?? 'left' }}">
                                @if($key === 'order_id')
                                    <span class="font-semibold text-[#28447F]">{{ $order->order_id ?? '-' }}</span>

                                @elseif($key === 'nama_unit')
                                    <span class="cell-clip font-semibold text-[#162749]" title="{{ $namaUnit }}">{{ $namaUnit }}</span>
                                    @if($isMismatch)
                                        @if($mismatch)
                                            <span class="cell-clip text-xs text-orange-700" title="{{ $mismatch['nama_excel'] }}">Excel: {{ $mismatch['nama_excel'] }}</span>
                                            <span class="cell-clip text-xs text-emerald-700" title="{{ $mismatch['nama_master'] }}">Kemitraan: {{ $mismatch['nama_master'] }}</span>
                                        @endif
                                        <span class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200"
                                              title="Nama unit beda dengan Unit Kemitraan">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 4 3 20h18Z"/><path d="M12 10v4M12 17h.01"/></svg>
                                            Mismatch
                                        </span>
                                    @endif

                                @elseif($key === 'cabang')
                                    {{ $order->billing_last_name ?? '-' }}

                                @elseif($key === 'grup')
                                    @if($grup)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $grupClass }}">Group {{ $grup }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif

                                @elseif($key === 'alamat')
                                    <span class="cell-clip" title="{{ $order->shipping_address_1 ?? '' }}">{{ $order->shipping_address_1 ?? '-' }}</span>

                                @elseif($key === 'kota')
                                    {{ $order->shipping_city ?? '-' }}

                                @elseif($key === 'kategori')
                                    <span class="cell-clip" title="{{ $kategori }}">{{ $kategori }}</span>

                                @elseif($key === 'qty')
                                    <span class="font-semibold">{{ $order->qty ?? $order->item_qty ?? 0 }}</span>

                                @elseif($key === 'order_date')
                                    {{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') : '-' }}

                                @elseif($key === 'payment_date')
                                    @if($paymentDate)
                                        {{ $paymentDate->format('d/m/Y') }}
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Pending</span>
                                    @endif

                                @elseif($key === 'est_print')
                                    @if($estimasiPrint)
                                        <span class="inline-block px-3 py-1 rounded-xl text-xs font-semibold {{ $estClass($jamPrint, 24, 48) }}">{{ $estimasiPrint->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif

                                @elseif($key === 'est_persiapan')
                                    @if($estimasiPersiapan)
                                        <span class="inline-block px-3 py-1 rounded-xl text-xs font-semibold {{ $estClass($jamPersiapan, 72, 96) }}">{{ $estimasiPersiapan->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif

                                @elseif($key === 'ekspedisi')
                                    {{ $order->ekspedisi ?? '-' }}

                                @elseif($key === 'service')
                                    {{ $order->service_pengiriman ?? '-' }}

                                @elseif($key === 'distribusi')
                                    @if(($order->status_kirim ?? '') === 'Diambil')
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-amber-100 text-amber-700">Diambil</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-700">Dikirim</span>
                                    @endif

                                @elseif($key === 'ship_total')
                                    {{ $rp($order->ship_total) }}

                                @elseif($key === 'berat')
                                    {{ number_format($order->order_weight ?? 0, 0, ',', '.') }} gr

                                @elseif($key === 'total')
                                    <span class="font-semibold">{{ $rp($order->total ?? $order->order_total ?? 0) }}</span>

                                @elseif($key === 'payment_method')
                                    {{ $order->payment_method ?? '-' }}

                                @elseif($key === 'status_bayar')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">MANUAL</span>

                                @elseif($key === 'status')
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ ucfirst($order->status ?? 'Pending') }}</span>

                                @elseif($key === 'tgl_proses')
                                    @if($isProcessed && $order->processed_at)
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-100 text-emerald-700">{{ \Carbon\Carbon::parse($order->processed_at)->format('d/m/Y H:i') }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                @endif
                            </td>
                        @endforeach

                        <td class="col-aksi" style="text-align: center">
                            @if(!$isProcessed)
                                <div class="inline-flex items-center gap-1">
                                    <a href="{{ route('import.manual.edit', $order->id) }}" title="Edit" aria-label="Edit"
                                       class="p-1.5 rounded-lg text-amber-600 hover:bg-amber-50 transition">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/></svg>
                                    </a>
                                    <form action="{{ route('import.manual.destroy', $order->id) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" aria-label="Hapus"
                                                class="p-1.5 rounded-lg text-red-600 hover:bg-red-50 transition">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16M10 11v6M14 11v6"/><path d="M6 7l1 12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-12"/><path d="M9 7V4h6v3"/></svg>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-emerald-600 text-xs font-medium">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4.5 4.5L19 7"/></svg>
                                    Diproses
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($kolom) + 1 }}" style="text-align: center" class="!py-14 text-gray-400">
                            Belum ada data pemesanan. Import dari Daftar Import atau klik Sync Pesanan Majalah.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($manualOrders->total() > 0)
    <div class="flex flex-wrap items-center justify-between gap-3 mt-4">
        <p class="text-sm text-gray-500">
            Menampilkan {{ $manualOrders->firstItem() }} sampai {{ $manualOrders->lastItem() }}
            dari {{ number_format($manualOrders->total()) }} data
        </p>
        @if($manualOrders->hasPages())
            <div class="pager">{{ $manualOrders->withQueryString()->links('pagination::tailwind') }}</div>
        @endif
    </div>
@endif

{{-- Modal proses massal --}}
<div id="bulkModal" class="fixed inset-0 bg-[#0F1B33]/60 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl w-[98vw] h-[94vh] flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="text-lg font-bold text-[#162749]">Edit & Proses Data Terpilih</h3>
                <p class="text-sm text-gray-500" id="modalCount">0 data dipilih</p>
            </div>
            <button type="button" id="btnCloseModal" aria-label="Tutup" class="p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>

        <div class="flex-1 overflow-auto px-6 py-4">
            <table class="w-full text-sm border border-gray-200 min-w-[1500px]">
                <thead>
                    <tr>
                        @foreach([
                            ['Status', 'w-24'], ['ID Pesan', 'w-32'], ['To Customer', 'min-w-[240px]'], ['Kategori Pesanan', 'w-40'],
                            ['Group', 'w-28'], ['Payment Date', 'w-36'], ['Payment Channel', 'w-44'], ['Distribusi *', 'w-40'],
                            ['Jasa Kurir *', 'min-w-[220px]'], ['Service', 'min-w-[190px]'], ['Catatan', 'min-w-[220px]'],
                        ] as [$th, $w])
                            <th class="sticky top-0 z-10 bg-gray-50 border-b border-gray-200 px-3 py-2.5 text-left font-semibold text-gray-600 {{ $w }}">{{ $th }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody id="modalTableBody" class="divide-y divide-gray-100"></tbody>
            </table>
        </div>

        <div class="flex justify-end gap-2 px-6 py-4 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <button type="button" id="btnCancelModal"
                    class="px-5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 hover:bg-white transition">Batal</button>
            <button type="button" id="saveBulkBtn"
                    class="inline-flex items-center gap-2 bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-6 py-2.5 rounded-xl text-sm font-medium transition disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h11l3 3v13H5Z"/><path d="M8 4v5h7V4M8 20v-6h8v6"/></svg>
                <span id="saveBulkLabel">Simpan & Kunci Semua Data</span>
            </button>
        </div>
    </div>
</div>

<datalist id="dl-kurir">
    <option value="JNE"><option value="TIKI"><option value="Lion Parcel">
</datalist>

<form id="bulkForm" action="{{ route('import.manual.bulk-action') }}" method="POST" class="hidden">
    @csrf
    <input type="hidden" name="action" value="processed">
    <input type="hidden" name="per_item" id="perItemInput">
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var URL_IDS   = "{{ route('import.manual.filtered-ids') }}";
    var URL_MODAL = "{{ route('import.manual.get-modal-data') }}";
    var CSRF      = "{{ csrf_token() }}";
    var GRUP_COLORS  = @json($grupColors);
    var GRUP_DEFAULT = @json($grupDefault);
    var CTL = 'w-full border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#E85D2A]/40 focus:border-[#E85D2A]';

    var selectedIds = [];
    var form  = document.getElementById('filterForm');
    var modal = document.getElementById('bulkModal');
    var body  = document.getElementById('modalTableBody');

    function $(s, r) { return (r || document).querySelector(s); }
    function $$(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }
    function val(name) { var el = form.elements[name]; return el ? el.value : ''; }
    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    /* ---------- Bar proses massal ---------- */
    function setBar(text, tone) {
        var el = $('#selectedCount');
        el.textContent = text;
        el.className = 'text-sm font-medium ' + (tone === 'err' ? 'text-red-600' : tone === 'ok' ? 'text-emerald-700' : 'text-gray-700');
    }

    function checkFilterStatus() {
        $('#bulkActionBar').classList.toggle('hidden', !(val('start_date') && val('end_date')));
    }

    function updateBarInfo() {
        var btn = $('#processAllBtn');
        var rows = $$('#dataTable tbody tr[data-row]');
        var belum = rows.filter(function (r) { return r.dataset.processed !== '1'; }).length;

        if (rows.length === 0) {
            btn.style.display = 'none';
            setBar('Tidak ada data pada filter ini', 'err');
        } else if (belum === 0) {
            btn.style.display = 'none';
            setBar('Semua data pada filter ini sudah diproses', 'ok');
        } else {
            btn.style.display = '';
            setBar('Siap memproses ' + belum + ' data sesuai filter tanggal');
        }
    }

    function requestJson(url, options) {
        options = options || {};
        options.headers = Object.assign({ 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, options.headers || {});
        return fetch(url, options).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        });
    }

    function processAll() {
        if (!val('start_date') || !val('end_date')) {
            setBar('Isi Dari Tanggal dan Sampai Tanggal terlebih dahulu.', 'err');
            return;
        }
        var qs = new URLSearchParams();
        ['start_date', 'end_date', 'order_id', 'grup', 'customer_name', 'product_name', 'product_sku', 'status', 'payment_method']
            .forEach(function (n) { qs.append(n, val(n)); });

        requestJson(URL_IDS + '?' + qs.toString())
            .then(function (res) {
                if (!res.count) { setBar('Tidak ada data yang belum diproses.', 'err'); return; }
                selectedIds = res.ids;
                setBar(res.count + ' data akan diproses');
                loadModalData();
            })
            .catch(function () { setBar('Gagal mengambil data.', 'err'); });
    }

    /* ---------- Modal ---------- */
    function badge(text, cls) {
        return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold ' + cls + '">' + esc(text) + '</span>';
    }

    function rowHtml(item) {
        var locked = Boolean(item.is_processed);
        var distribusi = String(item.status_kirim || 'Dikirim').trim();
        var diambil = distribusi === 'Diambil';
        var jasa = diambil ? 'Diambil Sendiri' : (item.jasa_kurir || 'Lion Parcel');
        var isLion = !diambil && (jasa === 'Lion Parcel');
        var svc = item.service_kurir || (isLion ? 'REGPACK' : 'REG');
        var dis = diambil ? ' disabled' : '';

        var cDist, cJasa, cSvc, cCatatan;
        if (locked) {
            cDist = badge(distribusi, 'bg-emerald-100 text-emerald-700');
            cJasa = cSvc = '<span class="text-sm text-gray-500">Terkunci</span>';
            cCatatan = '<span class="text-xs text-gray-500 italic">Sudah diproses' + (item.processed_at ? ' pada ' + esc(item.processed_at) : '') + '</span>';
        } else {
            cDist = badge(distribusi, 'bg-blue-100 text-blue-700');
            cJasa = '<input type="text" list="dl-kurir" class="jasa-kurir ' + CTL + '" value="' + esc(jasa) + '" placeholder="Pilih atau ketik jasa kurir"' + dis + '>';

            var opts = ['', 'REGPACK', 'BOSPACK', 'JAGOPACK', 'BIGPACK'].map(function (o) {
                return '<option value="' + o + '"' + (o === svc ? ' selected' : '') + '>' + (o || 'Pilih Service') + '</option>';
            }).join('');
            cSvc = '<select class="service-select ' + CTL + (isLion ? '' : ' hidden') + '">' + opts + '</select>' +
                   '<input type="text" class="service-text ' + CTL + (isLion ? ' hidden' : '') + '" value="' + esc(diambil ? '' : svc) + '" placeholder="REG / YES / CTC"' + dis + '>';
            cCatatan = '<input type="text" class="catatan ' + CTL + '" placeholder="Catatan tambahan...">';
        }

        var grup = String(item.grup || '').toUpperCase();
        var cGrup = grup ? badge('Group ' + grup, GRUP_COLORS[grup] || GRUP_DEFAULT) : '<span class="text-gray-400">-</span>';
        var cPay = item.payment_date ? esc(item.payment_date) : badge('Pending', 'bg-amber-100 text-amber-800 border border-amber-200');

        var cCust = '<div class="flex flex-col gap-0.5"><span>' + esc(item.to_customer) + '</span>';
        if (item.is_mismatch) {
            if (item.nama_excel)  cCust += '<span class="text-xs text-orange-700">Excel: ' + esc(item.nama_excel) + '</span>';
            if (item.nama_master) cCust += '<span class="text-xs text-emerald-700">Kemitraan: ' + esc(item.nama_master) + '</span>';
            cCust += '<span class="self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">Mismatch</span>';
        }
        cCust += '</div>';

        return '<tr data-id="' + esc(item.id) + '" data-distribusi="' + esc(distribusi) + '" data-locked="' + (locked ? 1 : 0) + '" class="' + (locked ? 'bg-gray-100 text-gray-500' : 'hover:bg-gray-50') + '">' +
            '<td class="px-3 py-2.5">' + esc(item.status_pembayaran || '-') + '</td>' +
            '<td class="px-3 py-2.5 font-medium">' + esc(item.invoice) + '</td>' +
            '<td class="px-3 py-2.5">' + cCust + '</td>' +
            '<td class="px-3 py-2.5 font-medium text-gray-700">' + esc(item.pesanan || '-') + '</td>' +
            '<td class="px-3 py-2.5">' + cGrup + '</td>' +
            '<td class="px-3 py-2.5">' + cPay + '</td>' +
            '<td class="px-3 py-2.5 font-medium text-[#28447F]">' + esc(item.payment_channel) + '</td>' +
            '<td class="px-3 py-2.5">' + cDist + '</td>' +
            '<td class="px-3 py-2.5">' + cJasa + '</td>' +
            '<td class="px-3 py-2.5">' + cSvc + '</td>' +
            '<td class="px-3 py-2.5">' + cCatatan + '</td>' +
        '</tr>';
    }

    function loadModalData() {
        requestJson(URL_MODAL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ ids: selectedIds })
        })
        .then(function (items) {
            body.innerHTML = items.map(rowHtml).join('');
            $('#modalCount').textContent = items.length + ' data dipilih';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            checkSaveState();
        })
        .catch(function () { setBar('Gagal memuat data ke modal.', 'err'); });
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function serviceValue(row) {
        var sel = $('.service-select', row), txt = $('.service-text', row);
        if (!sel || !txt) return '';
        return sel.classList.contains('hidden') ? txt.value.trim() : sel.value;
    }

    function syncService(row) {
        var jasa = $('.jasa-kurir', row).value.trim();
        var sel = $('.service-select', row), txt = $('.service-text', row);

        if (jasa === 'JNE' || jasa === 'TIKI') {
            sel.classList.add('hidden'); txt.classList.remove('hidden');
            txt.value = 'REG'; txt.readOnly = true;
        } else if (jasa === 'Lion Parcel') {
            sel.classList.remove('hidden'); txt.classList.add('hidden');
            sel.value = 'REGPACK';
        } else {
            sel.classList.add('hidden'); txt.classList.remove('hidden');
            txt.readOnly = false; txt.value = ''; txt.placeholder = 'REG, YES, CTC, dll';
        }
    }

    function checkSaveState() {
        var valid = true;
        $$('tr[data-id]', body).forEach(function (row) {
            if (row.dataset.locked === '1') return;
            var diambil = row.dataset.distribusi === 'Diambil';
            var jasa = diambil ? 'Diambil Sendiri' : $('.jasa-kurir', row).value.trim();
            if (!jasa) valid = false;
            if (row.dataset.distribusi === 'Dikirim' && !serviceValue(row)) valid = false;
        });
        var btn = $('#saveBulkBtn');
        btn.disabled = !valid;
        $('#saveBulkLabel').textContent = valid ? 'Simpan & Kunci Semua Data' : 'Lengkapi Jasa Kurir & Service';
    }

    function executeBulk() {
        if ($('#saveBulkBtn').disabled) return;
        if (!confirm('Yakin ingin memproses ' + selectedIds.length + ' data?')) return;

        var updates = [];
        $$('tr[data-id]', body).forEach(function (row) {
            if (row.dataset.locked === '1') return;
            var diambil = row.dataset.distribusi === 'Diambil';
            updates.push({
                id: row.dataset.id,
                status_kirim: row.dataset.distribusi,
                jasa_kurir: diambil ? 'Diambil Sendiri' : $('.jasa-kurir', row).value.trim(),
                service_kurir: diambil ? '' : serviceValue(row),
                catatan: $('.catatan', row).value
            });
        });

        $('#perItemInput').value = JSON.stringify(updates);
        $('#bulkForm').submit();
    }

    /* ---------- Event ---------- */
    $('#processAllBtn').addEventListener('click', processAll);
    $('#btnReset').addEventListener('click', function () { selectedIds = []; updateBarInfo(); });
    $('#btnCloseModal').addEventListener('click', hideModal);
    $('#btnCancelModal').addEventListener('click', hideModal);
    $('#saveBulkBtn').addEventListener('click', executeBulk);
    modal.addEventListener('click', function (e) { if (e.target === modal) hideModal(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') hideModal(); });

    body.addEventListener('change', function (e) {
        if (e.target.classList.contains('jasa-kurir')) syncService(e.target.closest('tr'));
        checkSaveState();
    });
    body.addEventListener('input', checkSaveState);

    ['start_date', 'end_date'].forEach(function (n) {
        form.elements[n].addEventListener('change', checkFilterStatus);
    });

    checkFilterStatus();
    updateBarInfo();
});
</script>
@endpush