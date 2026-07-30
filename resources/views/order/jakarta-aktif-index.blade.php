<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jakarta Aktif - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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
    
    /* ==========================================
   NAVIGASI
========================================== */
.nav-dropdown {
    appearance: none;
    min-width: 220px;
    padding: 12px 42px 12px 18px;
    border: 2px solid #d1d5db;
    border-radius: 14px;
    background-color: white;
    color: #374151;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;

    /* Panah dropdown */
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='2' stroke='%23374151'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='m19.5 8.25-7.5 7.5-7.5-7.5'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-size: 18px;
    background-position: right 14px center;

    transition: all 0.2s ease;
}

.nav-dropdown:hover {
    border-color: #6366f1;
}

.nav-dropdown:focus {
    outline: none;
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}

#navButtons {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.nav-tab,
.nav-back,
.btn-rekap {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    min-height: 42px;
    padding: 10px 18px;

    border-radius: 10px;

    font-size: 14px;
    font-weight: 600;

    text-decoration: none;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}


/* KEMBALI */

.nav-back {
    background: #64748b;
    color: white;
}

.nav-back:hover {
    background: #475569;
    color: white;
    transform: translateY(-1px);
}


/* MODUL */

.nav-modul {
    background: #16a34a;
    color: white;
}

.nav-modul:hover {
    background: #15803d;
    color: white;
}


/* MAJALAH */

.nav-majalah {
    background: #2563eb;
    color: white;
}

.nav-majalah:hover {
    background: #1d4ed8;
    color: white;
}


/* SERTIFIKAT */

.nav-sertifikat {
    background: #dc2626;
    color: white;
}

.nav-sertifikat:hover {
    background: #b91c1c;
}


/* SEMUA PESANAN */

.nav-semua {
    background: #334155;
    color: white;
}

.nav-semua:hover {
    background: #1e293b;
    color: white;
}


/* ACTIVE TAB */

.nav-tab.active-tab {
    box-shadow:
        0 0 0 3px rgba(37, 99, 235, 0.18),
        0 4px 12px rgba(0, 0, 0, 0.12);

    transform: translateY(-1px);
}


/* ==========================================
   REKAP AKTUAL
========================================== */

.btn-rekap {
    background: #3b82f6;
    color: white;

    padding-left: 22px;
    padding-right: 22px;
}

.btn-rekap:hover {
    background: #2563eb;
    color: white;

    transform: translateY(-1px);

    box-shadow: 0 5px 15px rgba(37, 99, 235, 0.25);
}


/* ==========================================
   ACTION BAR
========================================== */

.action-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 10px;

    padding: 14px 18px;

    background: #EFF6FF;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);

    flex-wrap: wrap;
}


/* TITLE */

.action-title {
    display: flex;
    align-items: center;

    gap: 10px;
}

.action-icon {
    width: 36px;
    height: 36px;

    display: flex;
    align-items: center;
    justify-content: center;

    background: #e0e7ff;

    border-radius: 9px;

    font-size: 18px;
}

.action-heading {
    color: #1e293b;

    font-size: 14px;

    font-weight: 700;
}

.action-subtitle {
    color: #64748b;

    font-size: 12px;

    margin-top: 2px;
}


/* ==========================================
   ACTION BUTTONS
========================================== */

.action-buttons {
    display: flex;

    align-items: center;

    gap: 8px;

    flex-wrap: wrap;
}


.action-buttons form {
    margin: 0;
}


.btn-action {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 7px;

    min-height: 40px;

    padding: 9px 17px;

    border: none;

    border-radius: 9px;

    color: white;

    font-size: 13px;

    font-weight: 600;

    text-decoration: none;

    cursor: pointer;

    transition:
        transform 0.2s ease,
        box-shadow 0.2s ease;
}


/* EXPORT */

.btn-export {
    background: #16a34a;
}

.btn-export:hover {
    background: #15803d;

    color: white;

    transform: translateY(-1px);

    box-shadow: 0 5px 12px rgba(22, 163, 74, 0.2);
}


/* SYNC */

.btn-sync {
    background: #7c3aed;
}

.btn-sync:hover {
    background: #6d28d9;

    transform: translateY(-1px);

    box-shadow: 0 5px 12px rgba(124, 58, 237, 0.2);
}


