@extends('layouts.panel')

@section('title', 'Manual Pemesanan Modul')

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
    .data-table .sticky-col { position: sticky; left: 0; z-index: 5; background: #fff; }
    .data-table thead .sticky-col { z-index: 15; background: #F8F9FB; }
    .data-table .sticky-col-aksi { position: sticky; right: 0; z-index: 5; background: #fff; }
    .data-table thead .sticky-col-aksi { z-index: 15; background: #F8F9FB; }
    .data-table tbody tr.processed-row td { background-color: #F1F5F9; color: #64748B; }
    .data-table tbody tr.processed-row td.sticky-col,
    .data-table tbody tr.processed-row td.sticky-col-aksi { background-color: #F1F5F9; }
    .cell-clip { display: block; max-width: 220px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
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
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Manual Pemesanan Modul</h1>
            <p class="mt-1 text-sm text-navy-950/55">Kelola data pemesanan modul secara manual</p>
        </div>

        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('order-manual-modul.index') }}"
               class="inline-flex items-center gap-2 bg-white border border-navy-950/10 hover:border-navy-700 text-navy-950/70 hover:text-navy-700 text-sm font-medium px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>

            <form action="{{ route('order-manual-modul.manual.sync') }}" method="POST"
                  onsubmit="return confirm('Yakin ingin sync data dari Bimba Shop + Casdana?\n\nData Manual Modul yang cocok (no cabang + nama unit + produk) akan di-update: Order Date, Payment Date, Estimasi Print PL, dll.')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-navy-700 hover:bg-navy-800 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v5h5"/><path d="M20 20v-5h-5"/><path d="M4 9a8 8 0 0 1 14-4.9L20 6M20 15a8 8 0 0 1-14 4.9L4 18"/></svg>
                    Sync Bimba Shop + Casdana
                </button>
            </form>

            <a href="{{ route('order-manual-modul.realisasi') }}"
               class="inline-flex items-center gap-2 bg-navy-950/5 hover:bg-navy-950/10 text-navy-950 text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                Rekap Aktual Manual
            </a>

            <a href="{{ route('order-manual-modul.manual.create') }}"
               class="inline-flex items-center gap-2 bg-rust-600 hover:bg-rust-500 text-white text-sm font-semibold px-5 py-2.5 rounded-xl transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Create Manual
            </a>
        </div>
    </div>

    {{-- ============ BAR PROSES MASSAL ============ --}}
    <div id="bulkActionBar" class="hidden mt-6 bg-white rounded-2xl shadow-card p-5 flex items-center justify-between border border-navy-700/10 flex-wrap gap-3">
        <span id="selectedCount" class="text-sm font-semibold text-navy-950">Siap memproses data sesuai filter tanggal</span>
        <div class="flex items-center gap-3">
            <button type="button" onclick="processAllFilteredData()" id="processAllBtn"
                    class="inline-flex items-center gap-2 bg-rust-600 hover:bg-rust-500 text-white px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18M8 2v4M16 2v4"/></svg>
                Proses & Edit Semua Sesuai Filter Tanggal
            </button>
            <button type="button" onclick="clearSelection()"
                    class="bg-navy-950/5 hover:bg-navy-950/10 text-navy-950/70 px-5 py-2.5 rounded-xl font-semibold text-sm transition-colors">
                Reset
            </button>
        </div>
    </div>

    {{-- ============ FILTER ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card p-6">
        <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Order ID</label>
                <input type="text" name="order_id" value="{{ request('order_id') }}" class="filter-input" placeholder="Cari Order ID...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Customer</label>
                <input type="text" name="customer_name" value="{{ request('customer_name') }}" class="filter-input" placeholder="Nama Customer...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Item Name</label>
                <input type="text" name="product_name" value="{{ request('product_name') }}" class="filter-input" placeholder="Nama Produk...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">SKU</label>
                <input type="text" name="product_sku" value="{{ request('product_sku') }}" class="filter-input" placeholder="SKU...">
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Payment Method</label>
                <select name="payment_method" class="filter-input">
                    <option value="">Semua</option>
                    <option value="cash" @selected(request('payment_method') == 'cash')>Cash</option>
                    <option value="transfer" @selected(request('payment_method') == 'transfer')>Transfer</option>
                    <option value="manual" @selected(request('payment_method') == 'manual')>Manual</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-navy-950/60 mb-1.5">Status</label>
                <select name="status" class="filter-input">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                    <option value="processing" @selected(request('status') == 'processing')>Processing</option>
                    <option value="completed" @selected(request('status') == 'completed')>Completed</option>
                </select>
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
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" @selected((int)request('per_page', 25) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3 lg:col-span-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-navy-700 hover:bg-navy-800 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('order-manual-modul.manual') }}" class="text-navy-950/50 hover:text-rust-600 px-4 py-2.5 text-sm font-medium transition-colors">Reset</a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sticky-col text-left">ID Manual</th>
                    <th class="text-left">ID Pesan</th>
                    <th class="text-left">Nama Unit</th>
                    <th class="text-left">Cabang</th>
                    <th class="text-left">Alamat Kirim</th>
                    <th class="text-left">Kab/Kota</th>
                    <th class="text-left">Kategori Pesanan</th>
                    <th class="text-center">Qty</th>
                    <th class="text-left">Order Date</th>
                    <th class="text-left">Payment Date</th>
                    <th class="text-center">Status Bayar</th>
                    <th class="text-center">Status biMBAShop</th>
                    <th class="text-center">Estimasi Print PL</th>
                    <th class="text-center">Estimasi Persiapan</th>
                    <th class="text-left">Jasa Kurir</th>
                    <th class="text-left">Service Kurir</th>
                    <th class="text-left">Distribusi</th>
                    <th class="text-right">Ship Total</th>
                    <th class="text-right">Berat (gr)</th>
                    <th class="text-right">Order Total</th>
                    <th class="text-left">Payment Channel</th>
                    <th class="text-left">Status</th>
                    <th class="text-center">Tanggal Proses</th>
                    <th class="sticky-col-aksi text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($manualOrders as $order)
                    @php
                        $isProcessed = (bool) ($order->is_processed ?? false);
                        $paymentDate = $order->payment_date ? \Carbon\Carbon::parse($order->payment_date) : null;
                        $estimasiPrint = $order->estimasi_print_pl ? \Carbon\Carbon::parse($order->estimasi_print_pl) : null;
                        $estimasiPersiapan = $order->estimasi_persiapan ? \Carbon\Carbon::parse($order->estimasi_persiapan) : null;
                        $status = strtolower($order->status ?? 'pending');
                        $statusClass = match($status) {
                            'completed' => 'bg-emerald-100 text-emerald-700',
                            'processing' => 'bg-blue-100 text-blue-700',
                            default => 'bg-amber-100 text-amber-800',
                        };
                        $namaUnit = $order->customer_name ?? '-';
                        $kategori = $order->product_name ?? $order->product_sku ?? '-';
                        $noCabItem = trim($order->billing_last_name ?? '');
                        $mismatch = $mismatchMap[$noCabItem] ?? null;
                        $isMismatch = $mismatch
                            || str_contains($order->catatan ?? '', 'NAMA_MISMATCH')
                            || str_contains($order->notes ?? '', 'NAMA_MISMATCH');
                    @endphp
                    <tr class="{{ $isProcessed ? 'processed-row' : '' }}">
                        <td class="sticky-col font-semibold text-navy-950">
                            MM-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="font-semibold text-rust-600">{{ $order->order_id ?? '-' }}</td>
                        <td class="font-semibold text-navy-950">
                            <div class="flex flex-col gap-0.5">
                                <span class="cell-clip" title="{{ $namaUnit }}">{{ $namaUnit }}</span>
                                @if($isMismatch && $mismatch)
                                    <div class="text-xs mt-0.5 space-y-0.5 whitespace-normal">
                                        <div class="text-orange-700"><span class="text-navy-950/40">Excel:</span> {{ $mismatch['nama_excel'] }}</div>
                                        <div class="text-emerald-700"><span class="text-navy-950/40">Kemitraan:</span> {{ $mismatch['nama_master'] }}</div>
                                    </div>
                                    <span class="inline-flex items-center gap-1 self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4M12 17h.01"/></svg>
                                        Mismatch
                                    </span>
                                @elseif($isMismatch)
                                    <span class="inline-flex items-center gap-1 self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"/><path d="M12 9v4M12 17h.01"/></svg>
                                        Mismatch
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $order->billing_last_name ?? '-' }}</td>
                        <td><span class="cell-clip" title="{{ $order->shipping_address_1 ?? '-' }}">{{ $order->shipping_address_1 ?? '-' }}</span></td>
                        <td>{{ $order->shipping_city ?? '-' }}</td>
                        <td><span class="cell-clip" title="{{ $kategori }}">{{ $kategori }}</span></td>
                        <td class="text-center font-semibold">{{ $order->qty ?? 0 }}</td>
                        <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '-' }}</td>
                        <td>
                            @if($paymentDate)
                                {{ $paymentDate->format('d/m/Y H:i') }}
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $statusBayar = strtoupper($order->status_bayar ?? ''); @endphp
                            @if(in_array($statusBayar, ['SUCCESS','SETTLED','PAID']))
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">{{ $statusBayar }}</span>
                            @elseif($statusBayar)
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">{{ $statusBayar }}</span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-navy-950/5 text-navy-950/40">-</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $statusBimba = strtolower($order->status_bimbashop ?? '');
                                $bimbaClass = match($statusBimba) {
                                    'completed'  => 'bg-emerald-100 text-emerald-700',
                                    'processing' => 'bg-blue-100 text-blue-700',
                                    'on-hold'    => 'bg-orange-100 text-orange-700',
                                    'pending'    => 'bg-amber-100 text-amber-800',
                                    default      => 'bg-navy-950/5 text-navy-950/40',
                                };
                            @endphp
                            @if($statusBimba)
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $bimbaClass }}">{{ ucfirst($statusBimba) }}</span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-navy-950/5 text-navy-950/40">-</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $estimasiPrint ? $estimasiPrint->format('d/m/Y') : '-' }}</td>
                        <td class="text-center">{{ $estimasiPersiapan ? $estimasiPersiapan->format('d/m/Y') : '-' }}</td>
                        <td>{{ $order->ekspedisi ?? '-' }}</td>
                        <td>{{ $order->service_pengiriman ?? '-' }}</td>
                        <td>{{ $order->status_kirim ?? '-' }}</td>
                        <td class="text-right">{{ number_format($order->ship_total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($order->order_weight ?? 0) }}</td>
                        <td class="text-right">{{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $order->payment_method ?? 'manual' }}</td>
                        <td>
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="text-center">
                            {{ $order->processed_at ? \Carbon\Carbon::parse($order->processed_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="sticky-col-aksi text-center">
                            @if($isProcessed)
                                <span class="text-navy-950/30 cursor-not-allowed" title="Data sudah diproses / dikunci">
                                    <svg class="w-4.5 h-4.5 inline" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                </span>
                            @else
                                <a href="{{ route('order-manual-modul.manual.edit', $order->id) }}"
                                   class="inline-flex text-navy-700 hover:text-rust-600 transition-colors" title="Edit">
                                    <svg class="w-4.5 h-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17l-1 3Z"/><path d="m14.5 7.5 2 2"/></svg>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="24" class="text-center py-16 text-navy-950/40">
                            Belum ada data pemesanan modul.<br>
                            Silakan <strong class="text-navy-950/60">Create Manual</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($manualOrders->total() > 0)
            <div class="px-6 py-4 border-t border-navy-950/10 flex items-center justify-between flex-wrap gap-3 pager">
                <div class="text-sm text-navy-950/60">
                    Menampilkan <span class="font-medium text-navy-950">{{ $manualOrders->firstItem() }}</span>
                    sampai <span class="font-medium text-navy-950">{{ $manualOrders->lastItem() }}</span>
                    dari total <span class="font-medium text-navy-950">{{ $manualOrders->total() }}</span> data
                </div>
                <div>{{ $manualOrders->withQueryString()->links('pagination::tailwind') }}</div>
            </div>
        @endif
    </div>

    {{-- ============ MODAL BULK ============ --}}
    <div id="bulkModal" class="hidden fixed inset-0 bg-navy-950/60 items-center justify-center z-50 p-4">
        <div class="bg-white rounded-3xl shadow-2xl w-[98vw] h-[95vh] mx-2 flex flex-col">
            <div class="p-6 border-b border-navy-950/10 flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-semibold text-navy-950">Edit & Proses Data Modul</h3>
                    <p class="text-navy-950/55" id="modalCount">0 data dipilih</p>
                </div>
                <button type="button" onclick="hideBulkModal()" class="text-navy-950/40 hover:text-rust-600 transition-colors">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-6">
                <table class="w-full text-sm border border-navy-950/10 min-w-[1600px]">
                    <thead class="bg-navy-950/[0.03] sticky top-0">
                        <tr class="divide-x divide-navy-950/10">
                            <th class="px-4 py-3 text-left">Status</th>
                            <th class="px-4 py-3 text-left">ID Pesan</th>
                            <th class="px-4 py-3 text-left">To Customer</th>
                            <th class="px-4 py-3 text-left">Kategori</th>
                            <th class="px-4 py-3 text-left">Payment Date</th>
                            <th class="px-4 py-3 text-left">Payment Channel</th>
                            <th class="px-4 py-3 text-left">Distribusi</th>
                            <th class="px-4 py-3 text-left">Jasa Kurir *</th>
                            <th class="px-4 py-3 text-left">Service</th>
                            <th class="px-4 py-3 text-left">Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody" class="divide-y divide-navy-950/10"></tbody>
                </table>
            </div>
            <div class="p-6 border-t border-navy-950/10 bg-navy-950/[0.02] flex justify-end gap-3">
                <button type="button" onclick="hideBulkModal()" class="px-6 py-3 text-navy-950/60 hover:bg-navy-950/5 rounded-2xl font-medium transition-colors">Batal</button>
                <button type="button" id="saveBulkBtn" onclick="executeBulkAction()" class="bg-rust-600 hover:bg-rust-500 text-white px-8 py-3 rounded-2xl font-semibold transition-colors">
                    Simpan & Kunci Semua Data
                </button>
            </div>
        </div>
    </div>

    <datalist id="jasaKurirOptions">
        <option value="JNE">
        <option value="TIKI">
        <option value="Lion Parcel">
    </datalist>

@endsection

@push('scripts')
<script>
const filteredIdsUrl = @json(route('order-manual-modul.manual.filtered-ids'));
const modalDataUrl   = @json(route('order-manual-modul.manual.get-modal-data'));
const bulkActionUrl  = @json(route('order-manual-modul.manual.bulk-action'));
const csrfToken      = @json(csrf_token());

let selectedIds = [];

function checkFilterStatus() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate   = document.querySelector('input[name="end_date"]').value;
    document.getElementById('bulkActionBar').classList.toggle('hidden', !(startDate && endDate));
}

function checkProcessButtonVisibility() {
    const processBtn = document.getElementById('processAllBtn');
    if (!processBtn) return;
    const unprocessed = document.querySelectorAll('.data-table tbody tr:not(.processed-row) td').length > 0;
    if (!unprocessed) {
        processBtn.style.display = 'none';
        document.getElementById('selectedCount').innerHTML =
            '<span class="text-emerald-600 font-medium">Semua data pada filter ini sudah diproses</span>';
    } else {
        processBtn.style.display = 'inline-flex';
        document.getElementById('selectedCount').textContent = 'Siap memproses data sesuai filter tanggal';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    checkFilterStatus();
    checkProcessButtonVisibility();
    ['start_date', 'end_date'].forEach(name => {
        document.querySelector(`input[name="${name}"]`).addEventListener('change', () => {
            checkFilterStatus();
            setTimeout(checkProcessButtonVisibility, 300);
        });
    });
});

function processAllFilteredData() {
    const startDate = document.querySelector('input[name="start_date"]').value;
    const endDate   = document.querySelector('input[name="end_date"]').value;
    if (!startDate || !endDate) {
        alert('Harap isi Dari Tanggal dan Sampai Tanggal!');
        return;
    }
    const params = new URLSearchParams({
        start_date: startDate,
        end_date: endDate,
        order_id: document.querySelector('input[name="order_id"]').value || '',
        customer_name: document.querySelector('input[name="customer_name"]').value || '',
        product_name: document.querySelector('input[name="product_name"]').value || '',
        product_sku: document.querySelector('input[name="product_sku"]').value || '',
        status: document.querySelector('select[name="status"]').value || '',
        payment_method: document.querySelector('select[name="payment_method"]').value || '',
    });

    fetch(`${filteredIdsUrl}?${params.toString()}`)
        .then(res => res.json())
        .then(response => {
            if (response.count === 0) {
                alert('Tidak ada data yang belum diproses.');
                return;
            }
            selectedIds = response.ids;
            document.getElementById('selectedCount').textContent = response.count + ' data akan diproses';
            loadModalData();
        })
        .catch(() => alert('Gagal mengambil data.'));
}

function loadModalData() {
    fetch(modalDataUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ ids: selectedIds }),
    })
        .then(res => res.json())
        .then(items => {
            const tbody = document.getElementById('modalTableBody');
            tbody.innerHTML = '';

            items.forEach(item => {
                const isLocked = Boolean(item.is_processed);
                const distribusi = (item.status_kirim || 'Dikirim').trim();

                const tr = document.createElement('tr');
                tr.dataset.id = item.id;
                tr.dataset.distribusi = distribusi;
                tr.className = isLocked ? 'processed-row' : '';

                const distribusiBadge = `<span class="inline-flex px-4 py-2.5 text-sm font-semibold rounded-2xl ${isLocked ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'}">${distribusi}</span>`;
                const paymentDateHtml = item.payment_date || '<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>';

                let jasaHtml, serviceHtml, catatanHtml;
                if (isLocked) {
                    jasaHtml = '<span class="text-sm text-navy-950/40">— Terkunci —</span>';
                    serviceHtml = '<span class="text-sm text-navy-950/40">— Terkunci —</span>';
                    catatanHtml = `<span class="text-xs text-navy-950/40 italic">Sudah diproses ${item.processed_at || ''}</span>`;
                } else {
                    const jasa = item.jasa_kurir || 'Lion Parcel';
                    const disabled = distribusi === 'Diambil' ? 'disabled' : '';
                    jasaHtml = `<input type="text" class="jasa-kurir-input w-full border border-navy-950/10 rounded-2xl px-3 py-2.5 text-sm" list="jasaKurirOptions" value="${distribusi === 'Diambil' ? 'Diambil Sendiri' : jasa}" ${disabled}>`;
                    serviceHtml = serviceFieldHtml(distribusi === 'Diambil' ? '' : jasa, item.service_kurir, disabled);
                    catatanHtml = '<input type="text" class="catatan w-full border border-navy-950/10 rounded-2xl px-3 py-2.5 text-sm" placeholder="Catatan...">';
                }

                tr.innerHTML = `
                    <td class="px-4 py-3">${item.status_pembayaran || '-'}</td>
                    <td class="px-4 py-3 font-medium">${item.invoice}</td>
                    <td class="px-4 py-3">${item.to_customer}</td>
                    <td class="px-4 py-3">${item.pesanan || '-'}</td>
                    <td class="px-4 py-3">${paymentDateHtml}</td>
                    <td class="px-4 py-3 text-navy-700">${item.payment_channel}</td>
                    <td class="px-4 py-3">${distribusiBadge}</td>
                    <td class="px-4 py-3 jasa-cell">${jasaHtml}</td>
                    <td class="px-4 py-3 service-cell">${serviceHtml}</td>
                    <td class="px-4 py-3">${catatanHtml}</td>
                `;
                tbody.appendChild(tr);
            });

            document.getElementById('modalCount').textContent = items.length + ' data dipilih';
            document.getElementById('bulkModal').classList.remove('hidden');
            document.getElementById('bulkModal').classList.add('flex');
            initModalLogic();
        })
        .catch(() => alert('Gagal memuat data ke modal.'));
}

