<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Pemesanan - biMBA AIUEO Logistik</title>

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
        .truncate { max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .processed-row {
            opacity: 0.65;
            background-color: #f1f5f9 !important;
            color: #64748b;
        }
        .processed-row td { color: #64748b; }

        .badge-green { background-color: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-yellow { background-color: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-red { background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-black { background-color: #1f2937; color: #f3f4f6; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
    </style>
</head>
<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    {{-- FLASH --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-4 rounded-2xl">
            {!! session('success') !!}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-2xl">
            {!! session('error') !!}
        </div>
    @endif

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6 flex-wrap gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manual Pemesanan</h1>
            <p class="text-gray-600">Kelola data pemesanan manual</p>
        </div>

        <div class="flex gap-3 flex-wrap">
            <a href="{{ route('order-manual.index') }}"
               class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
                ← Kembali ke Daftar Import
            </a>

            <a href="{{ route('import.manual-printed') }}"
                 class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700 flex items-center gap-2">
                📊 Rekap Aktual Manual
            </a>

            <form action="{{ route('import.sync-pesanan-majalah') }}"
                  method="POST"
                  onsubmit="return confirm('Yakin Sync semua Pesanan Majalah ke Manual Pemesanan?')">
                @csrf
                <button type="submit"
                        class="bg-pink-700 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-pink-800 flex items-center gap-2">
                    🔄 Sync Pesanan Majalah
                </button>
            </form>
        </div>
    </div>

    {{-- FORM IMPORT --}}
    <div id="importForm" class="hidden bg-white rounded-3xl shadow p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Upload File Excel / CSV</h2>
        <form action="{{ route('import.manual.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex gap-4 items-end flex-wrap">
                <div class="flex-1 min-w-[240px]">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                    <input type="file" name="import_file"
                           class="block w-full text-sm text-gray-500
                                  file:mr-4 file:py-3 file:px-6 file:rounded-2xl
                                  file:border-0 file:text-sm file:font-semibold
                                  file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                           accept=".xlsx,.xls,.csv" required>
                </div>
                <button type="submit"
                        class="bg-emerald-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-emerald-700">
                    🚀 Import Sekarang
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-2">Format yang didukung: .xlsx, .xls, .csv (max 10MB)</p>
        </form>
    </div>

    {{-- BULK ACTION BAR --}}
    <div id="bulkActionBar"
         class="hidden bg-white rounded-3xl shadow p-5 mb-6 flex items-center justify-between border border-indigo-100 flex-wrap gap-3">
        <div>
            <span id="selectedCount" class="text-sm font-semibold text-gray-700">
                Siap memproses data sesuai filter tanggal
            </span>
        </div>
        <div class="flex items-center gap-3">
            <button type="button"
                    onclick="processAllFilteredData()"
                    id="processAllBtn"
                    class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-emerald-700 flex items-center gap-2">
                📅 Proses & Edit Semua Sesuai Filter Tanggal
            </button>
            <button type="button"
                    onclick="clearSelection()"
                    class="bg-gray-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-600">
                Reset
            </button>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="bg-white rounded-3xl shadow p-6 mb-6">
        <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                <input type="text" name="order_id" value="{{ request('order_id') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5"
                       placeholder="Cari Order ID...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                <input type="text" name="customer_name" value="{{ request('customer_name') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5"
                       placeholder="Nama Customer...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                <input type="text" name="product_name" value="{{ request('product_name') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5"
                       placeholder="Nama Produk...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                <input type="text" name="product_sku" value="{{ request('product_sku') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5"
                       placeholder="SKU...">
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
                <input type="date" name="start_date" value="{{ request('start_date') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}"
                       class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tampilkan</label>
                <select name="per_page" onchange="this.form.submit()"
                        class="w-full border border-gray-300 rounded-xl px-4 py-2.5">
                    <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </div>
            <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">
                    🔍 Terapkan Filter
                </button>
                <a href="{{ route('import.manual') }}"
                   class="text-gray-500 hover:text-red-600 px-4 py-2.5 text-sm font-medium whitespace-nowrap">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- TABEL --}}
    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="text-left px-4 py-3">ID Pesan</th>
                    <th class="text-left px-4 py-3">Nama Unit</th>
                    <th class="text-left px-4 py-3">Cabang</th>
                    <th class="text-left px-4 py-3">Group</th>
                    <th class="text-left px-4 py-3">Alamat Kirim</th>
                    <th class="text-left px-4 py-3">Kab/Kota</th>
                    <th class="text-left px-4 py-3">Kategori Pesanan</th>
                    <th class="text-center px-4 py-3">Qty</th>
                    <th class="text-left px-4 py-3">Order Date</th>
                    <th class="text-left px-4 py-3">Payment Date</th>
                    <th class="text-center px-4 py-3">Estimasi Print PL | PS</th>
                    <th class="text-center px-4 py-3">Estimasi Persiapan</th>
                    <th class="text-left px-4 py-3">Jasa Kurir</th>
                    <th class="text-left px-4 py-3">Service Kurir</th>
                    <th class="text-left px-4 py-3">Distribusi</th>
                    <th class="text-right px-4 py-3">Ship Total</th>
                    <th class="text-right px-4 py-3">Berat (gr)</th>
                    <th class="text-right px-4 py-3">Order Total</th>
                    <th class="text-left px-4 py-3">Payment Channel</th>
                    <th class="text-left px-4 py-3">Status Bayar</th>
                    <th class="text-left px-4 py-3">Status</th>
                    <th class="text-center px-4 py-3">Tanggal Proses</th>
                    <th class="text-center px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($manualOrders as $order)
                    @php
                        $isProcessed = (bool) ($order->is_processed ?? false);

                        $paymentDate = $order->payment_date
                            ? \Carbon\Carbon::parse($order->payment_date)
                            : null;

                        $estimasiPrint = $order->estimasi_print_pl
                            ? \Carbon\Carbon::parse($order->estimasi_print_pl)
                            : null;

                        $estimasiPersiapan = $order->estimasi_persiapan
                            ? \Carbon\Carbon::parse($order->estimasi_persiapan)
                            : null;

                        $jamPrint = $estimasiPrint
                            ? now()->diffInHours($estimasiPrint, false)
                            : 999;

                        $jamPersiapan = $estimasiPersiapan
                            ? now()->diffInHours($estimasiPersiapan, false)
                            : 999;

                        $status = strtolower($order->status ?? 'pending');
                        $statusClass = match($status) {
                            'completed'  => 'bg-emerald-100 text-emerald-700',
                            'processing' => 'bg-blue-100 text-blue-700',
                            default      => 'bg-amber-100 text-amber-800',
                        };

                        $namaUnit = $order->customer_name
                            ?? trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? ''))
                            ?: '-';

                        $kategori = $order->product_name
                            ?? $order->item_name
                            ?? $order->product_sku
                            ?? '-';
                    @endphp
                    <tr class="{{ $isProcessed ? 'processed-row' : '' }} hover:bg-gray-50">
                        <td class="px-4 py-3 font-semibold text-indigo-700 whitespace-nowrap">
                            {{ $order->order_id ?? '-' }}
                        </td>
                        <td class="px-4 py-3 font-semibold text-gray-800">
                            @php
                                $noCabItem = trim($order->billing_last_name ?? '');
                                $mismatch  = $mismatchMap[$noCabItem] ?? null;
                                $isMismatch = $mismatch
                                    || str_contains($order->catatan ?? '', 'NAMA_MISMATCH')
                                    || str_contains($order->notes ?? '', 'NAMA_MISMATCH');
                            @endphp

                            <div class="flex flex-col gap-0.5">
                                {{-- Nama yang dipakai (Excel) --}}
                                <span>{{ $namaUnit }}</span>

                                @if($isMismatch && $mismatch)
                                    <div class="text-xs font-normal mt-0.5 space-y-0.5">
                                        <div class="text-orange-700">
                                            <span class="text-gray-500">Excel:</span>
                                            <span class="font-medium">{{ $mismatch['nama_excel'] }}</span>
                                        </div>
                                        <div class="text-emerald-700">
                                            <span class="text-gray-500">Kemitraan:</span>
                                            <span class="font-medium">{{ $mismatch['nama_master'] }}</span>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                        ⚠️ Mismatch
                                    </span>
                                @elseif($isMismatch)
                                    <span class="inline-flex items-center self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200"
                                        title="Nama unit beda dengan Unit Kemitraan">
                                        ⚠️ Mismatch
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->billing_last_name ?? '-' }}</td>
                       <td class="px-4 py-3 whitespace-nowrap">
                            @php
                                $grup = strtoupper(trim($order->grup ?? ''));
                                $grupClass = match($grup) {
                                    'A'     => 'bg-blue-100 text-blue-700 border border-blue-200',
                                    'B'     => 'bg-emerald-100 text-emerald-700 border border-emerald-200',
                                    'C'     => 'bg-purple-100 text-purple-700 border border-purple-200',
                                    'D'     => 'bg-amber-100 text-amber-700 border border-amber-200',
                                    'E'     => 'bg-rose-100 text-rose-700 border border-rose-200',
                                    'F'     => 'bg-cyan-100 text-cyan-700 border border-cyan-200',
                                    default => 'bg-gray-100 text-gray-600 border border-gray-200',
                                };
                            @endphp

                            @if($grup)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $grupClass }}">
                                    Group {{ $grup }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="block whitespace-normal break-words" title="{{ $order->shipping_address_1 ?? '' }}">
                                {{ $order->shipping_address_1 ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->shipping_city ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="block whitespace-normal break-words" title="{{ $kategori }}">
                                {{ $kategori }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold">{{ $order->qty ?? $order->item_qty ?? 0 }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($order->order_date)
                                {{ \Carbon\Carbon::parse($order->order_date)->format('d/m/Y') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($paymentDate)
                                {{ $paymentDate->format('d/m/Y H:i') }}
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                    Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($estimasiPrint)
                                <span class="inline-block px-3 py-1.5 rounded-2xl text-sm font-semibold whitespace-nowrap
                                    {{ $jamPrint <= 24 ? 'badge-green' : ($jamPrint <= 48 ? 'badge-red' : 'badge-black') }}">
                                    {{ $estimasiPrint->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($estimasiPersiapan)
                                <span class="inline-block px-3 py-1.5 rounded-2xl text-sm font-semibold whitespace-nowrap
                                    {{ $jamPersiapan <= 72 ? 'badge-green' : ($jamPersiapan <= 96 ? 'badge-red' : 'badge-black') }}">
                                    {{ $estimasiPersiapan->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->ekspedisi ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->service_pengiriman ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if(($order->status_kirim ?? '') === 'Diambil')
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-xl text-sm">Diambil</span>
                            @else
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl text-sm">Dikirim</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            Rp {{ number_format($order->ship_total ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            {{ number_format($order->order_weight ?? 0, 0, ',', '.') }} gr
                        </td>
                        <td class="px-4 py-3 text-right font-semibold whitespace-nowrap">
                            Rp {{ number_format($order->total ?? $order->order_total ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->payment_method ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">
                                MANUAL
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst($order->status ?? 'Pending') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($isProcessed && $order->processed_at)
                                <span class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl text-sm font-medium whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($order->processed_at)->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!$isProcessed)
                                <div class="flex items-center justify-center gap-3">
                                    <a href="{{ route('import.manual.edit', $order->id) }}"
                                       class="text-blue-600 hover:text-blue-700 text-lg" title="Edit">✏️</a>
                                    <button type="button"
                                            onclick="if(confirm('Yakin hapus data ini?')) document.getElementById('delete-form-{{ $order->id }}').submit()"
                                            class="text-red-600 hover:text-red-700 text-lg" title="Hapus">🗑</button>
                                    <form id="delete-form-{{ $order->id }}"
                                          action="{{ route('import.manual.destroy', $order->id) }}"
                                          method="POST" class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            @else
                                <span class="inline-flex items-center gap-1 text-emerald-600 font-medium text-sm">
                                    ✅ Diproses
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="22" class="text-center py-16 text-gray-500">
                            Belum ada data pemesanan.<br>
                            Silakan <strong>Import Excel</strong> atau klik <strong>Sync Pesanan Majalah</strong>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($manualOrders->total() > 0)
            <div class="px-6 py-4 bg-white border-t flex items-center justify-between flex-wrap gap-3">
                <div class="text-sm text-gray-700">
                    Menampilkan
                    <span class="font-medium">{{ $manualOrders->firstItem() }}</span>
                    sampai
                    <span class="font-medium">{{ $manualOrders->lastItem() }}</span>
                    dari total
                    <span class="font-medium">{{ $manualOrders->total() }}</span> data
                </div>
                <div>
                    {{ $manualOrders->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

{{-- MODAL BULK PROSES --}}
<div id="bulkModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl w-[98vw] h-[95vh] mx-2 flex flex-col">
        <div class="p-6 border-b flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-semibold">Edit & Proses Data Terpilih</h3>
                <p class="text-gray-600" id="modalCount">0 data dipilih</p>
            </div>
            <button type="button" onclick="hideBulkModal()" class="text-3xl text-gray-500 hover:text-gray-700">✕</button>
        </div>
        <div class="flex-1 overflow-auto p-6">
            <table class="w-full text-sm border border-gray-200 min-w-[1600px]" id="modalTable">
                <thead class="bg-gray-50 sticky">
                    <tr class="divide-x divide-gray-200">
                        <th class="px-4 py-3 text-left w-24">Status</th>
                        <th class="px-4 py-3 text-left w-32">Invoice</th>
                        <th class="px-4 py-3 text-left min-w-[240px]">To Customer</th>
                        <th class="px-4 py-3 text-left w-40">Kategori Pesanan</th>
                        <th class="px-4 py-3 text-left w-28">Group</th>   {{-- ← TAMBAHKAN --}}
                        <th class="px-4 py-3 text-left w-36">Payment Date</th>
                        <th class="px-4 py-3 text-left w-44">Payment Channel</th>
                        <th class="px-4 py-3 text-left w-40">Distribusi <span class="text-red-500">*</span></th>
                        <th class="px-4 py-3 text-left min-w-[220px]">Jasa Kurir <span class="text-red-500">*</span></th>
                        <th class="px-4 py-3 text-left min-w-[190px]">Service</th>
                        <th class="px-4 py-3 text-left w-44">Vendor</th>
                        <th class="px-4 py-3 text-left min-w-[220px]">Catatan</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
        <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
            <button type="button" onclick="hideBulkModal()"
                    class="px-6 py-3 text-gray-600 hover:bg-gray-100 rounded-2xl">Batal</button>
            <button type="button" onclick="executeBulkAction()"
                    class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-indigo-700">
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
    const endDate   = $('input[name="end_date"]').val();
    if (startDate && endDate) {
        $('#bulkActionBar').removeClass('hidden');
    } else {
        $('#bulkActionBar').addClass('hidden');
    }
}

function checkProcessButtonVisibility() {
    const processBtn = document.getElementById('processAllBtn');
    if (!processBtn) return;

    let unprocessedCount = 0;
    document.querySelectorAll('tbody tr').forEach(row => {
        if (!row.classList.contains('processed-row')) {
            unprocessedCount++;
        }
    });

    if (unprocessedCount === 0) {
        processBtn.style.display = 'none';
        $('#selectedCount').html('✅ <span class="text-emerald-600 font-medium">Semua data pada filter ini sudah diproses</span>');
    } else {
        processBtn.style.display = 'inline-flex';
        $('#selectedCount').text(`Siap memproses ${unprocessedCount} data sesuai filter tanggal`);
    }
}

$(document).ready(function () {
    $('.payment-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Semua',
        allowClear: true,
        width: '100%'
    });
    $('.status-select').select2({
        theme: 'bootstrap-5',
        placeholder: 'Semua Status',
        allowClear: true,
        width: '100%'
    });

    checkFilterStatus();
    checkProcessButtonVisibility();

    $('input[name="start_date"], input[name="end_date"]').on('change', function () {
        checkFilterStatus();
        setTimeout(checkProcessButtonVisibility, 700);
    });
});

function processAllFilteredData() {
    const startDate = $('input[name="start_date"]').val();
    const endDate   = $('input[name="end_date"]').val();

    if (!startDate || !endDate) {
        alert('❌ Harap isi Dari Tanggal dan Sampai Tanggal terlebih dahulu!');
        return;
    }

    $.ajax({
        url: '{{ route("import.manual.filtered-ids") }}',
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
            $('#selectedCount').text(`${response.count} data akan diproses`);
            loadModalData();
        },
        error: function () {
            alert('Gagal mengambil data.');
        }
    });
}

function loadModalData() {
    $.ajax({
        url: '{{ route("import.manual.get-modal-data") }}',
        method: 'POST',
        data: {
            ids: selectedIds,
            _token: '{{ csrf_token() }}'
        },
        success: function (items) {
            let html = '';

            items.forEach(item => {
                const isLocked = Boolean(item.is_processed);
                const currentDistribusi = (item.status_kirim || 'Dikirim').trim();

                let distribusiHtml, jasaKurirHtml, serviceKurirHtml, catatanHtml;

                if (isLocked) {
                    distribusiHtml = `<span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold bg-emerald-100 text-emerald-700 rounded-2xl">${currentDistribusi}</span>`;
                    jasaKurirHtml = `<span class="text-sm text-gray-500 font-medium">— Terkunci —</span>`;
                    serviceKurirHtml = `<span class="text-sm text-gray-500 font-medium">— Terkunci —</span>`;
                    catatanHtml = `<span class="text-xs text-gray-500 italic">Sudah diproses ${item.processed_at ? 'pada ' + item.processed_at : ''}</span>`;
                } else {
                    distribusiHtml = `<span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold bg-blue-100 text-blue-700 rounded-2xl">${currentDistribusi}</span>`;

                    const selectedJasa = item.jasa_kurir || 'Lion Parcel';
                    const selectedService = item.service_kurir || 'REGPACK';

                    jasaKurirHtml = `
                        <select class="jasa-kurir-select w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                            <option value="">Pilih atau ketik jasa kurir...</option>
                            <option value="JNE" ${selectedJasa === 'JNE' ? 'selected' : ''}>JNE</option>
                            <option value="TIKI" ${selectedJasa === 'TIKI' ? 'selected' : ''}>TIKI</option>
                            <option value="Lion Parcel" ${selectedJasa === 'Lion Parcel' ? 'selected' : ''}>Lion Parcel</option>
                        </select>`;

                    if (selectedJasa === 'Lion Parcel' || !selectedJasa) {
                        serviceKurirHtml = `
                            <select class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                                <option value="">Pilih Service</option>
                                <option value="REGPACK" ${selectedService === 'REGPACK' ? 'selected' : ''}>REGPACK</option>
                                <option value="BOSPACK" ${selectedService === 'BOSPACK' ? 'selected' : ''}>BOSPACK</option>
                                <option value="JAGOPACK" ${selectedService === 'JAGOPACK' ? 'selected' : ''}>JAGOPACK</option>
                                <option value="BIGPACK" ${selectedService === 'BIGPACK' ? 'selected' : ''}>BIGPACK</option>
                            </select>`;
                    } else {
                        serviceKurirHtml = `
                            <input type="text" class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm"
                                   value="${selectedService || 'REG'}" placeholder="REG / YES / CTC">`;
                    }

                    catatanHtml = `<input type="text" class="catatan w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" placeholder="Catatan tambahan...">`;
                }

                // ===== Group Badge =====
                const grup = (item.grup || '').toUpperCase();
                let grupBadge = '<span class="text-gray-400">-</span>';

                if (grup) {
                    const grupColors = {
                        'A': 'bg-blue-100 text-blue-700 border-blue-200',
                        'B': 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'C': 'bg-purple-100 text-purple-700 border-purple-200',
                        'D': 'bg-amber-100 text-amber-700 border-amber-200',
                        'E': 'bg-rose-100 text-rose-700 border-rose-200',
                        'F': 'bg-cyan-100 text-cyan-700 border-cyan-200',
                    };
                    const color = grupColors[grup] || 'bg-gray-100 text-gray-600 border-gray-200';
                    grupBadge = `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border ${color}">Group ${grup}</span>`;
                }

                // ===== Payment Date =====
                const paymentDateHtml = item.payment_date
                    ? item.payment_date
                    : `<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-200">Pending</span>`;

                // ===== To Customer + Mismatch =====
                let customerHtml = `<div class="flex flex-col gap-0.5">
                    <span>${item.to_customer}</span>`;

                if (item.is_mismatch) {
                    if (item.nama_excel || item.nama_master) {
                        customerHtml += `
                            <div class="text-xs font-normal mt-0.5 space-y-0.5">
                                ${item.nama_excel ? `
                                    <div class="text-orange-700">
                                        <span class="text-gray-500">Excel:</span>
                                        <span class="font-medium">${item.nama_excel}</span>
                                    </div>` : ''}
                                ${item.nama_master ? `
                                    <div class="text-emerald-700">
                                        <span class="text-gray-500">Kemitraan:</span>
                                        <span class="font-medium">${item.nama_master}</span>
                                    </div>` : ''}
                            </div>
                            <span class="inline-flex items-center self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                ⚠️ Mismatch
                            </span>`;
                    } else {
                        customerHtml += `
                            <span class="inline-flex items-center self-start mt-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 text-orange-800 border border-orange-200">
                                ⚠️ Mismatch
                            </span>`;
                    }
                }

                customerHtml += `</div>`;

                html += `
                    <tr data-id="${item.id}" data-distribusi="${currentDistribusi}"
                        class="${isLocked ? 'processed-row' : 'hover:bg-gray-50'}">
                        <td class="px-4 py-3">${item.status_pembayaran || '-'}</td>
                        <td class="px-4 py-3 font-medium">${item.invoice}</td>
                        <td class="px-4 py-3">${customerHtml}</td>
                        <td class="px-4 py-3 font-medium text-gray-700">${item.pesanan || '-'}</td>
                        <td class="px-4 py-3">${grupBadge}</td>
                        <td class="px-4 py-3">${paymentDateHtml}</td>
                        <td class="px-4 py-3 font-medium text-blue-700">${item.payment_channel}</td>
                        <td class="px-4 py-3">${distribusiHtml}</td>
                        <td class="px-4 py-3">${jasaKurirHtml}</td>
                        <td class="px-4 py-3">${serviceKurirHtml}</td>
                        <td class="px-4 py-3 font-medium text-indigo-700">${item.vendor}</td>
                        <td class="px-4 py-3">${catatanHtml}</td>
                    </tr>`;
            });

            $('#modalTableBody').html(html);
            $('#modalCount').text(`${items.length} data dipilih`);
            $('#bulkModal').removeClass('hidden');
            initModalLogic();
        },
        error: function () {
            alert('Gagal memuat data ke modal.');
        }
    });
}

function initModalLogic() {
    $('.jasa-kurir-select').select2({
        placeholder: 'Pilih atau ketik jasa kurir...',
        allowClear: true,
        tags: true,
        width: '100%',
        dropdownParent: $('#bulkModal'),
        createTag: function (params) {
            const term = $.trim(params.term);
            if (term === '') return null;
            return { id: term, text: term, new: true };
        }
    }).on('change', function () {
        const row = $(this).closest('tr');
        const jasa = $(this).val();
        let serviceField = row.find('.service-kurir');

        if (jasa === 'JNE' || jasa === 'TIKI') {
            if (!serviceField.is('input')) {
                serviceField.replaceWith(`
                    <input type="text" class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" value="REG">
                `);
            } else {
                serviceField.val('REG').prop('readonly', true);
            }
        } else if (jasa === 'Lion Parcel') {
            serviceField.replaceWith(`
                <select class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                    <option value="">Pilih Service</option>
                    <option value="REGPACK" selected>REGPACK</option>
                    <option value="BOSPACK">BOSPACK</option>
                    <option value="JAGOPACK">JAGOPACK</option>
                    <option value="BIGPACK">BIGPACK</option>
                </select>
            `);
        } else {
            if (!serviceField.is('input')) {
                serviceField.replaceWith(`
                    <input type="text" class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" placeholder="REG, YES, CTC, dll">
                `);
            } else {
                serviceField.prop('readonly', false).val('');
            }
        }
        checkSaveButtonState();
    });

    // Diambil Sendiri
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
            let serviceValue = '';
            const serviceField = row.find('.service-kurir');

            if (serviceField.length) {
                serviceValue = serviceField.is('select')
                    ? (serviceField.val() || '')
                    : $.trim(serviceField.val());
            }

            const distribusi = row.data('distribusi');
            if (distribusi === 'Diambil') {
                jasaKurir = 'Diambil Sendiri';
            }

            if (!jasaKurir) {
                isValid = false;
                return false;
            }
            if (distribusi === 'Dikirim' && !serviceValue) {
                isValid = false;
                return false;
            }
        });

        const saveButton = $('.bg-indigo-600');
        if (isValid) {
            saveButton.prop('disabled', false)
                .removeClass('opacity-50 cursor-not-allowed')
                .text('💾 Simpan & Kunci Semua Data');
        } else {
            saveButton.prop('disabled', true)
                .addClass('opacity-50 cursor-not-allowed')
                .text('Lengkapi Jasa Kurir & Service');
        }
    }

    $(document).off('input change', '.service-kurir').on('input change', '.service-kurir', checkSaveButtonState);
    setTimeout(checkSaveButtonState, 400);
}

function hideBulkModal() {
    $('#bulkModal').addClass('hidden');
    try {
        $('.jasa-kurir-select').select2('destroy');
    } catch (e) {}
}

function executeBulkAction() {
    if ($('.bg-indigo-600').prop('disabled')) {
        alert('❌ Harap isi Jasa Kurir dan Service untuk semua data yang belum terkunci!');
        return;
    }

    if (!confirm(`Yakin ingin memproses ${selectedIds.length} data?`)) return;

    const updates = [];

    $('#modalTableBody tr').each(function () {
        const row = $(this);
        let distribusiText = row.data('distribusi');
        let jasaKurirText = row.find('.jasa-kurir-select').val() || '';

        if (distribusiText === 'Diambil') {
            jasaKurirText = 'Diambil Sendiri';
        }

        updates.push({
            id: row.data('id'),
            status_kirim: distribusiText,
            jasa_kurir: jasaKurirText,
            service_kurir: row.find('.service-kurir').val() || '',
            catatan: row.find('.catatan').val() || ''
        });
    });

    const form = $('<form>', {
        action: '{{ route("import.manual.bulk-action") }}',
        method: 'POST'
    });

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