/* ==========================================
   RESPONSIVE
========================================== */

@media (max-width: 768px) {

    .nav-tab,
    .nav-back,
    .btn-rekap {
        flex: 1 1 auto;
    }

    .action-bar {
        align-items: stretch;
    }

    .action-title {
        width: 100%;
    }

    .action-buttons {
        width: 100%;
    }

    .btn-action {
        flex: 1;
    }

}
    </style>
</head>
<body class="bg-gray-50">

    @include('partials.top-nav')

    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        {{-- FLASH MESSAGE --}}
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

                {{-- UNIT TIDAK PESAN MAJALAH (selalu tampil) --}}
                <!--@if(!empty($unitTidakPesan) && count($unitTidakPesan) > 0)
                <div class="mb-6 bg-amber-50 border border-amber-200 text-amber-900 px-5 py-4 rounded-2xl">
                    <div class="flex items-start gap-3">
                        <span class="text-xl">⚠️</span>
                        <div class="flex-1">
                            <p class="font-semibold mb-2">
                                Unit tidak pesan majalah (qty 0): 
                                <span class="bg-amber-200 text-amber-900 px-2 py-0.5 rounded-full text-sm">
                                    {{ count($unitTidakPesan) }} unit
                                </span>
                            </p>
                            <ul class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-1 text-sm">
                                @foreach($unitTidakPesan as $u)
                                    <li>
                                        • <strong>{{ $u['nama'] }}</strong>
                                        @if(!empty($u['no_cab']))
                                            <span class="text-amber-700">({{ $u['no_cab'] }})</span>
                                        @endif
                                        <span class="text-amber-600 text-xs">— {{ $u['wilayah'] }} / {{ $u['sumber'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
                @endif-->

                @if(session('unit_nama_mismatch') && count(session('unit_nama_mismatch')) > 0)
                    <div class="mb-6 bg-orange-50 border border-orange-300 text-orange-900 px-5 py-4 rounded-2xl">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">⚠️</span>
                            <div class="flex-1">
                                <p class="font-semibold mb-2">
                                    Nama unit beda (Excel Majalah vs Unit Kemitraan):
                                    <span class="bg-orange-200 px-2 py-0.5 rounded-full text-sm">
                                        {{ count(session('unit_nama_mismatch')) }} unit
                                    </span>
                                </p>
                                <p class="text-sm text-orange-700 mb-2">
                                    Nama yang dipakai = dari <strong>Unit Kemitraan</strong>. Periksa data Excel jika salah.
                                </p>
                                <ul class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                                    @foreach(session('unit_nama_mismatch') as $m)
                                        <li class="bg-white/60 rounded-lg px-3 py-2 border border-orange-200">
                                            <strong>No Cab {{ $m['no_cab'] }}</strong><br>
                                            Excel: <span class="text-red-600">{{ $m['nama_excel'] }}</span><br>
                                            Master: <span class="text-emerald-700">{{ $m['nama_master'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Jakarta Aktif</h1>
                <p class="text-gray-600">Kelola Data Order Jakarta Aktif</p>
            </div>
            
            <div class="mb-5">

    {{-- ==========================================
         NAVIGASI UTAMA
    =========================================== --}}
                <div class="flex items-center justify-between gap-4 flex-wrap mb-4">
                            <div class="action-bar">

                        <div class="action-buttons">

                    {{-- KIRI: NAVIGASI --}}
                    <div class="flex items-center gap-3 flex-wrap">

                        {{-- KEMBALI --}}
                        

                        {{-- DROPDOWN KATEGORI --}}
                       <form action="{{ route('order.jakarta-aktif.sync-jkt') }}"
                                method="POST"
                                onsubmit="return confirm('Yakin ingin melakukan Sync JKT + Casdana?')">

                                @csrf

                                <button type="submit"
                                        class="btn-action btn-sync">

                                    <span>🔄</span>
                                    <span>Sync JKT + Casdana</span>
                                </button>
                            </form>

                            <!--<form action="{{ route('order.jakarta-aktif.sync-pesanan-majalah') }}"
                                method="POST"
                                onsubmit="return confirm('Yakin Sync semua Pesanan Majalah (Kabupaten + Kotamadya + PUW1) ke Jakarta Aktif?')">
                                @csrf
                                <button type="submit" class="btn-action btn-sync" style="background:#be185d;">
                                    <span>🔄</span>
                                    <span>Sync Pesanan Majalah</span>
                                </button>
                            </form>-->

                    </div>


                    {{-- KANAN: REKAP --}}
                    <div>
                        <a href="{{ route('order.jakarta-printed') }}"
                        class="btn-rekap">

                            <span>📊</span>
                            <span>Rekap Aktual</span>

                        </a>
                    </div>


                            @php
                                $queryString = http_build_query(request()->all());
                            @endphp


                            {{-- EXPORT --}}
                            <a href="{{ route('order.jakarta-aktif.export') }}{{ $queryString ? '?' . $queryString : '' }}"
                            class="btn-action btn-export">

                                <span>📥</span>
                                <span>Export Excel</span>

                            </a>
                            <a href="{{ route('order.jakarta-aktif.menu') }}"
                        class="nav-back">
                            <span>Kembali</span>
                        </a>


                            {{-- SYNC --}}
                            
                        </div>
                    </div>
                </div>  
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
<div class="bg-slate-100 rounded-3xl shadow p-6 mb-6">
    <form method="GET" id="filterForm" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-9 gap-4">

        {{-- ID Pesan (tetap text) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ID Pesan</label>
            <input type="text"
                   name="id_pesan"
                   value="{{ request('id_pesan') }}"
                   class="w-full border border-gray-300 bg-white rounded-xl px-4 py-2.5"
                   placeholder="Cari ID Pesan...">
        </div>

        {{-- Kirim (tetap text) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kirim</label>
            <input type="text"
                   name="kirim"
                   value="{{ request('kirim') }}"
                   class="w-full border border-gray-300 bg-white rounded-xl px-4 py-2.5"
                   placeholder="Nama Penerima...">
        </div>

        {{-- Pesanan (Select2) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Pesanan</label>
            <select name="pesanan" id="filterPesanan" class="w-full">
                <option value="">Semua Pesanan</option>
                @foreach($listPesanan ?? [] as $p)
                    <option value="{{ $p }}" {{ request('pesanan') == $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Nama Unit (Select2) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
            <select name="nama_unit" id="filterNamaUnit" class="w-full">
                <option value="">Semua Unit</option>
                @foreach($listNamaUnit ?? [] as $u)
                    <option value="{{ $u }}" {{ request('nama_unit') == $u ? 'selected' : '' }}>
                        {{ $u }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status Bayar (Select2) - BARU --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status Bayar</label>
            <select name="status_pembayaran" id="filterStatusBayar" class="w-full">
                <option value="">Semua Status</option>
                @foreach($listStatusBayar ?? [] as $s)
                    <option value="{{ $s }}" {{ request('status_pembayaran') == $s ? 'selected' : '' }}>
                        {{ $s }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Dari Tanggal --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
            <input type="date"
                   name="start_date"
                   value="{{ request('start_date') }}"
                   class="w-full border border-gray-300 bg-white rounded-xl px-4 py-2.5">
        </div>

        {{-- Sampai Tanggal --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
            <input type="date"
                   name="end_date"
                   value="{{ request('end_date') }}"
                   class="w-full border border-gray-300 bg-white rounded-xl px-4 py-2.5">
        </div>

        {{-- Per Page --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tampilkan</label>
            <select name="per_page"
                    onchange="this.form.submit()"
                    class="w-full border border-gray-300 bg-white rounded-xl px-4 py-2.5">
                <option value="50"  {{ request('per_page') == 50  ? 'selected' : '' }}>50</option>
                <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                <option value="200" {{ request('per_page') == 200 ? 'selected' : '' }}>200</option>
                <option value="300" {{ request('per_page') == 300 ? 'selected' : '' }}>300</option>
                <option value="500" {{ request('per_page') == 500 ? 'selected' : '' }}>500</option>
            </select>
        </div>

        {{-- Group --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Group</label>
            <select name="grup" id="filterGrup" class="w-full border border-gray-300 bg-white rounded-xl px-4 py-2.5">
                <option value="">Semua Group</option>
                <option value="A" {{ request('grup') == 'A' ? 'selected' : '' }}>Group A</option>
                <option value="B" {{ request('grup') == 'B' ? 'selected' : '' }}>Group B</option>
                <option value="C" {{ request('grup') == 'C' ? 'selected' : '' }}>Group C</option>
                <option value="D" {{ request('grup') == 'D' ? 'selected' : '' }}>Group D</option>
                <option value="E" {{ request('grup') == 'E' ? 'selected' : '' }}>Group E</option>
                <option value="F" {{ request('grup') == 'F' ? 'selected' : '' }}>Group F</option>
            </select>
        </div>

        {{-- Tombol --}}
        <div class="flex items-end gap-3 pt-6 lg:col-span-1 xl:col-span-1">
            <button type="submit"
                    class="bg-blue-600 text-white px-6 py-2.5 rounded-xl hover:bg-blue-700 flex-1">
                🔍 Filter
            </button>
            <a href="{{ route('order.jakarta-aktif') }}"
               class="text-gray-500 hover:text-red-600 px-4 py-2.5 whitespace-nowrap">
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
                       
                        <th class="text-left px-4 py-3">ID Pesan</th>
                        <th class="text-left px-4 py-3">Nama Unit</th>
                        <th class="text-left px-4 py-3">Cabang</th>
                        <th class="text-left px-4 py-3">Alamat Kirim</th>
                        <th class="text-left px-4 py-3">Kab/Kota</th>
                        <th class="text-left px-4 py-3">Kategori Pesanan</th>
                        <th class="text-left px-4 py-3">QTy</th>
                        <th class="text-left px-4 py-3">Order Date</th>
                        <th class="text-left px-4 py-3">Payment Date</th>
                        <th class="text-left px-4 py-3">Estimasi Print PL | PS</th>
                        <th class="text-left px-4 py-3">Estimasi Persiapan</th>
                        <th class="text-left px-4 py-3">Jasa Kurir</th>
                        <th class="text-left px-4 py-3">Service Kurir</th>
                        <th class="text-left px-4 py-3">Distribusi</th>
                        <th class="text-right px-4 py-3">Ship Total</th>
                        <th class="text-right px-4 py-3">Berat (gr)</th>
                        <th class="text-right px-4 py-3">Order Total</th>
                        <th class="text-right px-4 py-3">Payment Channel</th>
                        <th class="text-left px-4 py-3">Status Bayar</th>
                        <th class="text-left px-4 py-3">Status biMBAShop</th>
                        <th class="text-center px-4 py-3">Tanggal Proses</th>
                        <!--<th class="text-center px-4 py-3">Group</th>-->
                        <th class="text-center px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($data as $item)
                        @php
                            $isProcessed = $item->is_processed ?? false;

                            $paymentDate = $item->payment_date
                                ? \Carbon\Carbon::parse($item->payment_date)
                                : null;

                            $estimasiPrint = $item->estimasi_print_pl
                                ? \Carbon\Carbon::parse($item->estimasi_print_pl)
                                : null;

                            $estimasiPersiapan = $item->estimasi_persiapan
                                ? \Carbon\Carbon::parse($item->estimasi_persiapan)
                                : null;

                            $jamPrint = $estimasiPrint
                                ? now()->diffInHours($estimasiPrint, false)
                                : 999;

                            $jamPersiapan = $estimasiPersiapan
                                ? now()->diffInHours($estimasiPersiapan, false)
                                : 999;
                        @endphp
                    <tr class="{{ $isProcessed ? 'processed-row' : '' }} hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $item->id_pesan ?? '-' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span>{{ $item->nama_unit ?? '-' }}</span>

                                @php
                                    $noCabItem = trim($item->billing_last_name ?? '');
                                    $isMismatch = (
                                        ($item->catatan ?? '') === 'NAMA_MISMATCH'
                                        || (!empty($noCabItem) && isset($mismatchNoCab[$noCabItem]))
                                    );
                                @endphp

                                @if($isMismatch)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-800 border border-orange-200"
                                        title="Nama unit beda dengan Unit Kemitraan">
                                        ⚠️ Mismatch
                                    </span>
                                @endif

                                @if(!empty($item->grup))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800">
                                        Group {{ $item->grup }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $item->billing_last_name ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->kirim ?? '-' }}</td>
                        <td class="px-4 py-3">{{ $item->kab_kota_provinsi ?? '-' }}</td>
                        
                        <td class="px-4 py-3">
                            @if($item->items->count())

                                @php
                                    $grouped = $item->items
                                        ->groupBy(function ($detail) {
                                            return $detail->product?->kategori
                                                ?? $detail->nama_produk;
                                        });
                                @endphp

                                @foreach($grouped as $nama => $details)

                                    {{ $nama }}

                                    @if(!$loop->last)
                                        <span class="text-gray-400"> | </span>
                                    @endif

                                @endforeach

                            @else

                                {{ $item->pesanan }}

                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">{{ $item->item_qty ?? 0 }}</td>
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
                        <!--<td class="px-4 py-3">
                            @if(!empty($item->grup))
                                <span class="badge-green">Group {{ $item->grup }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>-->

                        <!-- Di dalam tabel, bagian Aksi -->
                    <td class="text-center px-4 py-3">
                        @if(!$isProcessed)
                            <a href="{{ route('order.jakarta-aktif.edit', $item->id) }}" 
                            class="text-blue-600 hover:text-blue-700 text-lg inline-block hover:scale-110 transition">
                                ✏️
                            </a>
                        @else
                            <span class="inline-flex items-center gap-1 text-emerald-600 font-medium text-sm">
                                ✅ Diproses
                            </span>
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
        <div id="bulkModal" class="hidden fixed inset-0 bg-black bg-opacity-60 flex items-center justify-center z-50">
            <div class="bg-white rounded-3xl shadow-2xl
            w-[98vw]
            h-[95vh]
            mx-2
            flex flex-col">
                <div class="p-6 border-b flex justify-between items-center">
                    <div>
                        <h3 class="text-2xl font-semibold">Edit & Proses Data Terpilih</h3>
                        <p class="text-gray-600" id="modalCount">0 data dipilih</p>
                    </div>
                    <button onclick="hideBulkModal()" class="text-3xl text-gray-500 hover:text-gray-700">✕</button>
                </div>
               <div class="flex-1 overflow-auto p-6">
                    <table class="w-full text-sm border border-gray-200 min-w-[1600px]" id="modalTable">
                        <thead class="bg-gray-50 sticky top-0">
                            <tr class="divide-x divide-gray-200">
                                <th class="px-4 py-3 text-left w-24">Status</th>
                                <th class="px-4 py-3 text-left w-32">Invoice</th>
                                <th class="px-4 py-3 text-left min-w-[240px]">To Customer</th>
                                <th class="px-4 py-3 text-left w-40">Kategori Pesanan</th>
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
                    <button onclick="hideBulkModal()" class="px-6 py-3 text-gray-600 hover:bg-gray-100 rounded-2xl">Batal</button>
                    <button onclick="executeBulkAction()" class="bg-indigo-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-indigo-700">💾 Simpan & Kunci Semua Data</button>
                </div>
            </div>
        </div>
    </div>

 
 <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    const currentRoute = "{{ Route::currentRouteName() }}";

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

        setTimeout(checkProcessButtonVisibility, 1000);

        $(document).ready(function() {
    // ========== FILTER SELECT2 ==========
    $('#filterPesanan, #filterNamaUnit, #filterStatusBayar').select2({
        placeholder: 'Pilih / cari...',
        allowClear: true,
        width: '100%',
        // supaya dropdown tidak terpotong di dalam card
        dropdownParent: $('#filterForm').parent()
    });

    // Style select2 biar selaras dengan input lain
    $('.select2-container .select2-selection--single').css({
        'height': '42px',
        'border-radius': '12px',
        'border-color': '#d1d5db',
        'padding-top': '6px'
    });

    checkFilterStatus();
    checkProcessButtonVisibility();

    $('input[name="start_date"], input[name="end_date"]').on('change', function() {
        checkFilterStatus();
        setTimeout(checkProcessButtonVisibility, 700);
    });

    setTimeout(checkProcessButtonVisibility, 1000);
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
            url: '{{ route("order.jakarta-aktif.filtered-ids") }}',
            method: 'GET',
            data: {

            route: currentRoute,

            start_date: startDate,
            end_date: endDate,

            id_pesan: $('input[name="id_pesan"]').val() || '',
            kirim: $('input[name="kirim"]').val() || '',
            nama_unit: $('input[name="nama_unit"]').val() || '',
            pesanan: $('input[name="pesanan"]').val() || '',
            status_pembayaran: $('#filterStatusBayar').val() || ''   // ← tambahkan
            
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

    // =====================================================
    // DETEKSI MAJALAH MANUAL
    // =====================================================
    const statusBayar = (item.status_pembayaran || '').toUpperCase().trim();
    const pesanan     = (item.pesanan || '').toString();
    const isManualMajalah = statusBayar === 'MANUAL' && (
        /M\d{2,4}/i.test(pesanan) ||
        pesanan.toLowerCase().includes('majalah')
    );

    let distribusiHtml, jasaKurirHtml, serviceKurirHtml, catatanHtml;

    if (isLocked) {
        distribusiHtml = `<span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold bg-emerald-100 text-emerald-700 rounded-2xl">${currentDistribusi}</span>`;
        jasaKurirHtml = `<span class="text-sm text-gray-500 font-medium">— Terkunci —</span>`;
        serviceKurirHtml = `<span class="text-sm text-gray-500 font-medium">— Terkunci —</span>`;
        catatanHtml = `<span class="text-xs text-gray-500 italic">Sudah diproses ${item.processed_at ? 'pada ' + item.processed_at : ''}</span>`;
    } else {
        distribusiHtml = `<span class="inline-flex items-center px-4 py-2.5 text-sm font-semibold bg-blue-100 text-blue-700 rounded-2xl">${currentDistribusi}</span>`;

        // =====================================================
        // DEFAULT OTOMATIS UNTUK MAJALAH MANUAL
        // =====================================================
        if (isManualMajalah) {
            jasaKurirHtml = `
                <select class="jasa-kurir-select w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                    <option value="">Pilih atau ketik jasa kurir...</option>
                    <option value="JNE">JNE</option>
                    <option value="TIKI">TIKI</option>
                    <option value="Lion Parcel" selected>Lion Parcel</option>
                </select>`;

            serviceKurirHtml = `
                <select class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                    <option value="">Pilih Service</option>
                    <option value="REGPACK" selected>REGPACK</option>
                    <option value="BOSPACK">BOSPACK</option>
                    <option value="JAGOPACK">JAGOPACK</option>
                    <option value="BIGPACK">BIGPACK</option>
                </select>`;
        } else {
            // Default biasa (bukan majalah manual)
            jasaKurirHtml = `
                <select class="jasa-kurir-select w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm">
                    <option value="">Pilih atau ketik jasa kurir...</option>
                    <option value="JNE">JNE</option>
                    <option value="TIKI">TIKI</option>
                    <option value="Lion Parcel">Lion Parcel</option>
                </select>`;

            serviceKurirHtml = `<input type="text" class="service-kurir w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" placeholder="REG / YES / CTC / dll">`;
        }

        catatanHtml = `<input type="text" class="catatan w-full border border-gray-300 rounded-2xl px-3 py-2.5 text-sm" placeholder="Catatan tambahan...">`;
    }

    html += `
        <tr data-id="${item.id}" data-distribusi="${currentDistribusi}"
            class="${isLocked ? 'processed-row' : 'hover:bg-gray-50'}">
            <td class="px-4 py-3">${item.status_pembayaran || '-'}</td>
            <td class="px-4 py-3 font-medium">${item.invoice}</td>
            <td class="px-4 py-3">${item.to_customer}</td>
            <td class="px-4 py-3 font-medium text-gray-700">
                ${item.pesanan ? item.pesanan : '-'}
            </td>
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

        // ==================== JASA KURIR SELECT2 ====================
        $('.jasa-kurir-select').select2({
            placeholder: "Pilih atau ketik jasa kurir...",
            allowClear: true,
            tags: true,
            tokenSeparators: [','],
            width: '100%',
            dropdownParent: $('#bulkModal'),
            createTag: function(params) {
                let term = $.trim(params.term);
                if (term === '') return null;
                return { id: term, text: term, new: true };
            }
        }).on('change', function() {
            const row = $(this).closest('tr');
            const jasa = $(this).val();
            let serviceField = row.find('.service-kurir');

            // === LOGIC SERVICE SEPERTI AWAL ===
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
                        <option value="REGPACK">REGPACK</option>
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

        // ==================== SETUP "DIAMBIL SENDIRI" ====================
        $('#modalTableBody tr:not(.processed-row)').each(function() {
            const row = $(this);
            if (row.data('distribusi') === 'Diambil') {
                const jasaSelect = row.find('.jasa-kurir-select');
                
                if (jasaSelect.find('option[value="Diambil Sendiri"]').length === 0) {
                    jasaSelect.append('<option value="Diambil Sendiri">Diambil Sendiri</option>');
                }

                jasaSelect.val('Diambil Sendiri')
                          .trigger('change')
                          .prop('disabled', true);

                row.find('.service-kurir').prop('disabled', true).val('');
            }
        });

        // ==================== VALIDASI ====================
        function checkSaveButtonState() {
            let isValid = true;

            $('#modalTableBody tr:not(.processed-row)').each(function () {
                const row = $(this);
                let jasaKurir = row.find('.jasa-kurir-select').val() || '';
                let serviceValue = '';

                const serviceField = row.find('.service-kurir');
                if (serviceField.length) {
                    serviceValue = serviceField.is('select') ? (serviceField.val() || '') : $.trim(serviceField.val());
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
        $('.jasa-kurir-select').select2('destroy');
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
        action: '{{ route("order.jakarta-aktif.bulk-action") }}',
        method: 'POST'
    });

    $('<input>', {
        type: 'hidden',
        name: '_token',
        value: '{{ csrf_token() }}'
    }).appendTo(form);

    $('<input>', {
        type: 'hidden',
        name: 'action',
        value: 'processed'
    }).appendTo(form);

    $('<input>', {
        type: 'hidden',
        name: 'per_item',
        value: JSON.stringify(updates)
    }).appendTo(form);

    // ============================
    // TAMBAHKAN INI
    // ============================
    $('<input>', {
        type: 'hidden',
        name: 'redirect',
        value: currentRoute
    }).appendTo(form);

    form.appendTo('body').submit();
}

function setActiveTab(el) {
    const tabType = el.getAttribute('data-tab');

    // Reset semua tombol
    document.querySelectorAll('.nav-tab').forEach(tab => {
        tab.classList.remove('active-tab', 'ring-2', 'ring-offset-2', 'ring-blue-400');
    });

    // Tambah style active
    el.classList.add('active-tab', 'ring-2', 'ring-offset-2', 'ring-blue-400');

    // Logika hide/show
    const modulBtn     = document.querySelector('[data-tab="modul"]');
    const majalahBtn   = document.querySelector('[data-tab="majalah"]');
    const sertifikatBtn = document.querySelector('[data-tab="sertifikat"]');
    const semuaBtn     = document.querySelector('[data-tab="semua"]');

    if (tabType === 'semua') {
        // Tampilkan semua
        modulBtn.style.display = 'inline-flex';
        majalahBtn.style.display = 'inline-flex';
        sertifikatBtn.style.display = 'inline-flex';
    } else {
        // Sembunyikan yang lain
        if (tabType === 'modul') {
            majalahBtn.style.display = 'none';
            sertifikatBtn.style.display = 'none';
        } else if (tabType === 'majalah') {
            modulBtn.style.display = 'none';
            sertifikatBtn.style.display = 'none';
        } else if (tabType === 'sertifikat') {
            modulBtn.style.display = 'none';
            majalahBtn.style.display = 'none';
        }
    }
}

// Inisialisasi saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const currentPath = window.location.pathname;
    
    // Otomatis aktifkan tombol sesuai halaman saat ini
    if (currentPath.includes('/modul')) {
        const btn = document.querySelector('[data-tab="modul"]');
        if (btn) setActiveTab(btn);
    } else if (currentPath.includes('/majalah')) {
        const btn = document.querySelector('[data-tab="majalah"]');
        if (btn) setActiveTab(btn);
    } else if (currentPath.includes('/sertifikat')) {
        const btn = document.querySelector('[data-tab="sertifikat"]');
        if (btn) setActiveTab(btn);
    } else {
        // Default ke Semua Pesanan
        const btn = document.querySelector('[data-tab="semua"]');
        if (btn) setActiveTab(btn);
    }
});
</script>
</body>
</html>