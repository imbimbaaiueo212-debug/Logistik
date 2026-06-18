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
    
    .status-success { background-color: #d1fae5; color: #065f46; padding: 4px 12px; border-radius: 9999px; font-size: 0.8rem; font-weight: 600; }
    .badge-green { background-color: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
    .badge-yellow { background-color: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
    .badge-red { background-color: #fee2e2; color: #b91c1c; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
    .badge-black { background-color: #1f2937; color: #f3f4f6; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }

    .processed-row {
        opacity: 0.65;
        background-color: #f1f5f9 !important;
        color: #64748b;
    }
    
    .processed-row td {
        color: #64748b;
    }

    .pagination {
        display: flex;
        gap: 6px;
    }
    .pagination li a, .pagination li span {
        padding: 8px 14px;
        border-radius: 9999px;
        font-size: 0.9rem;
    }
    .pagination .active span {
        background-color: #4f46e5;
        color: white;
    }

    /* Tambahkan di dalam tag <style> */
#modalTable td, #modalTable th {
    vertical-align: middle;
}

#modalTable .alamat {
    min-width: 280px;
}

#modalTable .jasa-kurir {
    min-width: 140px;
}

#modalTable .service-kurir {
    min-width: 140px;
}

#modalTable .catatan {
    min-width: 280px;
}

#modalTable td:nth-child(4) {  /* Kolom Alamat */
    max-width: 350px;
    white-space: normal;
    word-break: break-word;
}

