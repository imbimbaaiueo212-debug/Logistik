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
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-green { background-color: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-yellow { background-color: #fef3c7; color: #92400e; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-red { background-color: #ff0000; color: rgb(255, 255, 255); padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
        .badge-black { background-color: #1f2937; color: #f3f4f6; padding: 2px 8px; border-radius: 9999px; font-size: 0.8rem; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Top Navigation -->
    @include('partials.top-nav')

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Jakarta Aktif</h1>
                <p class="text-gray-600">Import & Kelola Data Order Jakarta Aktif</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('order.unit-pasif') }}" class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
                    ← Kembali ke Dashboard
                </a>
                <button onclick="document.getElementById('importForm').classList.toggle('hidden')" 
                        class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700 flex items-center gap-2">
                    📤 Import Data Baru
                </button>
                <a href="{{ route('order.jakarta-aktif.export') }}" class="bg-green-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-green-700 flex items-center gap-2">
                    📥 Export Excel
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
                        <option value="5" {{ request('per_page') == 5 ? 'selected' : '' }}>5</option>    
                        <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                        <option value="300" {{ request('per_page') == 300 ? 'selected' : '' }}>300</option>
                        <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
                        <option value="1000" {{ request('per_page') == 1000 ? 'selected' : '' }}>1000</option>
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

        <!-- Form Import -->
        <div id="importForm" class="hidden bg-white rounded-3xl shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Upload File Excel</h2>
            <form action="{{ route('order.jakarta-aktif.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File</label>
                        <input type="file" name="file" 
                               class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                               accept=".xlsx,.xls" required>
                    </div>
                    <button type="submit" class="bg-green-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-green-700">
                        🚀 Import Sekarang
                    </button>
                </div>
                <p class="text-xs text-gray-500 mt-2">Format yang didukung: .xlsx, .xls (max 10MB)</p>
            </form>
        </div>

        <!-- Tabel Data -->
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
                        <th class="text-left px-4 py-3">Estimasi Print PL (1x24)</th>
                        <th class="text-left px-4 py-3">Estimasi Persiapan (3x24)</th>
                        <th class="text-left px-4 py-3">Jasa Kurir</th>
                        <th class="text-left py-4 py-3">Service Kurir</th>
                        <th class="text-left px-4 py-3">Kirim</th>
                        <th class="text-right px-4 py-3">Ship Total</th>
                        <th class="text-right px-4 py-3">Berat</th>
                        <th class="text-right px-4 py-3">Item Price</th>
                        <th class="text-right px-4 py-3">Total</th>
                        <th class="text-left px-4 py-3">Status Bayar</th>
                        <th class="text-left px-4 py-3">Status biMBAShop</th>
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $item)
                        @php
                            $now = \Carbon\Carbon::now();
                            $paymentDate = $item->payment_date ? \Carbon\Carbon::parse($item->payment_date) : null;

                            // Estimasi Print PL = +24 jam dari Payment Date
                            $estimasiPrint = $paymentDate ? $paymentDate->copy()->addHours(24) : null;
                            $jamPrint = $paymentDate ? $paymentDate->diffInHours($now) : 999;

                            // Estimasi Persiapan = +72 jam (3x24) dari Payment Date
                            $estimasiPersiapan = $paymentDate ? $paymentDate->copy()->addHours(72) : null;
                            $jamPersiapan = $paymentDate ? $paymentDate->diffInHours($now) : 999;
                        @endphp
                    <tr class="hover:bg-gray-50">
                        <!-- Checkbox -->
                        <td class="text-center px-3 py-3">
                            <input type="checkbox" name="selected[]" value="{{ $item->id }}" class="w-4 h-4 row-checkbox">
                        </td>

                        <td class="px-4 py-3 font-medium">{{ $item->id_pesan ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->nama_unit ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->billing_last_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->kirim ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->kab_kota_provinsi ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->pesanan ?? '-' }}</td>

                        <!-- Order Date -->
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($item->tgl_pesan)
                                <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded-xl text-sm">
                                    {{ \Carbon\Carbon::parse($item->tgl_pesan)->format('d/m/Y H:i') }}
                                </span>
                            @else
                                -
                            @endif
                        </td>

                        

                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($item->payment_date)
                                {{ \Carbon\Carbon::parse($item->payment_date)->format('d/m/Y H:i') }}
                            @else
                                -
                            @endif
                        </td>

                        <!-- ESTIMASI PRINT PL (1x24 jam) -->
                        <td class="px-4 py-3 font-medium">
                            @if($estimasiPrint)
                                @if($jamPrint <= 24)
                                    <span class="badge-green px-3 py-1 rounded-xl text-xs font-semibold">
                                        {{ $estimasiPrint->format('d/m/Y H:i') }}
                                    </span>
                                @elseif($jamPrint <= 48)   <!-- Lewat 1 hari -->
                                    <span class="badge-red px-3 py-1 rounded-xl text-xs font-semibold">
                                        {{ $estimasiPrint->format('d/m/Y H:i') }}
                                    </span>
                                @else                      <!-- Sudah sangat lewat -->
                                    <span class="badge-black px-3 py-1 rounded-xl text-xs font-semibold">
                                        {{ $estimasiPrint->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <!-- ESTIMASI PERSIAPAN (3x24 jam) -->
                        <td class="px-4 py-3 font-medium">
                            @if($estimasiPersiapan)
                                @if($jamPersiapan <= 72)
                                    <span class="badge-green px-3 py-1 rounded-xl text-xs font-semibold">
                                        {{ $estimasiPersiapan->format('d/m/Y H:i') }}
                                    </span>
                                @elseif($jamPersiapan <= 96)   <!-- Lewat 1 hari setelah 72 jam -->
                                    <span class="badge-red px-3 py-1 rounded-xl text-xs font-semibold">
                                        {{ $estimasiPersiapan->format('d/m/Y H:i') }}
                                    </span>
                                @else                          <!-- Sudah sangat lewat -->
                                    <span class="badge-black px-3 py-1 rounded-xl text-xs font-semibold">
                                        {{ $estimasiPersiapan->format('d/m/Y H:i') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">{{ $item->ekspedisi ?? '-' }}</td>

                        <td class="px-4 py-3">{{ $item->service_pengiriman ?? '-'}}</td>

                        <td class="px-4 py-3">
                            @if($item->status_kirim === 'Dikirim')
                                <span class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl text-sm font-semibold">Dikirim</span>
                            @else
                                <span class="inline-block bg-amber-100 text-amber-700 px-3 py-1 rounded-xl text-sm font-semibold">Diambil</span>
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
                                <span class="text-gray-400 text-sm italic">— Belum Sync —</span>
                            @endif
                        </td>

                        <td class="px-4 py-3">
                            @php $statusPesan = $item->status_pesan ?? null; @endphp
                            @if($statusPesan)
                                @if(in_array(strtolower($statusPesan), ['completed','success','paid','settled']))
                                    <span class="inline-block bg-emerald-100 text-emerald-700 px-3 py-1 rounded-xl text-sm font-semibold">{{ $statusPesan }}</span>
                                @elseif(in_array(strtolower($statusPesan), ['cancelled','failed']))
                                    <span class="inline-block bg-red-100 text-red-700 px-3 py-1 rounded-xl text-sm font-semibold">{{ $statusPesan }}</span>
                                @else
                                    <span class="inline-block bg-gray-100 text-gray-700 px-3 py-1 rounded-xl text-sm font-semibold">{{ $statusPesan }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="text-center px-4 py-3">
                            <a href="{{ route('order.jakarta-aktif.edit', $item->id) }}" class="text-blue-600 hover:text-blue-700 text-lg">✏️</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="20" class="text-center py-16 text-gray-500">
                            Belum ada data Jakarta Aktif.<br>
                            Silakan klik tombol <strong>Sync JKT + Casdana</strong> di atas.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->count() > 0)
        <div class="mt-6 text-sm text-gray-600 flex justify-between items-center">
            <div>
                Menampilkan <strong>{{ $data->count() }}</strong> data 
                <span class="text-gray-400">(Total: {{ $data->total() }} data)</span>
            </div>
            <div>{{ $data->links() }}</div>
        </div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $('#selectAll').on('change', function() {
                $('.row-checkbox').prop('checked', this.checked);
            });
        });
    </script>

</body>
</html>