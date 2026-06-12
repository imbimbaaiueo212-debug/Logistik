<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jakarta Aktif - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 12px 8px; font-size: 0.85rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; }
        tr:hover { background-color: #f8fafc; }
        
        .status-success { 
            background-color: #d1fae5; 
            color: #065f46; 
            padding: 4px 12px; 
            border-radius: 9999px; 
            font-size: 0.8rem; font-weight: 600; 
        }

        .badge-green { background-color: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-yellow { background-color: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-red { background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-black { background-color: #1f2937; color: #f3f4f6; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }

        .processed-row { 
            opacity: 0.75; 
            background-color: #f9fafb; 
        }

        #bulkModal table td, #bulkModal table th { 
            padding: 10px 8px; 
            font-size: 0.8rem; 
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Top Navigation -->
    @include('partials.top-nav')

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Jakarta Aktif</h1>
                <p class="text-gray-600">Kelola Data Order Jakarta Aktif</p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('order.unit-pasif') }}" 
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
                    ← Kembali ke Dashboard
                </a>

                <form action="{{ route('order.jakarta-aktif.sync-jkt') }}" method="POST" style="display: inline;" 
                    onsubmit="return confirm('Yakin ingin sync semua data JKT dari Bimbashop & Casdana?')">
                    @csrf
                    <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-purple-700 flex items-center gap-2">
                        🔄 Sync JKT + Casdana
                    </button>
                </form>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div id="bulkActionBar" class="hidden bg-white rounded-3xl shadow p-5 mb-6 flex items-center justify-between border border-indigo-100">
            <div class="flex items-center gap-3">
                <span id="selectedCount" class="text-sm font-semibold text-gray-700">
                    0 data dipilih
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="showBulkModal()" 
                        class="bg-indigo-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-indigo-700 flex items-center gap-2">
                    ✅ Proses & Edit Terpilih
                </button>
                <button onclick="clearSelection()" 
                        class="bg-gray-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-600">
                    Batal
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Pesan</label>
                    <input type="text" name="id_pesan" value="{{ request('id_pesan') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Cari ID Pesan...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kirim</label>
                    <input type="text" name="kirim" value="{{ request('kirim') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Nama Penerima...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                    <input type="text" name="nama_unit" value="{{ request('nama_unit') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:border-blue-500" 
                           placeholder="Nama Unit...">
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
                    <a href="{{ route('order.jakarta-aktif') }}" 
                       class="text-gray-500 hover:text-red-600 px-4 py-2.5 text-sm font-medium whitespace-nowrap">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data Utama -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-center px-3 py-3 w-10">
                        </th>
                        <th class="text-left px-4 py-3">ID Pesan</th>
                        <th class="text-left px-4 py-3">Nama Unit</th>
                        <th class="text-left px-4 py-3">Cabang</th>
                        <th class="text-left px-4 py-3">Alamat Kirim</th>
                        <th class="text-left px-4 py-3">Kab/Kota</th>
                        <th class="text-left px-4 py-3">Pesanan</th>
                        <th class="text-left px-4 py-3">Order Date</th>
                        <th class="text-left px-4 py-3">Payment Date</th>
                        <th class="text-left px-4 py-3">Estimasi Print PL</th>
                        <th class="text-left px-4 py-3">Estimasi Persiapan</th>
                        <th class="text-left px-4 py-3">Jasa Kurir</th>
                        <th class="text-left px-4 py-3">Service Kurir</th>
                        <th class="text-left px-4 py-3">Kirim</th>
                        <th class="text-right px-4 py-3">Ship Total</th>
                        <th class="text-right px-4 py-3">Berat</th>
                        <th class="text-right px-4 py-3">Item Price</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-left px-4 py-3">Status Bayar</th>
                        <th class="text-left px-4 py-3">Status biMBAShop</th>
                        <th class="text-center px-4 py-3">Tanggal Proses</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $item)
                        @php
                            $isProcessed = $item->is_processed ?? false;
                            $now = \Carbon\Carbon::now();
                            $paymentDate = $item->payment_date ? \Carbon\Carbon::parse($item->payment_date) : null;
                            $estimasiPrint = $paymentDate ? $paymentDate->copy()->addHours(24) : null;
                            $jamPrint = $paymentDate ? $paymentDate->diffInHours($now) : 999;
                            $estimasiPersiapan = $paymentDate ? $paymentDate->copy()->addHours(72) : null;
                            $jamPersiapan = $paymentDate ? $paymentDate->diffInHours($now) : 999;
                        @endphp
                    <tr class="{{ $isProcessed ? 'processed-row' : '' }} hover:bg-gray-50">
                        <td class="text-center px-3 py-3">
                            @if(!$isProcessed)
                                <input type="checkbox" name="selected[]" value="{{ $item->id }}" class="w-4 h-4 row-checkbox">
                            @else
                                <span class="text-emerald-600 font-bold">✓</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $item->id_pesan ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->billing_last_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->kirim ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->kab_kota_provinsi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->pesanan ?? '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($item->tgl_pesan) {{ \Carbon\Carbon::parse($item->tgl_pesan)->format('d/m/Y H:i') }} @else - @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($paymentDate) {{ $paymentDate->format('d/m/Y H:i') }} @else - @endif
                        </td>
                       <!-- Estimasi Print PL -->
                        <td class="px-4 py-3 font-medium text-center">
                            @if($estimasiPrint)
                                <span class="inline-block px-3 py-1.5 rounded-2xl text-sm font-semibold whitespace-nowrap
                                    {{ $jamPrint <= 24 ? 'badge-green' : ($jamPrint <= 48 ? 'badge-red' : 'badge-black') }}">
                                    {{ $estimasiPrint->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- Estimasi Persiapan -->
                        <td class="px-4 py-3 font-medium text-center">
                            @if($estimasiPersiapan)
                                <span class="inline-block px-3 py-1.5 rounded-2xl text-sm font-semibold whitespace-nowrap
                                    {{ $jamPersiapan <= 72 ? 'badge-green' : ($jamPersiapan <= 96 ? 'badge-red' : 'badge-black') }}">
                                    {{ $estimasiPersiapan->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $item->ekspedisi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->service_pengiriman ?? '-' }}</td>
                        <td class="px-4 py-3">
                            @if($item->status_kirim === 'Dikirim')
                                <span class="bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl text-sm">Dikirim</span>
                            @else
                                <span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-xl text-sm">Diambil</span>
                            @endif
                        </td>
                        <td class="text-right px-4 py-3">Rp {{ number_format($item->ongkir ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right px-4 py-3">{{ number_format($item->berat ?? 0, 0, ',', '.') }} gr</td>
                        <td class="text-right px-4 py-3">Rp {{ number_format($item->harga ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right px-4 py-3 font-semibold">Rp {{ number_format($item->total ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @if($item->status_pembayaran)
                                <span class="status-success">{{ $item->status_pembayaran }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php $statusPesan = $item->status_pesan ?? null; @endphp
                            @if($statusPesan)
                                <span class="inline-block px-3 py-1 rounded-xl text-sm font-semibold
                                    {{ in_array(strtolower($statusPesan), ['completed','success','paid','settled']) ? 'bg-emerald-100 text-emerald-700' : 
                                    (in_array(strtolower($statusPesan), ['cancelled','failed']) ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                    {{ $statusPesan }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <!-- Kolom Aksi -->
                    <!-- Tanggal Proses -->
                        <td class="px-4 py-3 text-center">
                            @if($isProcessed && $item->processed_at)
                                <span class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl text-sm font-medium whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item->processed_at)->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="text-center px-4 py-3">
                            @if(!$isProcessed)
                                <a href="{{ route('order.jakarta-aktif.edit', $item->id) }}" 
                                class="text-blue-600 hover:text-blue-700 text-lg">✏️</a>
                            @else
                                <span class="text-emerald-600 font-medium">✅ Diproses</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="21" class="text-center py-16 text-gray-500">
                            Belum ada data Jakarta Aktif.<br>
                            Silakan klik tombol <strong>Sync JKT + Casdana</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->count() > 0)
        <div class="mt-6 text-sm text-gray-600 flex justify-between items-center">
            <div>Menampilkan <strong>{{ $data->count() }}</strong> data (Total: {{ $data->total() }})</div>
            <div>{{ $data->links() }}</div>
        </div>
        @endif
    </div>

    <!-- ==================== ADVANCED BULK MODAL ==================== -->
<div id="bulkModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl mx-4 max-h-[92vh] flex flex-col">
        
        <div class="p-6 border-b flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-semibold">Edit & Proses Data Terpilih</h3>
                <p class="text-gray-600" id="modalCount">0 data dipilih</p>
            </div>
            <button onclick="hideBulkModal()" class="text-gray-500 hover:text-gray-700 text-3xl">✕</button>
        </div>

        <!-- Tabel Editable -->
        <div class="flex-1 overflow-auto p-6">
            <table class="w-full text-sm border border-gray-200" id="modalTable">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left">ID Pesan</th>
                        <th class="px-4 py-3 text-left">Nama Unit</th>
                        <th class="px-4 py-3 text-left">Penerima</th>
                        <th class="px-4 py-3 text-left">Status Kirim</th>
                        <th class="px-4 py-3 text-left">Jasa Kurir</th>
                        <th class="px-4 py-3 text-left">Service</th>
                        <th class="px-4 py-3 text-left">Catatan</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>

        <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
            <button onclick="hideBulkModal()" 
                    class="px-6 py-3 text-gray-600 hover:bg-gray-100 rounded-2xl">
                Batal
            </button>
            <button onclick="executeBulkAction()" 
                    class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-indigo-700 flex items-center gap-2">
                💾 Simpan & Kunci Semua Data
            </button>
        </div>
    </div>
</div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script>
    let selectedIds = [];

    function updateBulkBar() {
        selectedIds = $('.row-checkbox:checked').map(function(){ return this.value; }).get();
        if (selectedIds.length > 0) {
            $('#bulkActionBar').removeClass('hidden');
            $('#selectedCount').text(`${selectedIds.length} data dipilih`);
        } else {
            $('#bulkActionBar').addClass('hidden');
        }
    }

    $(document).ready(function() {
        $('#selectAll').on('change', function() {
            $('.row-checkbox').prop('checked', this.checked);
            updateBulkBar();
        });
        $('.row-checkbox').on('change', updateBulkBar);
    });

    function showBulkModal() {
        if (selectedIds.length === 0) return;

        let html = '';
        $('.row-checkbox:checked').each(function() {
            const row = $(this).closest('tr');
            const id = $(this).val();
            
            html += `
                <tr data-id="${id}">
                    <td class="px-4 py-3">${row.find('td:eq(1)').text().trim()}</td>
                    <td class="px-4 py-3">${row.find('td:eq(2)').text().trim()}</td>
                    <td class="px-4 py-3">${row.find('td:eq(4)').text().trim()}</td>
                    
                    <td class="px-4 py-3">
                        <select class="status-kirim w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <option value="Diambil">Diambil</option>
                            <option value="Dikirim">Dikirim</option>
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <select class="jasa-kurir w-full border border-gray-300 rounded-xl px-3 py-2 text-sm">
                            <option value="">Pilih Jasa</option>
                            <option value="Ambil Sendiri">Ambil Sendiri</option>
                            <option value="Driver">Driver</option>
                            <option value="JNE">JNE</option>
                            <option value="Lion Parcel">Lion Parcel</option>
                            <option value="TIKI">TIKI</option>
                        </select>
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" class="service-kurir w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" 
                               placeholder="REG, YES, dll">
                    </td>
                    <td class="px-4 py-3">
                        <input type="text" class="catatan w-full border border-gray-300 rounded-xl px-3 py-2 text-sm" 
                               placeholder="Catatan...">
                    </td>
                </tr>`;
        });

        $('#modalTableBody').html(html);
        $('#modalCount').text(`${selectedIds.length} data dipilih`);
        $('#bulkModal').removeClass('hidden');
    }

    function hideBulkModal() {
        $('#bulkModal').addClass('hidden');
    }

    function executeBulkAction() {
        if (!confirm(`Yakin ingin menyimpan & mengunci ${selectedIds.length} data?`)) return;

        const updates = [];

        $('#modalTableBody tr').each(function() {
            const id = $(this).data('id');
            updates.push({
                id: id,
                status_kirim: $(this).find('.status-kirim').val(),
                jasa_kurir: $(this).find('.jasa-kurir').val(),
                service_kurir: $(this).find('.service-kurir').val(),
                catatan: $(this).find('.catatan').val()
            });
        });

        const form = $('<form>', {
            action: '{{ route("order.jakarta-aktif.bulk-action") }}',
            method: 'POST'
        });

        $('<input>').attr({type:'hidden', name:'_token', value:'{{ csrf_token() }}'}).appendTo(form);
        $('<input>').attr({type:'hidden', name:'action', value:'processed'}).appendTo(form);
        $('<input>').attr({type:'hidden', name:'per_item', value: JSON.stringify(updates)}).appendTo(form);

        form.appendTo('body').submit();
    }

    // Clear Selection
    function clearSelection() {
        $('.row-checkbox').prop('checked', false);
        $('#selectAll').prop('checked', false);
        $('#bulkActionBar').addClass('hidden');
    }
</script>
</body>
</html>