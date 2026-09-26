@extends('layouts.panel')

@section('title', 'Database Stokis Mitra')

@push('styles')
<style>
    /* ===== Tabel data ===== */
    .table-wrap { overflow: auto; max-height: 68vh; }
    .data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 1700px;
        font-size: 0.8125rem;
    }
    .data-table th,
    .data-table td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: top;
        white-space: nowrap;
        border-bottom: 1px solid rgba(15, 27, 51, 0.06);
    }
    .data-table thead th {
        position: sticky;
        top: 0;
        z-index: 20;
        background: #F4F6FA;
        color: rgba(15, 27, 51, 0.6);
        font-weight: 600;
        font-size: 0.75rem;
        border-bottom: 1px solid rgba(15, 27, 51, 0.1);
    }
    .data-table tbody tr:hover td { background-color: #F7F9FC; }
    .data-table .ctr { text-align: center; }

    /* Teks panjang dipotong "...", isi lengkap muncul saat kursor diarahkan (atribut title) */
    .cell-clip { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }

    /* Kolom pertama dan Aksi tetap terlihat saat tabel digeser ke samping */
    .data-table .sticky-l { position: sticky; left: 0; z-index: 10; background: #fff; }
    .data-table thead .sticky-l { z-index: 30; background: #F4F6FA; }
    .data-table .sticky-r { position: sticky; right: 0; z-index: 10; background: #fff; }
    .data-table thead .sticky-r { z-index: 30; background: #F4F6FA; }
    .data-table tbody tr:hover .sticky-l,
    .data-table tbody tr:hover .sticky-r { background-color: #F7F9FC; }

    /* Sembunyikan teks "Showing ..." bawaan pagination Laravel */
    .pager nav p { display: none; }

    /* Badge Ops Stokist */
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }
    .status-badge.status-green { background: #DCFCE7; color: #15803D; }
    .status-badge.status-red   { background: #FEE2E2; color: #B91C1C; }
    .status-badge.status-gray  { background: #F1F3F7; color: rgba(15, 27, 51, 0.55); }
</style>
@endpush

@section('content')

    @php
        // Definisi kolom tabel: [judul, nama field, tipe]. Tipe: text, clip, email, sku, ops
        $kolom = [
            ['No Cab', 'no_cab'],
            ['Nama Stokis Kemitraan', 'nama_stokis_db_kemitraan', 'clip'],
            ['Nama Stokis biMBA Shop', 'nama_stokis_db_bimbashop', 'clip'],
            ['Ops Stokist', 'ops_stokist', 'ops'],
            ['Status', 'status'],
            ['No Induk Mitra', 'no_induk_mitra'],
            ['Nama Mitra', 'nama_mitra', 'clip'],
            ['Email', 'email', 'email'],
            ['No HP', 'no_hp'],
            ['Form Pembukaan Unit', 'related_form_pembukaan_unit_aktif', 'clip'],
            ['Kerjasama English', 'related_formulir_kerjasama_english', 'clip'],
            ['DB Kemitraan & Shop', 'db_kemitraan_db_bimbashop', 'clip'],
            ['Unit biMBA', 'related_unit_bimba_aiueo', 'clip'],
            ['Kerjasama MK/MM', 'related_formulir_kerjasama_mk_mm', 'clip'],
            ['Pengajuan Perubahan', 'related_pengajuan_perubahan', 'clip'],
            ['Item SKU', 'item_sku', 'sku'],
            
        ];

        // Warna badge Ops Stokist: Active = hijau, Closed & Vacuum = merah, lainnya = abu-abu
        $warnaOps = function ($val) {
            $key = strtolower(trim((string) $val));
            return match ($key) {
                'active' => 'status-green',
                'closed', 'vacuum' => 'status-red',
                default => 'status-gray',
            };
        };
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Database Stokis Mitra</h1>
            <p class="mt-1 text-sm text-navy-950/55">Data stokis mitra biMBA AIUEO</p>
        </div>

        <button type="button"
                onclick="document.getElementById('importForm').classList.toggle('hidden')"
                class="inline-flex items-center gap-2 bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
            Import Excel
        </button>
    </div>

    {{-- ============ NOTIFIKASI ============ --}}
    @if (session('success'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ============ FORM IMPORT ============ --}}
    <div id="importForm" class="hidden mt-5 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <h2 class="text-base font-semibold text-navy-950">Import data stokis dari Excel</h2>

        <form action="{{ route('stokis.import') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1 min-w-0">
                    <label for="file" class="block text-sm text-navy-950/60 mb-1.5">Pilih file</label>
                    <input id="file" type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-navy-950/60
                                  file:mr-4 file:py-2.5 file:px-5
                                  file:rounded-xl file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-navy-700/10 file:text-navy-700
                                  hover:file:bg-navy-700/20">
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit"
                            class="bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                        Upload dan import
                    </button>
                    <button type="button"
                            onclick="document.getElementById('importForm').classList.add('hidden')"
                            class="text-sm font-medium text-navy-950/60 hover:text-navy-950 px-4 py-2.5">
                        Batal
                    </button>
                </div>
            </div>
        </form>

        <p class="mt-3 text-xs text-navy-950/45">Format yang didukung: .xlsx, .xls, .csv. Ukuran maksimal 10 MB.</p>
    </div>

    {{-- ============ PENCARIAN + JUMLAH PER HALAMAN ============ --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-4">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="per_page" value="{{ $perPage }}">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-navy-950/35 pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari No Cab, stokis, mitra, atau email"
                       class="w-72 sm:w-96 bg-white border border-navy-950/10 rounded-xl pl-10 pr-4 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700">
            </div>
            <button type="submit"
                    class="bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                Cari
            </button>
            @if (request('search'))
                <a href="{{ route('stokis.index', ['per_page' => $perPage]) }}"
                   class="text-sm font-medium text-navy-950/60 hover:text-rust-600 px-3 py-2.5">
                    Reset
                </a>
            @endif
        </form>

        <form method="GET" class="flex items-center gap-2">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <label for="per_page" class="text-sm text-navy-950/55">Tampilkan</label>
            <select id="per_page" name="per_page" onchange="this.form.submit()"
                    class="bg-white border border-navy-950/10 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:border-navy-700">
                @foreach ([5, 10, 20, 50, 100] as $opsi)
                    <option value="{{ $opsi }}" {{ (int) $perPage === $opsi ? 'selected' : '' }}>{{ $opsi }}</option>
                @endforeach
            </select>
        </form>
    </div>
    {{-- ============ RINGKASAN OPS STOKIST ============ --}}
    <div class="mt-1 grid grid-cols-3 gap-3">
        <div class="bg-white rounded-xl shadow-card px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-lg font-bold text-navy-950">{{ number_format($totalActive, 0, ',', '.') }}</p>
            </div>
            <span class="status-badge status-green">Active</span>
        </div>
        <div class="bg-white rounded-xl shadow-card px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-lg font-bold text-navy-950">{{ number_format($totalClosed, 0, ',', '.') }}</p>
            </div>
            <span class="status-badge status-red">Closed</span>
        </div>
        <div class="bg-white rounded-xl shadow-card px-4 py-3 flex items-center justify-between">
            <div>
                <p class="text-lg font-bold text-navy-950">{{ number_format($totalVacuum, 0, ',', '.') }}</p>
            </div>
            <span class="status-badge status-red">Vacuum</span>
        </div>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach ($kolom as $i => $kol)
                            <th class="{{ $i === 0 ? 'sticky-l' : '' }}">{{ $kol[0] }}</th>
                        @endforeach
                        <th class="ctr sticky-r">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($stokis as $item)
                        <tr>
                            @foreach ($kolom as $i => $kol)
                                @php
                                    $tipe = $kol[2] ?? 'text';
                                    $val  = $item->{$kol[1]};

                                    // Item SKU bisa berupa array
                                    if ($tipe === 'sku' && is_array($val)) {
                                        $val = implode(', ', $val);
                                    }
                                    // Status ditampilkan dengan huruf awal kapital saja (Aktif / Pasif)
                                    if ($kol[1] === 'status' && filled($val)) {
                                        $val = ucfirst(strtolower($val));
                                    }
                                    $tampil = filled($val) ? $val : '-';
                                @endphp

                                <td class="{{ $i === 0 ? 'sticky-l font-medium' : '' }} {{ in_array($tipe, ['clip', 'sku', 'email']) ? 'cell-clip' : '' }}"
                                    @if (in_array($tipe, ['clip', 'sku', 'email'])) title="{{ $tampil }}" @endif>
                                    @if ($tipe === 'email' && filled($val))
                                        <a href="mailto:{{ $val }}" class="text-navy-700 hover:text-rust-600 hover:underline">{{ $val }}</a>
                                    @elseif ($tipe === 'ops' && filled($val))
                                        <span class="status-badge {{ $warnaOps($val) }}">{{ $val }}</span>
                                    @else
                                        {{ $tampil }}
                                    @endif
                                </td>
                            @endforeach
                           {{-- Aksi --}}
                            <td class="ctr sticky-r">
                                <div class="inline-flex items-center gap-0.5">
                                    <a href="{{ route('stokis.edit', $item->id) }}" title="Edit" aria-label="Edit stokis"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17l-1 3Z"/><path d="m14.5 7.5 2 2"/></svg>
                                    </a>

                                    {{-- Pindah ke Stokis Pasif --}}
                                    <form action="{{ route('stokis.pasifkan', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Pindahkan stokis ini ke Stokis Pasif?')">
                                        @csrf
                                        <button type="submit" title="Pindah ke Pasif" aria-label="Pindah ke Stokis Pasif"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-navy-700 hover:bg-navy-700/10 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16v3H4z"/><path d="M5 10v9h14v-9"/><path d="M10 14h4"/></svg>
                                        </button>
                                    </form>

                                    <form action="{{ route('stokis.destroy', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Hapus data stokis ini? Data yang dihapus tidak bisa dikembalikan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" aria-label="Hapus stokis"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-red-600 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h16"/><path d="M9 7V4h6v3"/><path d="M6 7l1 13h10l1-13"/><path d="M10 11v6M14 11v6"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($kolom) + 1 }}" class="!text-center py-16 text-navy-950/50">
                                @if (request('search'))
                                    Tidak ada data yang cocok dengan "{{ request('search') }}".
                                @else
                                    Belum ada data. Klik Import Excel untuk mengunggah data stokis.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ PAGINATION ============ --}}
    <div class="mt-5 flex flex-col lg:flex-row items-center justify-between gap-4">
        <p class="text-sm text-navy-950/60">
            Menampilkan
            <span class="font-semibold text-navy-950">{{ number_format($stokis->firstItem() ?? 0, 0, ',', '.') }}</span>
            sampai
            <span class="font-semibold text-navy-950">{{ number_format($stokis->lastItem() ?? 0, 0, ',', '.') }}</span>
            dari
            <span class="font-semibold text-navy-950">{{ number_format($stokis->total(), 0, ',', '.') }}</span>
            data
        </p>
        <div class="pager max-w-full overflow-x-auto">
            {{ $stokis->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    </div>

@endsection