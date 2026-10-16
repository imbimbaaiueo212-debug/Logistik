@extends('layouts.panel')

@section('title', 'Manual Pemesanan Sertifikat')

@push('styles')
<style>
    .data-table { border-collapse: separate; border-spacing: 0; }
    .data-table thead th {
        position: sticky; top: 0; z-index: 10;
        background: #F1F5F9; white-space: nowrap;
    }
    .data-table th:first-child, .data-table td:first-child {
        position: sticky; left: 0; z-index: 5; background: #fff;
    }
    .data-table th:last-child, .data-table td:last-child {
        position: sticky; right: 0; z-index: 5; background: #fff;
        box-shadow: -1px 0 0 rgba(15,27,51,0.08);
    }
    .processed-row td { background: #F1F5F9 !important; color: #64748B; }
    .cell-clip {
        display: block; max-width: 220px; overflow: hidden;
        text-overflow: ellipsis; white-space: nowrap;
    }
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

    @include('partials.page-header', [
        'title'      => 'Manual Pemesanan Sertifikat',
        'subtitle'   => 'Kelola data pemesanan sertifikat secara manual',
        'back'       => route('order-manual-sertifikat.index'),
        'backLabel'  => 'Kembali',
        'primary'    => [
            'url'   => route('order-manual-sertifikat.manual.create'),
            'label' => 'Create Manual',
            'icon'  => 'plus',
        ],
    ])

    {{-- ============ TOOLBAR TAMBAHAN ============ --}}
    <div class="flex flex-wrap gap-3 mb-6">
        <form action="{{ route('order-manual-sertifikat.manual.sync') }}" method="POST"
              onsubmit="return confirm('Yakin ingin sync data dari Bimba Shop + Casdana?\n\nData Manual Sertifikat yang cocok (no cabang + nama unit + produk) akan di-update: Order Date, Payment Date, Estimasi Print PL, dll.')">
            @csrf
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 12a9 9 0 0 1 15-6.7L21 8M21 3v5h-5"/>
                    <path d="M21 12a9 9 0 0 1-15 6.7L3 16M3 21v-5h5"/>
                </svg>
                Sync Bimba Shop + Casdana
            </button>
        </form>

        <a href="{{ route('order-manual-sertifikat.realisasi') }}"
           class="inline-flex items-center gap-2 bg-navy-700 hover:bg-navy-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3v18h18"/>
                <path d="M7 16v-5M12 16V8M17 16v-9"/>
            </svg>
            Rekap Aktual Manual
        </a>
    </div>

    {{-- ============ BULK ACTION BAR ============ --}}
    <div id="bulkActionBar" class="hidden bg-white rounded-2xl shadow-card p-5 mb-6 flex items-center justify-between border border-navy-700/10 flex-wrap gap-3">
        <span id="selectedCount" class="text-sm font-semibold text-navy-950">Siap memproses data sesuai filter tanggal</span>
        <div class="flex items-center gap-3">
            <button type="button" onclick="processAllFilteredData()" id="processAllBtn"
                    class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>
                </svg>
                Proses & Edit Semua Sesuai Filter Tanggal
            </button>
            <button type="button" onclick="clearSelection()"
                    class="bg-navy-950/10 hover:bg-navy-950/20 text-navy-950/70 px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                Reset
            </button>
        </div>
    </div>

    {{-- ============ FILTER ============ --}}
    <div class="bg-white rounded-2xl shadow-card p-6 mb-6">
        <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
            <div>
                <label class="field-label">Order ID</label>
                <input type="text" name="order_id" value="{{ request('order_id') }}" class="filter-input" placeholder="Cari Order ID...">
            </div>
            <div>
                <label class="field-label">Customer</label>
                <input type="text" name="customer_name" value="{{ request('customer_name') }}" class="filter-input" placeholder="Nama Customer...">
            </div>
            <div>
                <label class="field-label">Item Name</label>
                <input type="text" name="product_name" value="{{ request('product_name') }}" class="filter-input" placeholder="Nama Produk...">
            </div>
            <div>
                <label class="field-label">SKU</label>
                <input type="text" name="product_sku" value="{{ request('product_sku') }}" class="filter-input" placeholder="SKU...">
            </div>
            <div>
                <label class="field-label">Payment Method</label>
                <select name="payment_method" class="filter-input">
                    <option value="">Semua</option>
                    <option value="cash" @selected(request('payment_method') == 'cash')>Cash</option>
                    <option value="transfer" @selected(request('payment_method') == 'transfer')>Transfer</option>
                    <option value="manual" @selected(request('payment_method') == 'manual')>Manual</option>
                </select>
            </div>
            <div>
                <label class="field-label">Status</label>
                <select name="status" class="filter-input">
                    <option value="">Semua Status</option>
                    <option value="pending" @selected(request('status') == 'pending')>Pending</option>
                    <option value="processing" @selected(request('status') == 'processing')>Processing</option>
                    <option value="completed" @selected(request('status') == 'completed')>Completed</option>
                </select>
            </div>
            <div>
                <label class="field-label">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="filter-input">
            </div>
            <div>
                <label class="field-label">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="filter-input">
            </div>
            <div>
                <label class="field-label">Tampilkan</label>
                <select name="per_page" onchange="this.form.submit()" class="filter-input">
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" @selected((int)request('per_page', 25) === $n)>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3 lg:col-span-2">
                <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 bg-navy-700 hover:bg-navy-800 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    Terapkan Filter
                </button>
                <a href="{{ route('order-manual-sertifikat.manual') }}" class="text-navy-950/50 hover:text-rust-600 px-3 py-2.5 text-sm font-medium">Reset</a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="bg-white rounded-2xl shadow-card overflow-x-auto">
        <table class="w-full text-sm data-table">
            <thead>
                <tr class="text-left text-xs font-semibold text-navy-950/70 border-b-2 border-navy-950/10">
                    <th class="px-4 py-3">ID Manual</th>
                    <th class="px-4 py-3">ID Pesan</th>
                    <th class="px-4 py-3">Nama Unit</th>
                    <th class="px-4 py-3">Cabang</th>
                    <th class="px-4 py-3">Alamat Kirim</th>
                    <th class="px-4 py-3">Kab/Kota</th>
                    <th class="px-4 py-3">Kategori Pesanan</th>
                    <th class="px-4 py-3 text-center">Qty</th>
                    <th class="px-4 py-3">Order Date</th>
                    <th class="px-4 py-3">Payment Date</th>
                    <th class="px-4 py-3 text-center">Status Bayar</th>
                    <th class="px-4 py-3 text-center">Status biMBAShop</th>
                    <th class="px-4 py-3 text-center">Estimasi Print PL</th>
                    <th class="px-4 py-3 text-center">Estimasi Persiapan</th>
                    <th class="px-4 py-3">Jasa Kurir</th>
                    <th class="px-4 py-3">Service Kurir</th>
                    <th class="px-4 py-3">Distribusi</th>
                    <th class="px-4 py-3 text-right">Ship Total</th>
                    <th class="px-4 py-3 text-right">Berat (gr)</th>
                    <th class="px-4 py-3 text-right">Order Total</th>
                    <th class="px-4 py-3">Payment Channel</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-center">Tanggal Proses</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-navy-950/5">
                @forelse($manualOrders as $order)
                    @php
                        $isProcessed = (bool) ($order->is_processed ?? false);
                        $paymentDate = $order->payment_date ? \Carbon\Carbon::parse($order->payment_date) : null;
                        $estimasiPrint = $order->estimasi_print_pl ? \Carbon\Carbon::parse($order->estimasi_print_pl) : null;
                        $estimasiPersiapan = $order->estimasi_persiapan ? \Carbon\Carbon::parse($order->estimasi_persiapan) : null;
                        $status = strtolower($order->status ?? 'pending');
                        $statusClass = match($status) {
                            'completed'  => 'bg-emerald-100 text-emerald-700',
                            'processing' => 'bg-blue-100 text-blue-700',
                            default      => 'bg-amber-100 text-amber-800',
                        };
                        $namaUnit = $order->customer_name ?? '-';
                        $kategori = $order->product_name ?? $order->product_sku ?? '-';
                        $noCabItem = trim($order->billing_last_name ?? '');
                        $mismatch = $mismatchMap[$noCabItem] ?? null;
                        $isMismatch = $mismatch
                            || str_contains($order->catatan ?? '', 'NAMA_MISMATCH')
                            || str_contains($order->notes ?? '', 'NAMA_MISMATCH');
                    @endphp
                    <tr class="{{ $isProcessed ? 'processed-row' : 'hover:bg-navy-950/[0.02]' }}">
                        <td class="px-4 py-3 font-semibold text-navy-950 whitespace-nowrap">
                            MS-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-navy-700 whitespace-nowrap">{{ $order->order_id ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold text-navy-950">
                            <span class="cell-clip" title="{{ $namaUnit }}">{{ $namaUnit }}</span>
                            @if($isMismatch)
                                <div class="text-xs mt-1 space-y-0.5">
                                    @if($mismatch)
                                        <div class="text-orange-700"><span class="text-navy-950/50">Excel:</span> {{ $mismatch['nama_excel'] }}</div>
                                        <div class="text-emerald-700"><span class="text-navy-950/50">Kemitraan:</span> {{ $mismatch['nama_master'] }}</div>
                                    @endif
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                        <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v4m0 4h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/></svg>
                                        Mismatch
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->billing_last_name ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="cell-clip" title="{{ $order->shipping_address_1 }}">{{ $order->shipping_address_1 ?? '-' }}</span></td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->shipping_city ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="cell-clip" title="{{ $kategori }}">{{ $kategori }}</span></td>
                        <td class="px-4 py-3 text-center font-semibold">{{ $order->qty ?? 0 }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($paymentDate)
                                {{ $paymentDate->format('d/m/Y H:i') }}
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @php $statusBayar = strtoupper($order->status_bayar ?? ''); @endphp
                            @if(in_array($statusBayar, ['SUCCESS', 'SETTLED', 'PAID']))
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">{{ $statusBayar }}</span>
                            @elseif($statusBayar)
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">{{ $statusBayar }}</span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-navy-950/5 text-navy-950/40">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
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
                        <td class="px-4 py-3 text-center whitespace-nowrap">{{ $estimasiPrint ? $estimasiPrint->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">{{ $estimasiPersiapan ? $estimasiPersiapan->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3">{{ $order->ekspedisi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $order->service_pengiriman ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $order->status_kirim ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($order->ship_total ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($order->order_weight ?? 0) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($order->total ?? $order->price ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $order->payment_method ?? 'manual' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            {{ $order->processed_at ? \Carbon\Carbon::parse($order->processed_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($isProcessed)
                                <span class="inline-flex text-navy-950/25 cursor-not-allowed" title="Data sudah diproses / dikunci">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="10" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg>
                                </span>
                            @else
                                <a href="{{ route('order-manual-sertifikat.manual.edit', $order->id) }}"
                                   class="inline-flex text-navy-700 hover:text-rust-600" title="Edit">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                    </svg>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="24" class="text-center py-16 text-navy-950/40">
                            Belum ada data pemesanan sertifikat.<br>
                            Silakan <strong>Create Manual</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($manualOrders->total() > 0)
            <div class="px-6 py-4 bg-white border-t border-navy-950/10 flex items-center justify-between flex-wrap gap-3">
                <div class="text-sm text-navy-950/60">
                    Menampilkan <span class="font-medium text-navy-950">{{ $manualOrders->firstItem() }}</span>
                    sampai <span class="font-medium text-navy-950">{{ $manualOrders->lastItem() }}</span>
                    dari total <span class="font-medium text-navy-950">{{ $manualOrders->total() }}</span> data
                </div>
                <div class="pager">{{ $manualOrders->appends(request()->query())->links('pagination::tailwind') }}</div>
            </div>
        @endif
    </div>

    {{-- ============ MODAL BULK ============ --}}
    <div id="bulkModal" class="hidden fixed inset-0 bg-navy-950/60 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-2xl w-[98vw] h-[95vh] mx-2 flex flex-col">
            <div class="p-6 border-b border-navy-950/10 flex justify-between items-center">
                <div>
                    <h3 class="text-2xl font-semibold text-navy-950">Edit & Proses Data Sertifikat</h3>
                    <p class="text-navy-950/60" id="modalCount">0 data dipilih</p>
                </div>
                <button type="button" onclick="hideBulkModal()" class="text-navy-950/50 hover:text-rust-600">
                    <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-auto p-6">
                <table class="w-full text-sm border border-navy-950/10 min-w-[1600px] data-table">
                    <thead>
                        <tr class="bg-navy-950/[0.03] divide-x divide-navy-950/10 text-left text-xs font-semibold text-navy-950/70">
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">ID Pesan</th>
                            <th class="px-4 py-3">To Customer</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Payment Date</th>
                            <th class="px-4 py-3">Payment Channel</th>
                            <th class="px-4 py-3">Distribusi</th>
                            <th class="px-4 py-3">Jasa Kurir *</th>
                            <th class="px-4 py-3">Service</th>
                            <th class="px-4 py-3">Catatan</th>
                        </tr>
                    </thead>
                    <tbody id="modalTableBody" class="divide-y divide-navy-950/10"></tbody>
                </table>
            </div>
            <div class="p-6 border-t border-navy-950/10 bg-navy-950/[0.02] flex justify-end gap-3">
                <button type="button" onclick="hideBulkModal()" class="px-6 py-3 text-navy-950/60 hover:bg-navy-950/5 rounded-xl">Batal</button>
                <button type="button" id="saveBulkBtn" onclick="executeBulkAction()" class="bg-rust-600 hover:bg-rust-500 text-white px-8 py-3 rounded-xl font-semibold transition-colors">
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
const filteredIdsUrl = @json(route('order-manual-sertifikat.manual.filtered-ids'));
const modalDataUrl   = @json(route('order-manual-sertifikat.manual.get-modal-data'));
const bulkActionUrl  = @json(route('order-manual-sertifikat.manual.bulk-action'));
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
                tr.className = isLocked ? 'processed-row' : 'hover:bg-navy-950/[0.02]';

                const distribusiBadge = `<span class="inline-flex px-4 py-2.5 text-sm font-semibold rounded-xl ${isLocked ? 'bg-emerald-100 text-emerald-700' : 'bg-blue-100 text-blue-700'}">${distribusi}</span>`;
                const paymentDateHtml = item.payment_date || '<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>';

                let jasaHtml, serviceHtml, catatanHtml;
                if (isLocked) {
                    jasaHtml = '<span class="text-sm text-navy-950/40">— Terkunci —</span>';
                    serviceHtml = '<span class="text-sm text-navy-950/40">— Terkunci —</span>';
                    catatanHtml = `<span class="text-xs text-navy-950/40 italic">Sudah diproses ${item.processed_at || ''}</span>`;
                } else {
                    const jasa = item.jasa_kurir || 'Lion Parcel';
                    const disabled = distribusi === 'Diambil' ? 'disabled' : '';
                    jasaHtml = `<input type="text" class="jasa-kurir-input w-full border border-navy-950/15 rounded-xl px-3 py-2.5 text-sm" list="jasaKurirOptions" value="${distribusi === 'Diambil' ? 'Diambil Sendiri' : jasa}" ${disabled}>`;
                    serviceHtml = serviceFieldHtml(distribusi === 'Diambil' ? '' : jasa, item.service_kurir, disabled);
                    catatanHtml = '<input type="text" class="catatan w-full border border-navy-950/15 rounded-xl px-3 py-2.5 text-sm" placeholder="Catatan...">';
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
        return `<select class="service-kurir w-full border border-navy-950/15 rounded-xl px-3 py-2.5 text-sm" ${disabled}>
            ${opts.map(o => `<option value="${o}" ${o === sel ? 'selected' : ''}>${o}</option>`).join('')}
        </select>`;
    }
    if (jasa === 'JNE' || jasa === 'TIKI') {
        return `<input type="text" class="service-kurir w-full border border-navy-950/15 rounded-xl px-3 py-2.5 text-sm" value="${currentValue || 'REG'}" ${disabled}>`;
    }
    return `<select class="service-kurir w-full border border-navy-950/15 rounded-xl px-3 py-2.5 text-sm" ${disabled}><option value="">Pilih Service</option></select>`;
}

function initModalLogic() {
    document.querySelectorAll('#modalTableBody tr').forEach(row => {
        const jasaInput = row.querySelector('.jasa-kurir-input');
        if (!jasaInput || jasaInput.disabled) return;

        jasaInput.addEventListener('input', () => {
            const cell = row.querySelector('.service-cell');
            cell.innerHTML = serviceFieldHtml(jasaInput.value.trim());
            checkSaveButtonState();
        });
        jasaInput.addEventListener('change', () => {
            const cell = row.querySelector('.service-cell');
            cell.innerHTML = serviceFieldHtml(jasaInput.value.trim());
            checkSaveButtonState();
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