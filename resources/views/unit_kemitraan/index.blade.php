<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Unit Kemitraan - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Poppins', sans-serif; }
        table { border-collapse: collapse; }
        th, td { padding: 10px 6px; font-size: 0.8rem; }
        th { background-color: #f1f5f9; font-weight: 600; white-space: nowrap; position: sticky; top: 0; z-index: 10; }
        tr:hover { background-color: #f8fafc; }
        .truncate { max-width: 160px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .small-text { font-size: 0.75rem; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Top Navigation -->
    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Database Unit Kemitraan</h1>
                <p class="text-gray-600">Semua kolom dari tabel unit_kemitraan</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('unit-kemitraan.create') }}" 
                   class="bg-blue-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-blue-700">
                    ➕ Tambah Unit Baru
                </a>
                <button onclick="document.getElementById('importForm').classList.toggle('hidden')"
                        class="bg-green-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-green-700 flex items-center gap-2">
                    📤 Import dari Excel
                </button>
            </div>
        </div>

        {{-- ==================== NOTIFIKASI ==================== --}}
@if(session('success'))
    <div class="mb-6 p-4 bg-green-100 border border-green-400 text-green-700 rounded-2xl">
        <strong>✅ Berhasil!</strong> {{ session('success') }}
    </div>
@endif

@if(session('warning'))
    <div class="mb-6 p-4 bg-yellow-100 border border-yellow-400 text-yellow-800 rounded-2xl">
        <strong>⚠️ Perhatian!</strong> {{ session('warning') }}
        
        @if(session('import_errors'))
            <div class="mt-3 text-sm">
                <p class="font-semibold mb-1">Detail error:</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach(session('import_errors') as $err)
                        <li>
                            No Cab: <strong>{{ $err['no_cab'] }}</strong> 
                            (ID: {{ $err['id_record'] }}) → {{ $err['reason'] }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif

@if(session('error'))
    <div class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded-2xl">
        <strong>❌ Gagal!</strong> {{ session('error') }}
    </div>
@endif
{{-- ==================== END NOTIFIKASI ==================== --}}

        
        <!-- ==================== FORM IMPORT ==================== -->
        <div id="importForm" class="hidden bg-white rounded-3xl shadow p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Import Data Unit Kemitraan</h2>
                        <form action="{{ route('unit-kemitraan.import.store') }}" 
                method="POST" 
                enctype="multipart/form-data">
                
                @csrf
                
                <div class="flex gap-4 items-end">
                    <div class="flex-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pilih File Excel / CSV</label>
                        <input type="file" name="import_file" 
                            class="..." 
                            accept=".xlsx,.xls,.csv" required>
                    </div>
                    <button type="submit" 
                            class="bg-green-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-green-700">
                        🚀 Import Sekarang
                    </button>
                </div>
            </form>
        </div>
        <!-- ==================== END FORM IMPORT ==================== -->
        <!-- ==================== FILTER FORM ==================== -->
        <div class="bg-white rounded-3xl shadow p-6 mb-8">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">No Cab</label>
            <input type="text" name="no_cab" 
                   value="{{ request('no_cab') }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                   placeholder="Cari No Cab...">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Mitra</label>
            <input type="text" name="nama_mitra" 
                   value="{{ request('nama_mitra') }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                   placeholder="Nama Mitra...">
        </div>

        <!-- Filter Status Pengelolaan -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Pengelolaan</label>
            <select name="status_pengelolaan" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500">
                <option value="">Semua Status Pengelolaan</option>
                <option value="Unit Aktif" {{ request('status_pengelolaan') == 'Unit Aktif' ? 'selected' : '' }}>Unit Aktif</option>
                <option value="Unit Pasif" {{ request('status_pengelolaan') == 'Unit Pasif' ? 'selected' : '' }}>Unit Pasif</option>
                <option value="all" {{ request('status_pengelolaan') == 'all' ? 'selected' : '' }}>Tampilkan Semua</option>
            </select>
        </div>

        <!-- Filter Mitra Pengelolaan -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mitra Pengelolaan</label>
            <select name="mitra_pengelolaan" class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500">
                <option value="">Semua Mitra Pengelolaan</option>
                <option value="YPAI" {{ request('mitra_pengelolaan') == 'YPAI' ? 'selected' : '' }}>YPAI</option>
                <option value="PUW1 | ops1" {{ request('mitra_pengelolaan') == 'PUW1 | ops1' ? 'selected' : '' }}>PUW1 | ops1</option>

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
            <label class="block text-sm font-medium text-gray-700 mb-1">Provinsi</label>
            <input type="text" name="provinsi" 
                   value="{{ request('provinsi') }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                   placeholder="Provinsi...">
        </div>

        <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Pencarian Umum</label>
            <input type="text" name="search" 
                   value="{{ request('search') }}"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                   placeholder="Cari No Cab, Nama Unit, Alamat...">
        </div>

        <div class="flex items-end gap-3 lg:col-span-2">
            <button type="submit" 
                    class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-blue-700 flex-1 md:flex-none">
                🔍 Terapkan Filter
            </button>
            <a href="{{ route('unit-kemitraan.index') }}" 
               class="bg-gray-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-600">
                Reset
            </a>
        </div>
    </form>
</div>
        <!-- ==================== END FILTER ==================== -->

        <!-- Tabel Utama -->
        <div class="bg-white rounded-3xl shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b-2 border-gray-300">
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider text-center">ID Record</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No Cab</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold text-xs tracking-wider">biMBA AIUEO Unit</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Jenis Unit</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Status Pengelolaan</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Mitra Pengelolaan</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Status Operasional</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No Telp Unit</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Email Unit</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Alamat Unit</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">RT</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">RW</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Provinsi</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Kab/Kota</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Kecamatan</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Kel/Desa</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Kode Pos</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No Induk Mitra</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Nama Mitra</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Email Mitra</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No HP Mitra</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Bank</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No Rekening</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Atas Nama</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No Akta</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Tgl Akta</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Nilai Lisensi</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">% Mitra</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">% YPAI</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Awal</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Akhir</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Perpanjang</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Tutup</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">JMP</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">LPM</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Pengembalian</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Tanggal VA BCA</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">VA Mandiri Royalti</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">VA Mandiri Lisensi</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Marketing</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Koorwil/KPK/Sos</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Detail</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Note</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Updated By</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Last Updated</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Sisa 3</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Sisa 1</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Sisa 2</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Sisa 4</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Sisa F</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Masa Kontrak</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Sisa</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Sisa RR</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No Lokasi</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Kategori Perubahan</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">PDF</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Update PDF</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Vendor Stokis 1</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Vendor Stokis 2</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Alamat Saat Ini</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Alamat Mitra</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">No Cab BiMBA Unit</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">LEN Perubahan Unit</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Kirim Email Lisensi</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Jakarta</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Tanggal Update</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Akun Facebook</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Akun Instagram</th>
                        <th class="whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Akun Media Sosial</th>
                        <th class="text-center whitespace-normal text-wrap px-4 py-4 font-bold uppercase text-xs tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 text-sm">
    @forelse($unitKemitraans as $unit)
    <tr class="hover:bg-gray-50">
        <td class="font-medium px-3 py-3 text-center">{{ $unit->id_record }}</td>
        <td class="px-3 py-3">{{ $unit->no_cab ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->bimba_aiueo_unit ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->status ?? '-' }}</td>
        
        <!-- Status Pengelolaan -->
        <td class="text-center px-3 py-3">
            @if($unit->status_pengelolaan == 'Unit Aktif')
                <span class="px-2 py-1 rounded-full bg-green-100 text-green-700 text-xs font-semibold">Unit Aktif</span>
            @elseif($unit->status_pengelolaan == 'Unit Pasif')
                <span class="px-2 py-1 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Unit Pasif</span>
            @else
                <span class="text-gray-400">-</span>
            @endif
        </td>

        <td class="px-3 py-3">{{ $unit->mitra_pengelolaan ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->ops ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->no_telp_unit ?? '-' }}</td>
        
        <!-- Kolom yang sering panjang -->
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->email_unit ?? '-' }}</td>
        <td class="px-3 py-3 whitespace-normal break-words max-w-md">{{ $unit->alamat_unit ?? '-' }}</td>
        
        <td class="px-3 py-3 text-center">{{ $unit->rt ?? '-' }}</td>
        <td class="px-3 py-3 text-center">{{ $unit->rw ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->provinsi ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->kab_kota ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->kecamatan ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->kel_desa ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->kode_pos ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->no_induk_mitra ?? '-' }}</td>
        
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->nama_mitra ?? '-' }}</td>
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->email ?? $unit->email_mitra ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->no_hp ?? $unit->no_hp_mitra ?? '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->bank ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->no_rekening ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->atas_nama ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->no_akta ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->tgl_akta ? $unit->tgl_akta->format('d/m/Y') : '-' }}</td>
        
        <td class="text-right px-3 py-3">{{ number_format($unit->nilai_lisensi ?? 0, 2) }}</td>
        <td class="text-right px-3 py-3">{{ $unit->persen_mitra ?? '-' }}</td>
        <td class="text-right px-3 py-3">{{ $unit->persen_ypai ?? '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->awal ? $unit->awal->format('d/m/Y') : '-' }}</td>
        <td class="px-3 py-3">{{ $unit->akhir ? $unit->akhir->format('d/m/Y') : '-' }}</td>
        <td class="px-3 py-3">{{ $unit->perpanjang ? $unit->perpanjang->format('d/m/Y') : '-' }}</td>
        <td class="px-3 py-3">{{ $unit->tutup ? $unit->tutup->format('d/m/Y') : '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->jmp ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->lpm ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->pengembalian ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->tanggal ? $unit->tanggal->format('d/m/Y') : '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->va_mandiri_royalti ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->va_mandiri_lisensi ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->marketing ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->koorwil_kpk_sos ?? '-' }}</td>
        
        <td class="px-3 py-3 whitespace-normal break-words max-w-xs">{{ $unit->detail ?? '-' }}</td>
        <td class="px-3 py-3 whitespace-normal break-words max-w-xs">{{ $unit->note ?? '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->updated_by ?? '-' }}</td>
        <td class="px-3 py-3 text-sm">{{ $unit->last_updated ? $unit->last_updated->format('d/m/Y H:i') : '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->sisa_3 ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->sisa_1 ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->sisa_2 ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->sisa_4 ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->sisa_f ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->masa_kontrak ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->sisa ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->sisa_rr ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->no_lokasi ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->kategori_perubahan ?? '-' }}</td>
        
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->pdf ?? '-' }}</td>
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->update_pdf ?? '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->vendor_stokis_1 ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->vendor_stokis_2 ?? '-' }}</td>
        
        <td class="px-3 py-3 whitespace-normal break-words max-w-md">{{ $unit->alamat_saat_ini ?? '-' }}</td>
        <td class="px-3 py-3 whitespace-normal break-words max-w-md">{{ $unit->alamat_mitra ?? '-' }}</td>
        
        <td class="px-3 py-3">{{ $unit->no_cab_bimba_unit ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->len_perubahan_unit ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->kirim_email_lisensi ?? '-' }}</td>
        <td class="px-3 py-3">{{ $unit->jakarta ?? '-' }}</td>
        <td class="px-3 py-3 text-sm">{{ $unit->tanggal_update ? $unit->tanggal_update->format('d/m/Y') : '-' }}</td>
        
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->akun_facebook ?? '-' }}</td>
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->akun_instagram ?? '-' }}</td>
        <td class="px-3 py-3 whitespace-normal break-words">{{ $unit->akun_media_sosial_unit_bimba_aiueo ?? '-' }}</td>

        <!-- Aksi -->
        <td class="text-center whitespace-nowrap px-3 py-3">
            <a href="{{ route('unit-kemitraan.show', $unit) }}" class="text-blue-600 hover:text-blue-700 mx-1">👁</a>
            <a href="{{ route('unit-kemitraan.edit', $unit) }}" class="text-amber-600 hover:text-amber-700 mx-1">✏</a>
            <button onclick="if(confirm('Yakin hapus data ini?')) document.getElementById('delete-{{ $unit->id_record }}').submit()" 
                    class="text-red-600 hover:text-red-700 mx-1">🗑</button>
            <form id="delete-{{ $unit->id_record }}" action="{{ route('unit-kemitraan.destroy', $unit) }}" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="70" class="text-center py-20 text-gray-500">
            Belum ada data unit kemitraan.
        </td>
    </tr>
    @endforelse
</tbody>
            </table>
        </div>

        @if($unitKemitraans->count() > 0)
        <div class="mt-6 flex justify-between text-sm text-gray-600">
            <div>Menampilkan <strong>{{ $unitKemitraans->count() }}</strong> dari {{ $unitKemitraans->total() }} data</div>
            <div>{{ $unitKemitraans->links() }}</div>
        </div>
        @endif

    </div>
</body>
</html>