@extends('layouts.panel')

@section('title', 'Data Casdana')

@push('styles')
<style>
    .data-table { border-collapse: separate; border-spacing: 0; width: 100%; font-size: 13px; }
    .data-table th, .data-table td { padding: 10px 12px; white-space: nowrap; border-bottom: 1px solid #0F1B330F; }
    .data-table thead th {
        position: sticky; top: 0; z-index: 10;
        background: #F8F9FB; color: #0F1B3399;
        font-weight: 600; font-size: 11.5px; text-transform: uppercase; letter-spacing: .02em;
        border-bottom: 1px solid #0F1B331A;
    }
    .data-table tbody tr:hover td { background-color: #F8F9FB; }
    .data-table .sticky-col-aksi { position: sticky; right: 0; z-index: 5; background: #fff; }
    .data-table thead .sticky-col-aksi { z-index: 15; background: #F8F9FB; }
    .nominal { font-variant-numeric: tabular-nums; }
    .filter-input {
        width: 100%; border: 1px solid #0F1B331A; border-radius: 0.75rem;
        padding: 9px 12px; font-size: 13.5px; background: #fff; color: #0F1B33;
    }
    .filter-input:focus {
        outline: none; border-color: #28447F;
        box-shadow: 0 0 0 3px rgba(40,68,127,0.12);
    }
    .pager nav p { display: none; }
</style>
@endpush

@section('content')

    @include('partials.flash')

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Data Casdana</h1>
            <p class="mt-1 text-sm text-navy-950/55">Import & Kelola Data Transaksi Kas Dana</p>
        </div>

        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('import.index') }}"
               class="inline-flex items-center gap-2 bg-white border border-navy-950/10 hover:border-navy-700 text-navy-950/70 hover:text-navy-700 text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali ke Daftar Import
            </a>

            <button type="button" onclick="document.getElementById('importForm').classList.toggle('hidden')"
                    class="inline-flex items-center gap-2 bg-navy-700 hover:bg-navy-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="M17 8l-5-5-5 5"/><path d="M12 3v12"/></svg>
                Import Data Baru
            </button>
        </div>
    </div>

    {{-- ============ FILTER ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card p-6">
        <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Invoice Number</label>
                <input type="text" name="invoice_number" value="{{ request('invoice_number') }}" class="filter-input" placeholder="Cari Invoice...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Merchant</label>
                <input type="text" name="merchant" value="{{ request('merchant') }}" class="filter-input" placeholder="Nama Merchant...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Customer</label>
                <input type="text" name="customer" value="{{ request('customer') }}" class="filter-input" placeholder="Nama Customer...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Status</label>
                <select name="status" class="filter-input">
                    <option value="">Semua Status</option>
                    <option value="PAID" @selected(request('status') == 'PAID')>PAID</option>
                    <option value="PENDING" @selected(request('status') == 'PENDING')>PENDING</option>
                    <option value="EXPIRED" @selected(request('status') == 'EXPIRED')>EXPIRED</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Payment Channel</label>
                <input type="text" name="payment_channel" value="{{ request('payment_channel') }}" class="filter-input" placeholder="Payment Channel...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="filter-input">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="filter-input">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Tampilkan</label>
                <select name="per_page" onchange="this.form.submit()" class="filter-input">
                    @foreach([25,50,100,200,500,1000] as $n)
                        <option value="{{ $n }}" @selected((int)request('per_page') === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3 lg:col-span-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-navy-700 hover:bg-navy-800 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('import.casdana') }}" class="text-navy-950/50 hover:text-rust-600 px-4 py-2.5 text-sm font-medium whitespace-nowrap transition-colors">Reset</a>
            </div>
        </form>
    </div>

    {{-- ============ FORM IMPORT ============ --}}
    <div id="importForm" class="hidden mt-6 bg-white rounded-2xl shadow-card p-6">
        <h2 class="text-lg font-semibold text-navy-950 mb-4">Upload File Excel / CSV</h2>
        <form action="{{ route('import.casdana.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex gap-4 items-end flex-wrap">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-sm font-medium text-navy-950/70 mb-2">Pilih File</label>
                    <input type="file" name="import_file"
                           class="block w-full text-sm text-navy-950/60 file:mr-4 file:py-3 file:px-6 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-navy-700/10 file:text-navy-700 hover:file:bg-navy-700/20"
                           accept=".xlsx,.xls,.csv" required>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-8 py-3 rounded-xl font-semibold text-sm transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Import Sekarang
                </button>
            </div>
            <p class="text-xs text-navy-950/40 mt-2">Format yang didukung: .xlsx, .xls, .csv (max 10MB)</p>
        </form>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-left">Invoice Number</th>
                    <th class="text-left">Merchant</th>
                    <th class="text-left">Customer</th>
                    <th class="text-left">Status</th>
                    <th class="text-left">Payment Date</th>
                    <th class="text-left">Payment Channel</th>
                    <th class="text-left">Payment Code</th>
                    <th class="text-right">Amount (IDR)</th>
                    <th class="sticky-col-aksi text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($casdanaTransactions as $transaction)
                <tr>
                    <td class="font-medium text-navy-950">{{ $transaction->invoice_number }}</td>
                    <td>{{ $transaction->merchant ?? '-' }}</td>
                    <td>{{ $transaction->customer ?? '-' }}</td>
                    <td>
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold
                            @if(in_array(strtoupper($transaction->status ?? ''), ['SETTLED','PAID'])) bg-emerald-100 text-emerald-700
                            @elseif(strtoupper($transaction->status ?? '') == 'PENDING') bg-amber-100 text-amber-800
                            @else bg-red-100 text-red-700 @endif">
                            {{ $transaction->status ?? '-' }}
                        </span>
                    </td>
                    <td>{{ $transaction->payment_date ? $transaction->payment_date->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $transaction->payment_channel ?? '-' }}</td>
                    <td>{{ $transaction->payment_code ?? '-' }}</td>
                    <td class="text-right font-semibold nominal">
                        Rp {{ number_format($transaction->amount ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="sticky-col-aksi text-center">
                        <div class="flex items-center justify-center gap-3">
                            <a href="{{ route('import.casdana.edit', $transaction->id) }}"
                               class="inline-flex text-navy-700 hover:text-rust-600 transition-colors" title="Edit">
                                <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17l-1 3Z"/><path d="m14.5 7.5 2 2"/></svg>
                            </a>
                            <button type="button" onclick="if(confirm('Yakin hapus?')) document.getElementById('delete-form-{{ $transaction->id }}').submit()"
                                    class="inline-flex text-red-500 hover:text-red-700 transition-colors" title="Hapus">
                                <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                            </button>
                            <form id="delete-form-{{ $transaction->id }}" action="{{ route('import.casdana.destroy', $transaction->id) }}" method="POST" class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-16 text-navy-950/40">
                        Tidak ada data yang sesuai filter.
                    </td>
                </tr>
                @endforelse
            </tbody>

            @if($casdanaTransactions->count() > 0)
            <tfoot>
                <tr class="bg-navy-950/[0.03] border-t-2 border-navy-950/10 font-semibold">
                    <td colspan="7" class="text-right pr-4 py-3 text-navy-950/70">Total Amount</td>
                    <td class="text-right py-3 nominal text-base text-navy-950">
                        Rp {{ number_format($totalAmount ?? $casdanaTransactions->sum('amount'), 0, ',', '.') }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>

    @if($casdanaTransactions->count() > 0)
    <div class="mt-6 text-sm text-navy-950/60 flex justify-between items-center flex-wrap gap-3 pager">
        <div>
            Menampilkan <strong class="text-navy-950">{{ $casdanaTransactions->count() }}</strong> data
            <span class="text-navy-950/40">(Total: {{ $casdanaTransactions->total() }} data)</span>
        </div>
        <div>{{ $casdanaTransactions->withQueryString()->links('pagination::tailwind') }}</div>
    </div>
    @endif

@endsection