#modalTable input, #modalTable select {
    min-height: 42px;
}
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Jakarta Aktif</h1>
                <p class="text-gray-600">Kelola Data Order Jakarta Aktif</p>
            </div>
            
            <div class="flex gap-3">
                <a href="{{ route('order.unit-pasif') }}" class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700">← Kembali ke Dashboard</a>
                <a href="{{ route('order.jakarta-printed') }}" class="bg-blue-500 text-white px-6 py-3 rounded-xl font-semibold">Rekap Aktual</a>
                
                <form action="{{ route('order.jakarta-aktif.sync-jkt') }}" method="POST" style="display: inline;" onsubmit="return confirm('Yakin ingin sync?')">
                    @csrf
                    <button type="submit" class="bg-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-purple-700">🔄 Sync JKT + Casdana</button>
                </form>
            </div>
        </div>

        <!-- Bulk Action Bar -->
        <div id="bulkActionBar" class="bg-white rounded-3xl shadow p-5 mb-6 flex items-center justify-between border border-indigo-100">
            <div>
                <span id="selectedCount" class="text-sm font-semibold text-gray-700">
                    Siap memproses data sesuai filter tanggal
                </span>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="processAllFilteredData()" 
                        id="processAllBtn"
                        class="bg-emerald-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-emerald-700 flex items-center gap-2">
                    📅 Proses & Edit Semua Sesuai Filter Tanggal
                </button>
                <button onclick="clearSelection()" 
                        class="bg-gray-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-600">
                    Reset
                </button>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-8 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ID Pesan</label>
                    <input type="text" name="id_pesan" value="{{ request('id_pesan') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Cari ID Pesan...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kirim</label>
                    <input type="text" name="kirim" value="{{ request('kirim') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Nama Penerima...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                    <input type="text" name="nama_unit" value="{{ request('nama_unit') }}" class="w-full border border-gray-300 rounded-xl px-4 py-2.5" placeholder="Nama Unit...">
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
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>
                <div class="flex items-end gap-3 pt-6 lg:col-span-2">
                    <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">🔍 Terapkan Filter</button>
                    <a href="{{ route('order.jakarta-aktif') }}" class="text-gray-500 hover:text-red-600 px-4 py-2.5">Reset</a>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="text-left px-4 py-3">Waktu import</th>
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
                        <th class="text-left px-4 py-3">Distribusi</th>
                        <th class="text-right px-4 py-3">Ship Total</th>
                        <th class="text-right px-4 py-3">Berat</th>
                        <th class="text-right px-4 py-3">Item Price</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-right px-4 py-3">Payment Channel</th>
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
                            $paymentDate = $item->payment_date ? \Carbon\Carbon::parse($item->payment_date) : null;
                            $estimasiPrint = $paymentDate ? $paymentDate->copy()->addHours(24) : null;
                            $jamPrint = $paymentDate ? $paymentDate->diffInHours(\Carbon\Carbon::now()) : 999;
                            $estimasiPersiapan = $paymentDate ? $paymentDate->copy()->addHours(72) : null;
                            $jamPersiapan = $paymentDate ? $paymentDate->diffInHours(\Carbon\Carbon::now()) : 999;
                        @endphp
                    <tr class="{{ $isProcessed ? 'processed-row' : '' }} hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $item->created_at ?? '-' }}</td>
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
                        <td class="px-4 py-3 font-medium text-center">
                            @if($estimasiPrint)
                                <span class="inline-block px-3 py-1.5 rounded-2xl text-sm font-semibold whitespace-nowrap {{ $jamPrint <= 24 ? 'badge-green' : ($jamPrint <= 48 ? 'badge-red' : 'badge-black') }}">
                                    {{ $estimasiPrint->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium text-center">
                            @if($estimasiPersiapan)
                                <span class="inline-block px-3 py-1.5 rounded-2xl text-sm font-semibold whitespace-nowrap {{ $jamPersiapan <= 72 ? 'badge-green' : ($jamPersiapan <= 96 ? 'badge-red' : 'badge-black') }}">
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
                        <td class="text-right px-4 py-3">{{ $item->jenis_bank ?? '-' }}</td>
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
                        <td class="px-4 py-3 text-center">
                            @if($isProcessed && $item->processed_at)
                                <span class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl text-sm font-medium whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($item->processed_at)->format('d/m/Y H:i') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="text-center px-4 py-3">
                            @if(!$isProcessed)
                                <a href="{{ route('order.jakarta-aktif.edit', $item->id) }}" class="text-blue-600 hover:text-blue-700 text-lg">✏️</a>
                            @else
                                <span class="text-emerald-600 font-medium">✅ Diproses</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="24" class="text-center py-16 text-gray-500">
                            Belum ada data Jakarta Aktif.<br>
                            Silakan klik tombol <strong>Sync JKT + Casdana</strong>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="px-6 py-4 bg-white border-t flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan <span class="font-medium">{{ $data->firstItem() }}</span> 
                    sampai <span class="font-medium">{{ $data->lastItem() }}</span> 
                    dari total <span class="font-medium">{{ $data->total() }}</span> data
                </div>
                <div>{{ $data->appends(request()->query())->links() }}</div>
            </div>
        </div>

        <!-- Modal tetap sama -->
       <!-- Modal -->
<div id="bulkModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-7xl mx-4 max-h-[92vh] flex flex-col" style="width: 95%; max-width: 1400px;">
        <div class="p-6 border-b flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-semibold">Edit & Proses Data Terpilih</h3>
                <p class="text-gray-600" id="modalCount">0 data dipilih</p>
            </div>
            <button onclick="hideBulkModal()" class="text-3xl text-gray-500 hover:text-gray-700">✕</button>
        </div>
        
        <div class="flex-1 overflow-auto p-6">
            <table class="w-full text-sm border border-gray-200 min-w-full" id="modalTable">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-3 text-left w-20">Status</th>
                        <th class="px-4 py-3 text-left w-28">Invoice</th>
                        <th class="px-4 py-3 text-left w-48">To Customer</th>
                        <th class="px-4 py-3 text-left">Alamat</th>                    <!-- Lebih lebar -->
                        <th class="px-4 py-3 text-left w-36">Payment Date</th>
                        <th class="px-4 py-3 text-left w-40">Payment Channel</th>
                        <th class="px-4 py-3 text-left w-32">Distribusi <span class="text-red-500">*</span></th>
                        <th class="px-4 py-3 text-left w-36">Jasa Kurir <span class="text-red-500">*</span></th>
                        <th class="px-4 py-3 text-left w-32">Service</th>
                        <th class="px-4 py-3 text-left w-36">Vendor</th>
                        <th class="px-4 py-3 text-left w-52">Catatan</th>
                    </tr>
                </thead>
                <tbody id="modalTableBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>

        <div class="p-6 border-t bg-gray-50 flex justify-end gap-3">
            <button onclick="hideBulkModal()" class="px-6 py-3 text-gray-600 hover:bg-gray-100 rounded-2xl">Batal</button>
            <button onclick="executeBulkAction()" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-indigo-700">💾 Simpan & Kunci Semua Data</button>
        </div>
    </div>
</div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
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

    $(document).ready(function() {
        checkFilterStatus();
        checkProcessButtonVisibility();

        $('input[name="start_date"], input[name="end_date"]').on('change', function() {
            checkFilterStatus();
            setTimeout(checkProcessButtonVisibility, 700);
        });

        // Cek ulang setelah halaman load
        setTimeout(checkProcessButtonVisibility, 1000);
    });

    function processAllFilteredData() {
        const startDate = $('input[name="start_date"]').val();
        const endDate   = $('input[name="end_date"]').val();

        if (!startDate || !endDate) {
            alert('❌ Harap isi Dari Tanggal dan Sampai Tanggal terlebih dahulu!');
            return;
        }

        $.ajax({
            url: '{{ route("order.jakarta-aktif.filtered-ids") }}',
            method: 'GET',
            data: {
                start_date: startDate,
                end_date: endDate,
                id_pesan: $('input[name="id_pesan"]').val() || '',
                kirim: $('input[name="kirim"]').val() || '',
                nama_unit: $('input[name="nama_unit"]').val() || ''
            },
            success: function(response) {
                if (response.count === 0) {
                    alert('Tidak ada data yang belum diproses.');
                    return;
                }
                selectedIds = response.ids;
                $('#selectedCount').text(`${response.count} data akan diproses`);
                loadModalData();
            },
            error: function() {
                alert('Gagal mengambil data.');
            }
        });
    }

    function loadModalData() {
    $.ajax({
        url: '{{ route("order.jakarta-aktif.get-modal-data") }}',
        method: 'POST',
        data: {
            ids: selectedIds,
            _token: '{{ csrf_token() }}'
        },
        success: function(items) {
            let html = '';
            
            items.forEach(item => {
                const isLocked = Boolean(item.is_processed);
                const currentDistribusi = (item.status_kirim || 'Dikirim').trim();

                let distribusiHtml, jasaKurirHtml, serviceKurirHtml, alamatHtml, catatanHtml;

                if (isLocked) {
                    distribusiHtml = `<span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold bg-emerald-100 text-emerald-700 rounded-2xl">${currentDistribusi}</span>`;
                    jasaKurirHtml = `<span class="text-sm text-gray-500 font-medium">— Terkunci —</span>`;
                    serviceKurirHtml = `<span class="text-sm text-gray-500 font-medium">— Terkunci —</span>`;
                    alamatHtml = `<span class="text-sm text-gray-600">${item.kirim || '-'}</span>`;
                    catatanHtml = `<span class="text-xs text-gray-500 italic">Sudah diproses ${item.processed_at ? 'pada ' + item.processed_at : ''}</span>`;
                } else {
                    distribusiHtml = `<span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold bg-blue-100 text-blue-700 rounded-2xl">${currentDistribusi}</span>`;

                    jasaKurirHtml = `<select class="jasa-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500"><option value="">Pilih Jasa Kurir</option></select>`;

                    serviceKurirHtml = `<input type="text" class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" placeholder="REG, YES, CTC, dll">`;

                    // ALAMAT BISA DIEDIT
                    alamatHtml = `<input type="text" class="alamat w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" value="${item.kirim || ''}" placeholder="Masukkan alamat lengkap...">`;

                    catatanHtml = `<input type="text" class="catatan w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" placeholder="Catatan tambahan...">`;
                }

                html += `
                    <tr data-id="${item.id}" data-distribusi="${currentDistribusi}" 
                        class="${isLocked ? 'processed-row' : 'hover:bg-gray-50'}">
                        <td class="px-4 py-3">${item.status_pembayaran || '-'}</td>
                        <td class="px-4 py-3 font-medium">${item.invoice}</td>
                        <td class="px-4 py-3">${item.to_customer}</td>
                        <td class="px-4 py-3">${alamatHtml}</td>
                        <td class="px-4 py-3">${item.payment_date}</td>
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
        error: function(xhr) {
            console.error(xhr.responseText);
            alert('Gagal memuat data ke modal.');
        }
    });
}

    function initModalLogic() {
        // Setup berdasarkan Distribusi
        $('#modalTableBody tr:not(.processed-row)').each(function() {
            const row = $(this);
            const distribusi = row.data('distribusi');
            const jasaSelect = row.find('.jasa-kurir');
            const serviceInput = row.find('.service-kurir');

            if (distribusi === 'Diambil') {
                jasaSelect.html('<option value="Ambil Sendiri" selected>Ambil Sendiri</option>').prop('disabled', true);
                serviceInput.prop('disabled', true).val('');
            } else {
                jasaSelect.html(`
                    <option value="">Pilih Jasa Kurir</option>
                    <option value="JNE">JNE</option>
                    <option value="Lion Parcel">Lion Parcel</option>
                    <option value="TIKI">TIKI</option>
                `).prop('disabled', false);
                serviceInput.prop('disabled', false);
            }
        });

        // Event listener
        $(document).off('change', '.jasa-kurir').on('change', '.jasa-kurir', function() {
            const jasa = $(this).val();
            const service = $(this).closest('tr').find('.service-kurir');
            
            if (jasa === 'Driver') {
                service.val('').prop('disabled', true).attr('placeholder', 'Tidak perlu diisi');
            } else {
                service.prop('disabled', false).attr('placeholder', 'REG, YES, CTC, dll');
            }
            checkSaveButtonState();
        });

        // Cek saat service diubah juga
        $(document).off('input change', '.service-kurir').on('input change', '.service-kurir', checkSaveButtonState);

        // Cek pertama kali
        setTimeout(checkSaveButtonState, 300);
    }

    // Validasi lengkap: Jasa Kurir & Service harus diisi
    function checkSaveButtonState() {
        let isValid = true;

        $('#modalTableBody tr:not(.processed-row)').each(function() {
            const distribusi = $(this).data('distribusi');
            const jasaKurir = $(this).find('.jasa-kurir').val();
            const service = $(this).find('.service-kurir').val().trim();

            if (!jasaKurir) {
                isValid = false;
                return false;
            }

            // Untuk Dikirim, Service juga harus diisi
            if (distribusi === 'Dikirim' && !service) {
                isValid = false;
                return false;
            }
        });

        const saveButton = $('.bg-indigo-600');
        if (isValid) {
            saveButton.prop('disabled', false).removeClass('opacity-50 cursor-not-allowed');
        } else {
            saveButton.prop('disabled', true).addClass('opacity-50 cursor-not-allowed');
        }
    }

    function hideBulkModal() {
        $('#bulkModal').addClass('hidden');
    }

    function executeBulkAction() {
    if ($('.bg-indigo-600').prop('disabled')) {
        alert('❌ Harap isi Jasa Kurir dan Service untuk semua data yang belum terkunci!');
        return;
    }

    if (!confirm(`Yakin ingin memproses ${selectedIds.length} data?`)) return;

    const updates = [];
    $('#modalTableBody tr').each(function() {
        const distribusiText = $(this).find('td:nth-child(7) span').text().trim(); // sesuaikan urutan jika perlu

        updates.push({
            id: $(this).data('id'),
            status_kirim: distribusiText,
            jasa_kurir: $(this).find('.jasa-kurir').val() || '',
            service_kurir: $(this).find('.service-kurir').val() || '',
            alamat_pengiriman: $(this).find('.alamat').val() || '',   // ← TAMBAHKAN INI
            catatan: $(this).find('.catatan').val() || ''
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

    function clearSelection() {
        selectedIds = [];
        window.location.href = '{{ route("order.jakarta-aktif") }}';
    }
</script>
</body>
</html>