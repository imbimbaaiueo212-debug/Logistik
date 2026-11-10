@extends('layouts.panel')

@section('title', 'Manual Pemesanan ' . ucfirst($jenis))

@push('styles')
<style>
    .data-table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 13px; }
    .data-table th, .data-table td { padding: 10px 12px; white-space: nowrap; border-bottom: 1px solid #0F1B330F; }
    .data-table thead th { background: #F8F9FB; color: #0F1B3399; font-weight: 600; font-size: 11.5px; text-transform: uppercase; letter-spacing: .02em; border-bottom: 1px solid #0F1B331A; }
    .data-table tbody tr:hover td { background-color: #F8F9FB; }
    .filter-input { width: 100%; border: 1px solid #0F1B331A; border-radius: 0.75rem; padding: 9px 12px; font-size: 13.5px; background: #fff; color: #0F1B33; }
    .pager nav p { display: none; }
</style>
@endpush

@section('content')
    @include('partials.flash')

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Manual Pemesanan {{ ucfirst($jenis) }}</h1>
            <p class="mt-1 text-sm text-navy-950/55">Pesanan {{ $jenis }} yang diinput manual (masuk ke Report Angka Cetak dan Penjualan)</p>
        </div>
        <a href="{{ route("order-manual-{$jenis}.manual.create") }}"
           class="inline-flex items-center gap-2 bg-[#E85D2A] hover:bg-[#D14E1F] text-white text-sm font-medium px-5 py-2.5 rounded-xl transition-colors shrink-0">
            + Create Manual
        </a>
    </div>

    <form method="GET" class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input type="text" name="order_id" value="{{ request('order_id') }}" class="filter-input" placeholder="ID Manual / ID Pesanan">
        <input type="text" name="customer_name" value="{{ request('customer_name') }}" class="filter-input" placeholder="Customer / Unit">
        <select name="status_unit" class="filter-input">
            <option value="">Semua status unit</option>
            <option value="aktif" @selected(request('status_unit') === 'aktif')>Aktif</option>
            <option value="pasif" @selected(request('status_unit') === 'pasif')>Pasif</option>
        </select>
        <button class="bg-[#162749] hover:bg-[#1D3361] text-white text-sm font-medium px-5 py-2.5 rounded-xl">Filter</button>
    </form>

    <div class="bg-white rounded-2xl shadow-card mt-6 overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID Manual</th><th>Tanggal</th><th>Customer / Unit</th><th>Status Unit</th>
                    <th>SKU</th><th>Produk</th><th class="text-right">Qty</th><th>Stokis</th>
                    <th>Ekspedisi</th><th>Service</th><th>Status</th><th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $o)
                <tr>
                    <td class="font-medium">{{ $o->manual_id }}</td>
                    <td>{{ optional($o->order_date)->format('d/m/Y H:i') }}</td>
                    <td>{{ $o->customer_name }}</td>
                    <td>{{ ucfirst($o->status_unit) }}</td>
                    <td>{{ $o->product_sku }}</td>
                    <td>{{ $o->product_name }}</td>
                    <td class="text-right font-semibold">{{ number_format($o->qty) }}</td>
                    <td>{{ $o->kode_stokis ?: '-' }}</td>
                    <td>{{ $o->ekspedisi }}</td>
                    <td>{{ $o->service_pengiriman }}</td>
                    <td>{{ $o->synced_penjualan_at ? 'Di Penjualan' : ($o->is_processed ? 'Diproses' : 'Pending') }}</td>
                    <td>
                        @if(! $o->is_processed && ! $o->synced_penjualan_at)
                            <form method="POST" action="{{ route("order-manual-{$jenis}.manual.destroy", $o->manual_id) }}"
                                  onsubmit="return confirm('Hapus seluruh item pesanan {{ $o->manual_id }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" class="text-center text-navy-950/50 py-10">Belum ada pesanan manual {{ $jenis }}.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pager mt-4">{{ $orders->links() }}</div>
@endsection