function serviceFieldHtml(jasa, currentValue, disabled = '') {
    if (jasa === 'Lion Parcel') {
        const opts = ['REGPACK', 'BOSPACK', 'JAGOPACK', 'BIGPACK'];
        const sel = currentValue && opts.includes(currentValue) ? currentValue : 'REGPACK';
        return `<select class="service-kurir w-full border border-navy-950/10 rounded-2xl px-3 py-2.5 text-sm" ${disabled}>
            ${opts.map(o => `<option value="${o}" ${o === sel ? 'selected' : ''}>${o}</option>`).join('')}
        </select>`;
    }
    if (jasa === 'JNE' || jasa === 'TIKI') {
        return `<input type="text" class="service-kurir w-full border border-navy-950/10 rounded-2xl px-3 py-2.5 text-sm" value="${currentValue || 'REG'}" ${disabled}>`;
    }
    return `<select class="service-kurir w-full border border-navy-950/10 rounded-2xl px-3 py-2.5 text-sm" ${disabled}><option value="">Pilih Service</option></select>`;
}

function initModalLogic() {
    document.querySelectorAll('#modalTableBody tr').forEach(row => {
        const jasaInput = row.querySelector('.jasa-kurir-input');
        if (!jasaInput || jasaInput.disabled) return;

        ['input', 'change'].forEach(evt => {
            jasaInput.addEventListener(evt, () => {
                const cell = row.querySelector('.service-cell');
                cell.innerHTML = serviceFieldHtml(jasaInput.value.trim());
                checkSaveButtonState();
            });
        });
    });

    document.getElementById('modalTableBody').addEventListener('input', e => {
        if (e.target.classList.contains('service-kurir')) checkSaveButtonState();
    });
    document.getElementById('modalTableBody').addEventListener('change', e => {
        if (e.target.classList.contains('service-kurir')) checkSaveButtonState();
    });

    checkSaveButtonState();
}

