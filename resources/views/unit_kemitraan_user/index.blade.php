<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Unit Kemitraan + User Export - biMBA AIUEO</title>
    
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
            min-width: 1800px;
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
            max-width: 180px;
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
                <h1 class="text-3xl font-bold text-gray-800">Database Unit Kemitraan + User Export</h1>
                <p class="text-gray-600">Matching No Cab dengan First Name di User Export</p>
            </div>
            <div class="flex gap-3">
                <button onclick="document.getElementById('importUnitForm').classList.toggle('hidden')"
                        class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700">
                    📤 Import Unit Kemitraan
                </button>
                <button onclick="document.getElementById('importUserForm').classList.toggle('hidden')"
                        class="bg-purple-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-purple-700">
                    📤 Import User Export
                </button>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-3xl shadow p-6 mb-8">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No Cab</label>
                    <input type="text" name="no_cab" value="{{ request('no_cab') }}" 
                           class="w-full border border-gray-300 rounded-xl px-4 py-3" placeholder="Cari No Cab...">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIM / No Induk Mitra</label>
                    <input type="text" 
                        name="no_induk_mitra" 
                        value="{{ request('no_induk_mitra') }}" 
                        class="w-full border border-gray-300 rounded-xl px-4 py-3" 
                        placeholder="Cari NIM / No Induk Mitra...">
                </div>

                <!-- Status Pengelolaan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Pengelolaan</label>
                    <select name="status_pengelolaan" class="w-full border border-gray-300 rounded-xl px-4 py-3">
                        <option value="">Semua</option>
                        <option value="Unit Aktif" {{ request('status_pengelolaan') == 'Unit Aktif' ? 'selected' : '' }}>Unit Aktif</option>
                        <option value="Unit Pasif" {{ request('status_pengelolaan') == 'Unit Pasif' ? 'selected' : '' }}>Unit Pasif</option>
                        <option value="all" {{ request('status_pengelolaan') == 'all' ? 'selected' : '' }}>Tampilkan Semua</option>
                    </select>
                </div>

                <!-- Mitra Pengelolaan -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Mitra Pengelolaan</label>
                    <select name="mitra_pengelolaan" class="w-full border border-gray-300 rounded-xl px-4 py-3">
                        <option value="">Semua</option>
                        <option value="YPAI" {{ request('mitra_pengelolaan') == 'YPAI' ? 'selected' : '' }}>YPAI</option>
                        <option value="PUW1 | OPS1" {{ request('mitra_pengelolaan') == 'PUW1 | OPS1' ? 'selected' : '' }}>PUW1 | OPS1</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="MM" {{ request('status') == 'MM' ? 'selected' : '' }}>MM</option>
                        <option value="MM 1" {{ request('status') == 'MM 1' ? 'selected' : '' }}>MM 1</option>
                        <option value="Aktif 1" {{ request('status') == 'Aktif 1' ? 'selected' : '' }}>Aktif 1</option>
                        <option value="MK 1" {{ request('status') == 'MK 1' ? 'selected' : '' }}>MK 1</option>
                        <option value="MK" {{ request('status') == 'MK' ? 'selected' : '' }}>MK</option>
                        <option value="MK Rinda" {{ request('status') == 'MK Rinda' ? 'selected' : '' }}>MK Rinda</option>
                        <option value="MKU" {{ request('status') == 'MKU' ? 'selected' : '' }}>MKU</option>
                        <option value="MKU 1" {{ request('status') == 'MKU 1' ? 'selected' : '' }}>MKU 1</option>
                        <option value="E-biMBA Aktif" {{ request('status') == 'E-biMBA Aktif' ? 'selected' : '' }}>E-biMBA Aktif</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Matching First Name</label>
                    <select name="matching_status" class="w-full border border-gray-300 rounded-xl px-4 py-3">
                        <option value="">Semua</option>
                        <option value="ditemukan" {{ request('matching_status') == 'ditemukan' ? 'selected' : '' }}>✅ Ditemukan</option>
                        <option value="tidak_ditemukan" {{ request('matching_status') == 'tidak_ditemukan' ? 'selected' : '' }}>❌ Tidak Ditemukan</option>
                    </select>
                </div>

                <div class="xl:col-span-6 flex gap-3">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-blue-700">
                        🔍 Terapkan Filter
                    </button>
                    <a href="{{ route('unit-kemitraan-user.index') }}" 
                       class="bg-gray-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-600">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- TABEL -->
        <div class="bg-white rounded-3xl shadow table-container">
            <table class="text-sm">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="narrow uppercase">No Cab</th>
                        <th class="medium uppercase">BiMBA AIUEO Unit</th>
                        <th class="medium uppercase">Nama Mitra</th>
                        <th class="narrow uppercase">Jenis Unit</th>
                        <th class="narrow uppercase">Status Pengelolaan</th>
                        <th class="narrow uppercase">Mitra Pengelolaan</th>
                        <th class="narrow uppercase">No Induk Mitra</th>
                        <th class="medium uppercase">No HP</th>
                        <th class="wide uppercase">Matching First Name</th>
                        <th class="wide uppercase">User Email</th>
                        <th class="narrow uppercase">Display Name</th>
                        <th class="narrow text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($unitKemitraans as $unit)
                        @php
                            $matched = $userExports->first(function($u) use ($unit) {
                                return $u->first_name && str_contains($u->first_name, (string)$unit->no_cab);
                            });
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="font-medium">{{ $unit->no_cab ?? '-' }}</td>
                            <td>{{ $unit->bimba_aiueo_unit ?? '-' }}</td>
                            <td class="truncate">{{ $unit->nama_mitra ?? '-' }}</td>
                            <td>{{ $unit->status ?? '-' }}</td>
                            <!--untuk unit pengelolaan-->
                            <td>{{ $unit->status_pengelolaan ?? '-' }}</td>
                            <td>{{ $unit->mitra_pengelolaan ?? '-' }}</td>
                            <td>{{ $unit->no_induk_mitra ?? '-' }}</td>
                            <td>{{ $unit->no_hp ?? '-' }}</td>
                            <td class="truncate">
                                @if($matched)
                                    <span class="text-green-600 font-medium">{{ $matched->first_name }}</span>
                                @else
                                    <span class="text-red-500 text-sm">Tidak ditemukan</span>
                                @endif
                            </td>
                            <td class="truncate text-blue-600">{{ $matched ? $matched->user_email : '-' }}</td>
                            <td class="truncate">{{ $matched ? $matched->display_name : '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('unit-kemitraan.show', $unit) }}" class="text-blue-600 hover:underline">👁</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-16 text-gray-500">
                                Belum ada data. Silakan import file Excel.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-8 flex justify-between items-center">
            <div class="text-gray-600">
                Menampilkan <strong>{{ $unitKemitraans->firstItem() ?? 0 }}</strong> 
                sampai <strong>{{ $unitKemitraans->lastItem() ?? 0 }}</strong> 
                dari <strong>{{ $unitKemitraans->total() }}</strong> data
            </div>
            <div>
                {{ $unitKemitraans->links() }}
            </div>
        </div>

    </div>
</body>
</html>