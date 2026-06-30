<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Stokis Mitra - biMBA AIUEO</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        .table-container {
            overflow-x: auto;
            max-height: 70vh;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 1400px;
        }
        
        th, td {
            padding: 12px 8px;
            font-size: 0.875rem;
            vertical-align: top;
            border-bottom: 1px solid #e5e7eb;
        }
        
        th {
            background-color: #f8fafc;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 20;
            font-size: 0.8rem;
        }
        
        tr:hover { background-color: #f1f5f9; }
        
        .truncate {
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>
</head>

<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Database Stokis Mitra</h1>
            <p class="text-gray-500">Database Stokis Mitra Apps</p>
        </div>

        <!-- Tombol Import -->
        <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-2xl font-semibold flex items-center gap-2">
            📤 Import Excel
        </button>
    </div>

    <!-- ==================== FORM IMPORT ==================== -->
    <div id="importForm" class="hidden bg-white rounded-3xl shadow p-6 mb-8">
        <h3 class="font-semibold text-lg mb-4">Import Data Stokis dari Excel</h3>
        
        <form action="{{ route('stokis.import') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label class="block text-sm text-gray-600 mb-1">Pilih File Excel</label>
                    <input type="file" name="file" 
                           accept=".xlsx,.xls,.csv"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-2xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>
                
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-2xl font-semibold">
                    🚀 Upload & Import
                </button>
                
                <button type="button" onclick="document.getElementById('importForm').classList.add('hidden')"
                        class="text-gray-500 hover:text-gray-700 px-4 py-3">
                    Batal
                </button>
            </div>
        </form>
        
        <p class="text-xs text-gray-500 mt-3">
            Format yang didukung: .xlsx, .xls, .csv • Maksimal 10MB
        </p>
    </div>
    <!-- ==================== END FORM IMPORT ==================== -->

    <!-- Search & Per Page -->
    <div class="mb-6 flex flex-wrap gap-4 items-center justify-between">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') ?? '' }}"
                   placeholder="Cari No Cab, Nama Stokis, Nama Mitra, Email..." 
                   class="border border-gray-300 rounded-2xl px-5 py-3 focus:outline-none focus:border-blue-500 text-base w-80">
            
            <button type="submit" class="bg-gray-700 hover:bg-gray-800 text-white px-8 py-3 rounded-2xl font-semibold">
                🔍 Cari
            </button>

            @if(request('search'))
                <a href="{{ route('stokis.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold flex items-center">
                Reset
                </a>
            @endif
        </form>

        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label class="text-sm text-gray-600 whitespace-nowrap">Tampilkan:</label>
            <select name="per_page" onchange="this.form.submit()" 
                    class="border border-gray-300 rounded-2xl px-4 py-3 text-base focus:outline-none">
                <option value="5"  {{ $perPage == 5 ? 'selected' : '' }}>5</option>
                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20</option>
                <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
            </select>
        </form>
    </div>

    <!-- Tabel -->
    <div class="bg-white rounded-3xl shadow table-container">
        <table class="w-full text-sm">
            <!-- ... isi tabel tetap sama seperti sebelumnya ... -->
            <thead>
                <tr>
                    <th>No Cab</th>
                    <th>Nama Stokis Kemitraan</th>
                    <th>Nama Stokis biMBA Shop</th>
                    <th>No Induk Mitra</th>
                    <th>Nama Mitra</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Form Pembukaan Unit</th>
                    <th>Kerjasama English</th>
                    <th>DB Kemitraan & Shop</th>
                    <th>Unit biMBA</th>
                    <th>Kerjasama MK/MM</th>
                    <th>Pengajuan Perubahan</th>
                    <th>Item SKU</th>
                    <th>Ops Stokist</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($stokis as $item)
                    <tr>
                        <td>{{ $item->no_cab ?? '-' }}</td>
                        <td class="truncate">{{ $item->nama_stokis_db_kemitraan ?? '-' }}</td>
                        <td class="truncate">{{ $item->nama_stokis_db_bimbashop ?? '-' }}</td>
                        <td>{{ $item->no_induk_mitra ?? '-' }}</td>
                        <td>{{ $item->nama_mitra ?? '-' }}</td>
                        <td class="text-blue-600">{{ $item->email ?? '-' }}</td>
                        <td>{{ $item->no_hp ?? '-' }}</td>
                        <td class="truncate">{{ $item->related_form_pembukaan_unit_aktif ?? '-' }}</td>
                        <td class="truncate">{{ $item->related_formulir_kerjasama_english ?? '-' }}</td>
                        <td class="truncate">{{ $item->db_kemitraan_db_bimbashop ?? '-' }}</td>
                        <td class="truncate">{{ $item->related_unit_bimba_aiueo ?? '-' }}</td>
                        <td class="truncate">{{ $item->related_formulir_kerjasama_mk_mm ?? '-' }}</td>
                        <td class="truncate">{{ $item->related_pengajuan_perubahan ?? '-' }}</td>
                        <td>{{ is_array($item->item_sku) ? implode(', ', $item->item_sku) : ($item->item_sku ?? '-') }}</td>
                        <td>{{ $item->ops_stokist ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="15" class="text-center py-20 text-gray-500">
                            Belum ada data.<br>
                            Silakan import file Excel terlebih dahulu.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-between items-center text-base">
        <div class="text-gray-600">
            Menampilkan 
            <span class="font-semibold">{{ $stokis->firstItem() ?? 0 }}</span> 
            sampai 
            <span class="font-semibold">{{ $stokis->lastItem() ?? 0 }}</span> 
            dari total 
            <span class="font-semibold">{{ $stokis->total() }}</span> data
        </div>

        <div class="flex justify-center">
            {{ $stokis->links('pagination::tailwind') }}
        </div>
    </div>

</div>
</body>
</html>