function checkSaveButtonState() {
    let isValid = true;
    document.querySelectorAll('#modalTableBody tr:not(.processed-row)').forEach(row => {
        const distribusi = row.dataset.distribusi;
        let jasa = row.querySelector('.jasa-kurir-input')?.value.trim() || '';
        if (distribusi === 'Diambil') jasa = 'Diambil Sendiri';

        const serviceField = row.querySelector('.service-kurir');
        const serviceValue = serviceField ? serviceField.value.trim() : '';

        if (!jasa) isValid = false;
        if (distribusi === 'Dikirim' && !serviceValue) isValid = false;
    });

    const saveBtn = document.getElementById('saveBulkBtn');
    if (isValid) {
        saveBtn.disabled = false;
        saveBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        saveBtn.textContent = 'Simpan & Kunci Semua Data';
    } else {
        saveBtn.disabled = true;
        saveBtn.classList.add('opacity-50', 'cursor-not-allowed');
        saveBtn.textContent = 'Lengkapi Jasa Kurir & Service';
    }
}

function hideBulkModal() {
    document.getElementById('bulkModal').classList.add('hidden');
    document.getElementById('bulkModal').classList.remove('flex');
}

function executeBulkAction() {
    if (document.getElementById('saveBulkBtn').disabled) {
        alert('Lengkapi Jasa Kurir dan Service!');
        return;
    }
    if (!confirm('Yakin memproses ' + selectedIds.length + ' data?')) return;

    const updates = [];
    document.querySelectorAll('#modalTableBody tr').forEach(row => {
        const distribusi = row.dataset.distribusi;
        let jasa = row.querySelector('.jasa-kurir-input')?.value.trim() || '';
        if (distribusi === 'Diambil') jasa = 'Diambil Sendiri';
        const serviceField = row.querySelector('.service-kurir');

        updates.push({
            id: row.dataset.id,
            status_kirim: distribusi,
            jasa_kurir: jasa,
            service_kurir: serviceField ? serviceField.value : '',
            catatan: row.querySelector('.catatan')?.value || '',
        });
    });

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = bulkActionUrl;

    const fields = {
        _token: csrfToken,
        action: 'processed',
        per_item: JSON.stringify(updates),
    };
    Object.entries(fields).forEach(([name, value]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
}

function clearSelection() {
    selectedIds = [];
    checkProcessButtonVisibility();
}
</script>
@endpush