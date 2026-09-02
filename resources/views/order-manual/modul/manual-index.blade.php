<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Pemesanan Modul - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        .processed-row { opacity: 0.65; background-color: #f1f5f9 !important; color: #64748b; }
        .processed-row td { color: #64748b; }
        .select2-container--default .select2-selection--single,
        .select2-container--bootstrap-5 .select2-selection--single {
            height: 42px !important; border: 1px solid #d1d5db !important;
            border-radius: 0.75rem !important; padding: 0.4rem 0.75rem !important;
            display: flex !important; align-items: center !important; background-color: #fff !important;
        }
        .select2-dropdown { border-radius: 0.75rem !important; border: 1px solid #d1d5db !important; }
    </style>
</head>
<body class="bg-gray-50">
@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-2xl">{!! session('success') !!}</div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl">{!! session('error') !!}</div>
    @endif

    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manual Pemesanan Modul</h1>
            <p class="text-gray-600">Kelola data pemesanan modul secara manual</p>
        </div>


        <div class="flex gap-3 flex-wrap">
    <a href="{{ route('order-manual-modul.index') }}"
       class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">← Kembali</a>

    {{-- TOMBOL SYNC BARU --}}
    <form action="{{ route('order-manual-modul.manual.sync') }}" method="POST" 
          onsubmit="return confirm('Yakin ingin sync data dari Bimba Shop + Casdana?\n\nData Manual Modul yang cocok (no cabang + nama unit + produk) akan di-update: Order Date, Payment Date, Estimasi Print PL, dll.')">
        @csrf
        <button type="submit"
                class="bg-teal-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-teal-700 flex items-center gap-2">
            🔄 Sync Bimba Shop + Casdana
        </button>
    </form>

    <a href="{{ route('order-manual-modul.realisasi') }}"
   class="bg-emerald-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-emerald-700">
    📋 Rekap Aktual
</a>

    <a href="{{ route('order-manual-modul.manual.create') }}"
       class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-indigo-700">+ Create Manual</a>
</div>
    </div>

    <div id="bulkActionBar" class="hidden bg-white rounded-3xl shadow p-5 mb-6 flex items-center justify-between border border-indigo-100 flex-wrap gap-3">
        <span id="selectedCount" class="text-sm font-semibold text-gray-700">Siap memproses data sesuai filter tanggal</span>
        <div class="flex items-center gap-3">
            <button type="button" onclick="processAllFilteredData()" id="processAllBtn"
                    class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-emerald-700">
                📅 Proses & Edit Semua Sesuai Filter Tanggal
            </button>
            <button type="button" onclick="clearSelection()" class="bg-gray-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-600">Reset</button>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow p-6 mb-6">
        <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                <input type="text" name="order_id" value="{{ request('order_id') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Cari Order ID...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <input type="text" name="customer_name" value="{{ request('customer_name') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Nama Customer...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                <input type="text" name="product_name" value="{{ request('product_name') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Nama Produk...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                <input type="text" name="product_sku" value="{{ request('product_sku') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="SKU...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select name="payment_method" class="payment-select w-full">
                    <option value="">Semua</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="manual" {{ request('payment_method') == 'manual' ? 'selected' : '' }}>Manual</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="status-select w-full">
                    <option value="">Semua Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tampilkan</label>
                <select name="per_page" onchange="this.form.submit()" class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    @foreach([10,25,50,100] as $n)
                        <option value="{{ $n }}" {{ (int)request('per_page', 25) === $n ? 'selected' : '' }}>{{ $n }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">🔍 Terapkan Filter</button>
                <a href="{{ route('order-manual-modul.manual') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5 text-sm font-medium">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="text-left px-4 py-3">ID Manual</th>
                    <th class="text-left px-4 py-3">ID Pesan</th>
                    <th class="text-left px-4 py-3">Nama Unit</th>
                    <th class="text-left px-4 py-3">Cabang</th>
                    <th class="text-left px-4 py-3">Alamat Kirim</th>
                    <th class="text-left px-4 py-3">Kab/Kota</th>
                    <th class="text-left px-4 py-3">Kategori Pesanan</th>
                    <th class="text-center px-4 py-3">Qty</th>
                    <th class="text-left px-4 py-3">Order Date</th>
                    <th class="text-left px-4 py-3">Payment Date</th>
                    <th class="text-center px-4 py-3">Status Bayar</th>          {{-- BARU --}}
                    <th class="text-center px-4 py-3">Status biMBAShop</th>      {{-- BARU --}}
                    <th class="text-center px-4 py-3">Estimasi Print PL</th>
                    <th class="text-center px-4 py-3">Estimasi Persiapan</th>
                    <th class="text-left px-4 py-3">Jasa Kurir</th>
                    <th class="text-left px-4 py-3">Service Kurir</th>
                    <th class="text-left px-4 py-3">Distribusi</th>
                    <th class="text-right px-4 py-3">Ship Total</th>
                    <th class="text-right px-4 py-3">Berat (gr)</th>
                    <th class="text-right px-4 py-3">Order Total</th>
                    <th class="text-left px-4 py-3">Payment Channel</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-center px-4 py-3">Tanggal Proses</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
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
                    <tr class="{{ $isProcessed ? 'processed-row' : '' }} hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-gray-700 whitespace-nowrap">
                            MM-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-indigo-700 whitespace-nowrap">{{ $order->order_id ?? '-' }}</td>
                        <td class="px-4 py-3 font-semibold text-gray-800">
                            <div class="flex flex-col gap-0.5">
                                <span>{{ $namaUnit }}</span>
                                @if($isMismatch && $mismatch)
                                    <div class="text-xs mt-0.5 space-y-0.5">
                                        <div class="text-orange-700"><span class="text-gray-500">Excel:</span> {{ $mismatch['nama_excel'] }}</div>
                                        <div class="text-emerald-700"><span class="text-gray-500">Kemitraan:</span> {{ $mismatch['nama_master'] }}</div>
                                    </div>
                                    <span class="inline-flex self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">⚠️ Mismatch</span>
                                @elseif($isMismatch)
                                    <span class="inline-flex self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">⚠️ Mismatch</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->billing_last_name ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="block whitespace-normal break-words">{{ $order->shipping_address_1 ?? '-' }}</span></td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->shipping_city ?? '-' }}</td>
                        <td class="px-4 py-3"><span class="block whitespace-normal break-words">{{ $kategori }}</span></td>
                        <td class="px-4 py-3 text-center font-semibold">{{ $order->qty ?? 0 }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($paymentDate)
                                {{ $paymentDate->format('d/m/Y H:i') }}
                            @else
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                            @endif
                        </td>
                        {{-- Status Bayar (dari Casdana) --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @php
                                $statusBayar = strtoupper($order->status_bayar ?? '');
                            @endphp
                            @if($statusBayar === 'SUCCESS' || $statusBayar === 'SETTLED' || $statusBayar === 'PAID')
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                    {{ $statusBayar }}
                                </span>
                            @elseif($statusBayar)
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    {{ $statusBayar }}
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                    -
                                </span>
                            @endif
                        </td>

                        {{-- Status biMBAShop --}}
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @php
                                $statusBimba = strtolower($order->status_bimbashop ?? '');
                                $bimbaClass = match($statusBimba) {
                                    'completed'  => 'bg-emerald-100 text-emerald-700',
                                    'processing' => 'bg-blue-100 text-blue-700',
                                    'on-hold'    => 'bg-orange-100 text-orange-700',
                                    'pending'    => 'bg-amber-100 text-amber-800',
                                    default      => 'bg-gray-100 text-gray-500',
                                };
                            @endphp
                            @if($statusBimba)
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $bimbaClass }}">
                                    {{ ucfirst($statusBimba) }}
                                </span>
                            @else
                                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">{{ $estimasiPrint ? $estimasiPrint->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">{{ $estimasiPersiapan ? $estimasiPersiapan->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-3">{{ $order->ekspedisi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $order->service_pengiriman ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $order->status_kirim ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($order->ship_total ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($order->order_weight ?? 0) }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($order->total ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">{{ $order->payment_method ?? 'manual' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            {{ $order->processed_at ? \Carbon\Carbon::parse($order->processed_at)->format('d/m/Y H:i') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            @if($isProcessed)
                                <span class="text-gray-400 cursor-not-allowed font-medium" title="Data sudah diproses / dikunci">Edit</span>
                            @else
                                <a href="{{ route('order-manual-modul.manual.edit', $order->id) }}"
                                class="text-blue-600 hover:text-blue-800 font-medium">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="24" class="text-center py-16 text-gray-500">
                            Belum ada data pemesanan modul.<br>
                            Silakan <strong>Create Manual</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($manualOrders->total() > 0)
            <div class="px-6 py-4 bg-white border-t flex items-center justify-between flex-wrap gap-3">
                <div class="text-sm text-gray-700">
                    Menampilkan <span class="font-medium">{{ $manualOrders->firstItem() }}</span>
                    sampai <span class="font-medium">{{ $manualOrders->lastItem() }}</span>
                    dari total <span class="font-medium">{{ $manualOrders->total() }}</span> data
                </div>
                <div>{{ $manualOrders->appends(request()->query())->links() }}</div>
            </div>
        @endif
    </div>
</div>

{{-- MODAL BULK --}}
<div id="bulkModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl w-[98vw] h-[95vh] mx-2 flex flex-col">
        <div class="p-6 border-b flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-semibold">Edit & Proses Data Modul</h3>
                <p class="text-gray-600" id="modalCount">0 data dipilih</p>
            </div>
            <button type="button" onclick="hideBulkModal()" class="text-3xl text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="flex-1 overflow-auto p-6">
            <table class="w-full text-sm border border-gray-200 min-w-[1600px]">
                <thead class="bg-gray-50 sticky top-0">
                    <tr class="divide-x divide-gray-200">
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
                <tbody id="modalTableBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
        <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
            <button type="button" onclick="hideBulkModal()" class="px-6 py-3 text-gray-600 hover:bg-gray-100 rounded-2xl">Batal</button>
            <button type="button" onclick="executeBulkAction()" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-indigo-700">
                💾 Simpan & Kunci Semua Data
            </button>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let selectedIds = [];

function checkFilterStatus() {
    const startDate = $('input[name="start_date"]').val();
    const endDate = $('input[name="end_date"]').val();
    if (startDate && endDate) $('#bulkActionBar').removeClass('hidden');
    else $('#bulkActionBar').addClass('hidden');
}

function checkProcessButtonVisibility() {
    const processBtn = document.getElementById('processAllBtn');
    if (!processBtn) return;
    let unprocessedCount = 0;
    document.querySelectorAll('tbody tr').forEach(row => {
        if (!row.classList.contains('processed-row') && row.querySelector('td')) unprocessedCount++;
    });
    if (unprocessedCount === 0) {
        processBtn.style.display = 'none';
        $('#selectedCount').html('✅ <span class="text-emerald-600 font-medium">Semua data pada filter ini sudah diproses</span>');
    } else {
        processBtn.style.display = 'inline-flex';
        $('#selectedCount').text(`Siap memproses data sesuai filter tanggal`);
    }
}

$(document).ready(function () {
    $('.payment-select, .status-select').select2({
        theme: 'bootstrap-5', placeholder: 'Pilih...', allowClear: true, width: '100%'
    });
    checkFilterStatus();
    checkProcessButtonVisibility();
    $('input[name="start_date"], input[name="end_date"]').on('change', function () {
        checkFilterStatus();
        setTimeout(checkProcessButtonVisibility, 500);
    });
});

function processAllFilteredData() {
    const startDate = $('input[name="start_date"]').val();
    const endDate = $('input[name="end_date"]').val();
    if (!startDate || !endDate) {
        alert('❌ Harap isi Dari Tanggal dan Sampai Tanggal!');
        return;
    }
    $.ajax({
        url: '{{ route("order-manual-modul.manual.filtered-ids") }}',
        method: 'GET',
        data: {
            start_date: startDate,
            end_date: endDate,
            order_id: $('input[name="order_id"]').val() || '',
            customer_name: $('input[name="customer_name"]').val() || '',
            product_name: $('input[name="product_name"]').val() || '',
            product_sku: $('input[name="product_sku"]').val() || '',
            status: $('select[name="status"]').val() || '',
            payment_method: $('select[name="payment_method"]').val() || '',
        },
        success: function (response) {
            if (response.count === 0) {
                alert('Tidak ada data yang belum diproses.');
                return;
            }
            selectedIds = response.ids;
            $('#selectedCount').text(response.count + ' data akan diproses');
            loadModalData();
        },
        error: function () { alert('Gagal mengambil data.'); }
    });
}

function loadModalData() {
    $.ajax({
        url: '{{ route("order-manual-modul.manual.get-modal-data") }}',
        method: 'POST',
        data: { ids: selectedIds, _token: '{{ csrf_token() }}' },
        success: function (items) {
            let html = '';
            items.forEach(item => {
                const isLocked = Boolean(item.is_processed);
                const currentDistribusi = (item.status_kirim || 'Dikirim').trim();
                let distribusiHtml, jasaKurirHtml, serviceKurirHtml, catatanHtml;

                if (isLocked) {
                    distribusiHtml = `<span class="inline-flex px-4 py-2.5 text-sm font-semibold bg-emerald-100 text-emerald-700 rounded-2xl">${currentDistribusi}</span>`;
                    jasaKurirHtml = `<span class="text-sm text-gray-500">— Terkunci —</span>`;
                    serviceKurirHtml = `<span class="text-sm text-gray-500">— Terkunci —</span>`;
                    catatanHtml = `<span class="text-xs text-gray-500 italic">Sudah diproses ${item.processed_at || ''}</span>`;
                } else {
                    distribusiHtml = `<span class="inline-flex px-4 py-2.5 text-sm font-semibold bg-blue-100 text-blue-700 rounded-2xl">${currentDistribusi}</span>`;
                    const selectedJasa = item.jasa_kurir || 'Lion Parcel';
                    const selectedService = item.service_kurir || 'REGPACK';
                    jasaKurirHtml = `
                        <select class="jasa-kurir-select w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                            <option value="">Pilih jasa kurir...</option>
                            <option value="JNE" ${selectedJasa === 'JNE' ? 'selected' : ''}>JNE</option>
                            <option value="TIKI" ${selectedJasa === 'TIKI' ? 'selected' : ''}>TIKI</option>
                            <option value="Lion Parcel" ${selectedJasa === 'Lion Parcel' ? 'selected' : ''}>Lion Parcel</option>
                        </select>`;
                    serviceKurirHtml = `
                        <select class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                            <option value="">Pilih Service</option>
                            <option value="REGPACK" ${selectedService === 'REGPACK' ? 'selected' : ''}>REGPACK</option>
                            <option value="BOSPACK" ${selectedService === 'BOSPACK' ? 'selected' : ''}>BOSPACK</option>
                            <option value="JAGOPACK" ${selectedService === 'JAGOPACK' ? 'selected' : ''}>JAGOPACK</option>
                            <option value="BIGPACK" ${selectedService === 'BIGPACK' ? 'selected' : ''}>BIGPACK</option>
                        </select>`;
                    catatanHtml = `<input type="text" class="catatan w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" placeholder="Catatan...">`;
                }

                const paymentDateHtml = item.payment_date || `<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>`;

                html += `<tr data-id="${item.id}" data-distribusi="${currentDistribusi}" class="${isLocked ? 'processed-row' : 'hover:bg-gray-50'}">
                    <td class="px-4 py-3">${item.status_pembayaran || '-'}</td>
                    <td class="px-4 py-3 font-medium">${item.invoice}</td>
                    <td class="px-4 py-3">${item.to_customer}</td>
                    <td class="px-4 py-3">${item.pesanan || '-'}</td>
                    <td class="px-4 py-3">${paymentDateHtml}</td>
                    <td class="px-4 py-3 text-blue-700">${item.payment_channel}</td>
                    <td class="px-4 py-3">${distribusiHtml}</td>
                    <td class="px-4 py-3">${jasaKurirHtml}</td>
                    <td class="px-4 py-3">${serviceKurirHtml}</td>
                    <td class="px-4 py-3">${catatanHtml}</td>
                </tr>`;
            });
            $('#modalTableBody').html(html);
            $('#modalCount').text(items.length + ' data dipilih');
            $('#bulkModal').removeClass('hidden');
            initModalLogic();
        },
        error: function () { alert('Gagal memuat data ke modal.'); }
    });
}

function initModalLogic() {
    $('.jasa-kurir-select').select2({
        placeholder: 'Pilih jasa kurir...', allowClear: true, tags: true, width: '100%',
        dropdownParent: $('#bulkModal')
    }).on('change', function () {
        const row = $(this).closest('tr');
        const jasa = $(this).val();
        let serviceField = row.find('.service-kurir');
        if (jasa === 'Lion Parcel') {
            serviceField.replaceWith(`
                <select class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                    <option value="REGPACK" selected>REGPACK</option>
                    <option value="BOSPACK">BOSPACK</option>
                    <option value="JAGOPACK">JAGOPACK</option>
                    <option value="BIGPACK">BIGPACK</option>
                </select>`);
        } else if (jasa === 'JNE' || jasa === 'TIKI') {
            serviceField.replaceWith(`<input type="text" class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" value="REG">`);
        }
        checkSaveButtonState();
    });

    $('#modalTableBody tr:not(.processed-row)').each(function () {
        const row = $(this);
        if (row.data('distribusi') === 'Diambil') {
            const jasaSelect = row.find('.jasa-kurir-select');
            if (jasaSelect.find('option[value="Diambil Sendiri"]').length === 0) {
                jasaSelect.append('<option value="Diambil Sendiri">Diambil Sendiri</option>');
            }
            jasaSelect.val('Diambil Sendiri').trigger('change').prop('disabled', true);
            row.find('.service-kurir').prop('disabled', true).val('');
        }
    });

    function checkSaveButtonState() {
        let isValid = true;
        $('#modalTableBody tr:not(.processed-row)').each(function () {
            const row = $(this);
            let jasaKurir = row.find('.jasa-kurir-select').val() || '';
            const serviceField = row.find('.service-kurir');
            let serviceValue = serviceField.is('select') ? (serviceField.val() || '') : $.trim(serviceField.val() || '');
            const distribusi = row.data('distribusi');
            if (distribusi === 'Diambil') jasaKurir = 'Diambil Sendiri';
            if (!jasaKurir) { isValid = false; return false; }
            if (distribusi === 'Dikirim' && !serviceValue) { isValid = false; return false; }
        });
        const saveButton = $('.bg-indigo-600');
        if (isValid) {
            saveButton.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed').text('💾 Simpan & Kunci Semua Data');
        } else {
            saveButton.prop('disabled', true).addClass('opacity-50 cursor-not-allowed').text('Lengkapi Jasa Kurir & Service');
        }
    }
    $(document).off('input change', '.service-kurir').on('input change', '.service-kurir', checkSaveButtonState);
    setTimeout(checkSaveButtonState, 400);
}

function hideBulkModal() {
    $('#bulkModal').addClass('hidden');
    try { $('.jasa-kurir-select').select2('destroy'); } catch (e) {}
}

function executeBulkAction() {
    if ($('.bg-indigo-600').prop('disabled')) {
        alert('❌ Lengkapi Jasa Kurir dan Service!');
        return;
    }
    if (!confirm('Yakin memproses ' + selectedIds.length + ' data?')) return;

    const updates = [];
    $('#modalTableBody tr').each(function () {
        const row = $(this);
        let distribusiText = row.data('distribusi');
        let jasaKurirText = row.find('.jasa-kurir-select').val() || '';
        if (distribusiText === 'Diambil') jasaKurirText = 'Diambil Sendiri';
        updates.push({
            id: row.data('id'),
            status_kirim: distribusiText,
            jasa_kurir: jasaKurirText,
            service_kurir: row.find('.service-kurir').val() || '',
            catatan: row.find('.catatan').val() || ''
        });
    });

    const form = $('<form>', { action: '{{ route("order-manual-modul.manual.bulk-action") }}', method: 'POST' });
    $('<input>', { type: 'hidden', name: '_token', value: '{{ csrf_token() }}' }).appendTo(form);
    $('<input>', { type: 'hidden', name: 'action', value: 'processed' }).appendTo(form);
    $('<input>', { type: 'hidden', name: 'per_item', value: JSON.stringify(updates) }).appendTo(form);
    form.appendTo('body').submit();
}

function clearSelection() {
    selectedIds = [];
    checkProcessButtonVisibility();
}
</script>
</body>
</html>