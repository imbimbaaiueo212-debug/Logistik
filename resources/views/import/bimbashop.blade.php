<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data biMBA Shop - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        .truncate { max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .nominal { font-variant-numeric: tabular-nums; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Top Navigation -->
    @include('partials.top-nav')

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Data Order (biMBA Shop)</h1>
                <p class="text-gray-600">Import & Kelola Data Order dari biMBA Shop</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('import.index') }}" 
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
                    ← Kembali ke Daftar Import
                </a>
                <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                        class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700 flex items-center gap-2">
                    📤 Import Data Baru
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Order ID</label>
                    <input type="text" name="order_id" value="{{ request('order_id') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Cari Order ID...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item SKU</label>
                    <input type="text" name="item_sku" value="{{ request('item_sku') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="SKU...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                    <input type="text" name="item_name" value="{{ request('item_name') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Nama Item...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Billing Name</label>
                    <input type="text" name="billing_name" value="{{ request('billing_name') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Nama Billing...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" class="payment-select w-full">
                        <option value="">Semua</option>
                        <option value="bacs" {{ request('payment_method') == 'bacs' ? 'selected' : '' }}>bacs</option>
                        <option value="lunas_payment_gateway" {{ request('payment_method') == 'lunas_payment_gateway' ? 'selected' : '' }}>lunas_payment_gateway</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="status-select w-full">
                        <option value="">Semua Status</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="on-hold" {{ request('status') == 'on-hold' ? 'selected' : '' }}>On Hold</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
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
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
                        <option value="5"  {{ request('per_page') == 5  ? 'selected' : '' }}>5</option>
                        <option value="10"  {{ request('per_page') == 10  ? 'selected' : '' }}>10</option>
                        <option value="25"  {{ request('per_page') == 25  ? 'selected' : '' }}>25</option>
                        <option value="50"  {{ request('per_page') == 50  ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                    </select>
                </div>

                <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('import.bimbashop') }}" 
                       class="text-gray-500 hover:text-red-600 px-4 py-2.5 text-sm font-medium whitespace-nowrap">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Form Import -->
        <div id="importForm" class="hidden bg-white rounded-3xl shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Upload File Excel / CSV</h2>
            <form action="{{ route('import.bimbashop.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                        <input type="file" name="import_file" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-green-700">
                        🚀 Import Sekarang
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Format yang didukung: .xlsx, .xls, .csv (max 10MB)</p>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-left">Waktu Import</th>
                        <th class="text-left">Order ID</th>
                        <th class="text-left">Order Date</th>
                        <th class="text-left">Item SKU</th>
                        <th class="text-left">Item Name</th>
                        <th class="text-right">Item Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-left">Status</th>
                        <th class="text-right">Order Total</th>
                        <th class="text-right">Ship Total</th>
                        <th class="text-right">Discount</th>
                        <th class="text-right">Refunded</th>
                        <th class="text-left">Payment Method</th>
                        <th class="text-left">Billing Name</th>
                        <th class="text-left">Shipping Name</th>
                        <th class="text-left">Shipping Address 1</th>
                        <th class="text-left">Shipping Address 2</th>
                        <th class="text-left">Shipping City</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($bimbashopOrders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $order->created_at ?? '-' }}</td>
                        <td class="font-medium">{{ $order->order_id }}</td>
                        <td>{{ $order->order_date ? $order->order_date->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $order->item_sku ?? '-' }}</td>
                        <td class="truncate">{{ $order->item_name ?? '-' }}</td>
                        <td class="text-right nominal">Rp {{ number_format($order->item_price ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center font-medium">{{ $order->item_qty ?? 0 }}</td>
                        <td>
                            <span class="px-3 py-1 rounded-full text-xs 
                                @if(strtolower($order->status ?? '') == 'completed') bg-green-100 text-green-700
                                @elseif(strtolower($order->status ?? '') == 'processing') bg-blue-100 text-blue-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ $order->status ?? 'Pending' }}
                            </span>
                        </td>
                        <td class="text-right font-semibold nominal">Rp {{ number_format($order->order_total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right nominal">Rp {{ number_format($order->ship_total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right nominal">Rp {{ number_format($order->discount_total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right nominal">Rp {{ number_format($order->refunded_total ?? 0, 0, ',', '.') }}</td>
                        <td>{{ $order->payment_method ?? '-' }}</td>
                        <td>{{ trim(($order->billing_first_name ?? '') . ' ' . ($order->billing_last_name ?? '')) ?: '-' }}</td>
                        <td>{{ trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? '')) ?: '-' }}</td>
                        <td class="truncate">{{ $order->shipping_address_1 ?? '-' }}</td>
                        <td class="truncate">{{ $order->shipping_address_2 ?? '-' }}</td>
                        <td>{{ $order->shipping_city ?? '-' }}</td>
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-3">
                                <a href="{{ route('import.bimbashop.edit', $order->id) }}" 
                                   class="text-blue-600 hover:text-blue-700" title="Edit">
                                    ✏️
                                </a>
                                <button onclick="if(confirm('Yakin hapus data ini?')) document.getElementById('delete-form-{{ $order->id }}').submit()" 
                                        class="text-red-600 hover:text-red-700" title="Hapus">
                                    🗑
                                </button>
                                <form id="delete-form-{{ $order->id }}" action="{{ route('import.bimbashop.destroy', $order->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="18" class="text-center py-16 text-gray-500">
                            Tidak ada data yang sesuai filter.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($bimbashopOrders->count() > 0)
        <div class="mt-6 text-sm text-gray-600 flex justify-between items-center">
            <div>
                Menampilkan <strong>{{ $bimbashopOrders->count() }}</strong> data 
                <span class="text-gray-400">(Total: {{ $bimbashopOrders->total() }} data)</span>
            </div>
            <div>{{ $bimbashopOrders->links() }}</div>
        </div>
        @endif

    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('.payment-select').select2({
                theme: 'bootstrap-5',
                placeholder: "Semua Payment Method",
                allowClear: true,
                width: '100%'
            });

            $('.status-select').select2({
                theme: 'bootstrap-5',
                placeholder: "Semua Status",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

</body>
</html>