<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual Pemesanan - biMBA AIUEO Logistik</title>
    
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
        <h1 class="text-3xl font-bold text-gray-800">Manual Pemesanan</h1>
        <p class="text-gray-600">Kelola data pemesanan manual</p>
    </div>
    
    <div class="flex gap-3">
        <a href="{{ route('import.index') }}" 
           class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
            ← Kembali ke Daftar Import
        </a>
        
        <!-- Tombol Import Excel -->
        <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-emerald-700 flex items-center gap-2">
            📤 Import Excel
        </button>

        <!-- Tombol Tambah Manual -->
        <button onclick="openModal()"
                class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700 flex items-center gap-2">
            ➕ Tambah Data Manual
        </button>
    </div>
</div>

<!-- Form Import Excel -->
<div id="importForm" class="hidden bg-white rounded-3xl shadow p-6 mb-8">
    <h2 class="text-xl font-semibold mb-4">Upload File Excel / CSV</h2>
    <form action="{{ route('import.manual.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                <input type="file" name="import_file" 
                       class="block w-full text-sm text-gray-500 
                              file:mr-4 file:py-3 file:px-6 file:rounded-2xl 
                              file:border-0 file:text-sm file:font-semibold 
                              file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100"
                       accept=".xlsx,.xls,.csv" required>
            </div>
            <button type="submit" class="bg-emerald-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-emerald-700">
                🚀 Import Sekarang
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-2">Format yang didukung: .xlsx, .xls, .csv (max 10MB)</p>
    </form>
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <input type="text" name="customer_name" value="{{ request('customer_name') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Nama Customer...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Item Name</label>
                    <input type="text" name="product_name" value="{{ request('product_name') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Nama Produk...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <input type="text" name="product_sku" value="{{ request('product_sku') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="SKU...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                    <select name="payment_method" class="payment-select w-full">
                        <option value="">Semua</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
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
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('import.manual') }}" 
                       class="text-gray-500 hover:text-red-600 px-4 py-2.5 text-sm font-medium whitespace-nowrap">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-left">Order ID</th>
                        <th class="text-left">Order Date</th>
                        <th class="text-left">Item SKU</th>
                        <th class="text-left">Item Name</th>
                        <th class="text-right">Item Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-left">Status</th>
                        <th class="text-right">Order Total</th>
                        <th class="text-right">Ship Total</th>
                        <th class="text-right">Berat</th>
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
                    @forelse($manualOrders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="font-medium">{{ $order->order_id ?? '-' }}</td>
                        <td>{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $order->product_sku ?? $order->item_sku ?? '-' }}</td>
                        <td class="truncate">{{ $order->product_name ?? $order->item_name ?? '-' }}</td>
                        <td class="text-right nominal">Rp {{ number_format($order->price ?? $order->item_price ?? 0, 0, ',', '.') }}</td>
                        <td class="text-center font-medium">{{ $order->qty ?? $order->item_qty ?? 0 }}</td>
                        <td>
                            <span class="px-3 py-1 rounded-full text-xs 
                                @if(strtolower($order->status ?? '') == 'completed') bg-green-100 text-green-700
                                @elseif(strtolower($order->status ?? '') == 'processing') bg-blue-100 text-blue-700
                                @else bg-yellow-100 text-yellow-700 @endif">
                                {{ ucfirst($order->status ?? 'Pending') }}
                            </span>
                        </td>
                        <td class="text-right font-semibold nominal">Rp {{ number_format($order->total ?? $order->order_total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right nominal">Rp {{ number_format($order->ship_total ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right nominal">{{ number_format($order->order_weight ?? 0, 0, ',', '.') }} gr</td>
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
                                <a href="{{ route('import.manual.edit', $order->id) }}" 
                                   class="text-blue-600 hover:text-blue-700" title="Edit">✏️</a>
                                <button onclick="if(confirm('Yakin hapus data ini?')) document.getElementById('delete-form-{{ $order->id }}').submit()" 
                                        class="text-red-600 hover:text-red-700" title="Hapus">🗑</button>
                                <form id="delete-form-{{ $order->id }}" action="{{ route('import.manual.destroy', $order->id) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="19" class="text-center py-16 text-gray-500">
                            Belum ada data pemesanan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($manualOrders->count() > 0)
        <div class="mt-6 text-sm text-gray-600 flex justify-between items-center">
            <div>
                Menampilkan <strong>{{ $manualOrders->count() }}</strong> data 
                <span class="text-gray-400">(Total: {{ $manualOrders->total() }} data)</span>
            </div>
            <div>{{ $manualOrders->links() }}</div>
        </div>
        @endif

    </div>

    <!-- Modal Tambah Data -->
<div id="createModal" class="fixed inset-0 bg-black/60 hidden flex justify-center items-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[92vh] overflow-auto">
        <div class="flex justify-between items-center border-b p-6 sticky top-0 bg-white rounded-t-3xl">
            <h2 class="text-2xl font-bold">Tambah Manual Pemesanan</h2>
            <button onclick="closeModal()" class="text-3xl text-gray-400 hover:text-red-500">&times;</button>
        </div>

        <form action="{{ route('import.manual.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-2 gap-5">
                <!-- Kolom Kiri -->
                <div>
                    <label class="block text-sm font-medium mb-1">Order ID</label>
                    <input type="text" name="order_id" class="w-full border border-gray-300 rounded-xl p-3" placeholder="OPS-12345">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Tanggal Order</label>
                    <input type="date" name="order_date" class="w-full border border-gray-300 rounded-xl p-3" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Customer Name</label>
                    <input type="text" name="customer_name" class="w-full border border-gray-300 rounded-xl p-3" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Phone</label>
                    <input type="text" name="phone" class="w-full border border-gray-300 rounded-xl p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Product SKU</label>
                    <input type="text" name="product_sku" class="w-full border border-gray-300 rounded-xl p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Item Name</label>
                    <input type="text" name="product_name" class="w-full border border-gray-300 rounded-xl p-3" required>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <label class="block text-sm font-medium mb-1">Qty</label>
                    <input type="number" id="qty" name="qty" value="1" 
                           class="w-full border border-gray-300 rounded-xl p-3" onkeyup="hitungTotal()" onchange="hitungTotal()" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Item Price</label>
                    <input type="number" id="price" name="price" step="0.01" 
                           class="w-full border border-gray-300 rounded-xl p-3" onkeyup="hitungTotal()" onchange="hitungTotal()" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Order Total</label>
                    <input type="number" id="total" name="total" readonly 
                           class="w-full bg-gray-100 border border-gray-300 rounded-xl p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Ship Total</label>
                    <input type="number" name="ship_total" step="0.01" class="w-full border border-gray-300 rounded-xl p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Order Weight (gr)</label>
                    <input type="number" name="order_weight" step="0.01" class="w-full border border-gray-300 rounded-xl p-3">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full border border-gray-300 rounded-xl p-3">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer</option>
                        <option value="credit_card">Credit Card</option>
                    </select>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-xl p-3">
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                    </select>
                </div>

                <!-- Billing & Shipping -->
                <div class="col-span-2 border-t pt-4 mt-2">
                    <h3 class="font-semibold mb-3 text-gray-700">Billing Information</h3>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Billing First Name</label>
                            <input type="text" name="billing_first_name" class="w-full border border-gray-300 rounded-xl p-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Billing Last Name</label>
                            <input type="text" name="billing_last_name" class="w-full border border-gray-300 rounded-xl p-3">
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <h3 class="font-semibold mb-3 text-gray-700">Shipping Information</h3>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium mb-1">Shipping First Name</label>
                            <input type="text" name="shipping_first_name" class="w-full border border-gray-300 rounded-xl p-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Shipping Last Name</label>
                            <input type="text" name="shipping_last_name" class="w-full border border-gray-300 rounded-xl p-3">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-1">Shipping Address 1</label>
                            <input type="text" name="shipping_address_1" class="w-full border border-gray-300 rounded-xl p-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Shipping Address 2</label>
                            <input type="text" name="shipping_address_2" class="w-full border border-gray-300 rounded-xl p-3">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Shipping City</label>
                            <input type="text" name="shipping_city" class="w-full border border-gray-300 rounded-xl p-3">
                        </div>
                    </div>
                </div>

                <div class="col-span-2">
                    <label class="block text-sm font-medium mb-1">Notes</label>
                    <textarea name="notes" rows="3" class="w-full border border-gray-300 rounded-xl p-3"></textarea>
                </div>
            </div>

            <div class="border-t p-6 flex justify-end gap-3">
                <button type="button" onclick="closeModal()" 
                        class="px-6 py-3 bg-gray-500 text-white rounded-xl hover:bg-gray-600">
                    Batal
                </button>
                <button type="submit" 
                        class="px-8 py-3 bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('.payment-select').select2({ theme: 'bootstrap-5', placeholder: "Semua", allowClear: true });
            $('.status-select').select2({ theme: 'bootstrap-5', placeholder: "Semua Status", allowClear: true });
        });

        function openModal() {
            document.getElementById('createModal').classList.remove('hidden');
            document.getElementById('createModal').classList.add('flex');
        }

        function closeModal() {
            document.getElementById('createModal').classList.add('hidden');
            document.getElementById('createModal').classList.remove('flex');
        }
    </script>
</body>
</html>