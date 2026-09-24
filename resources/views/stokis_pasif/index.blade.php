@extends('layouts.panel')

@section('title', 'Stokis Pasif')

@push('styles')
<style>
    /* ===== Tabel data ===== */
    .table-wrap { overflow: auto; max-height: 68vh; }
    .data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 1800px;
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
</style>
@endpush

@section('content')

    @php
        // Definisi kolom tabel: [judul, nama field, tipe]. Tipe: text, clip, email, sku, tgl
        $kolom = [
            ['No Cab', 'no_cab'],
            ['Nama Stokis Kemitraan', 'nama_stokis_db_kemitraan', 'clip'],
            ['Nama Stokis biMBA Shop', 'nama_stokis_db_bimbashop', 'clip'],
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
            ['Ops Stokist', 'ops_stokist'],
            ['Tanggal Pasif', 'tanggal_pasif', 'tgl'],
        ];
    @endphp

    {{-- ============ JUDUL ============ --}}
        <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Stokis Pasif</h1>
            <p class="mt-1 text-sm text-navy-950/55">Stokis mitra biMBA AIUEO yang sudah tidak aktif</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('stokis.index') }}"
               class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Stokis Aktif
            </a>
            <a href="{{ route('stokis-pasif.create') }}"
               class="inline-flex items-center gap-2 bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Stokis
            </a>
        </div>
    </div>

    {{-- ============ NOTIFIKASI ============ --}}
    @if (session('success'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

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
                <a href="{{ route('stokis-pasif.index', ['per_page' => $perPage]) }}"
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
                                    // Tanggal pasif ditampilkan d/m/Y
                                    if ($tipe === 'tgl' && filled($val)) {
                                        $val = \Illuminate\Support\Carbon::parse($val)->format('d/m/Y');
                                    }
                                    $tampil = filled($val) ? $val : '-';
                                @endphp

                                <td class="{{ $i === 0 ? 'sticky-l font-medium' : '' }} {{ in_array($tipe, ['clip', 'sku', 'email']) ? 'cell-clip' : '' }}"
                                    @if (in_array($tipe, ['clip', 'sku', 'email'])) title="{{ $tampil }}" @endif>
                                    @if ($tipe === 'email' && filled($val))
                                        <a href="mailto:{{ $val }}" class="text-navy-700 hover:text-rust-600 hover:underline">{{ $val }}</a>
                                    @else
                                        {{ $tampil }}
                                    @endif
                                </td>
                            @endforeach

                            {{-- Aksi --}}
                            <td class="ctr sticky-r">
                                <div class="inline-flex items-center gap-0.5">
                                    {{-- Aktifkan kembali --}}
                                    {{-- Edit --}}
                                    <a href="{{ route('stokis-pasif.edit', $item->id) }}" title="Edit" aria-label="Edit stokis"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17l-1 3Z"/><path d="m14.5 7.5 2 2"/></svg>
                                    </a>
                                    <form action="{{ route('stokis-pasif.aktifkan', $item->id) }}" method="POST"
                                          onsubmit="return confirm('Aktifkan kembali stokis ini? Data akan kembali ke daftar Stokis Aktif.')">
                                        @csrf
                                        <button type="submit" title="Aktifkan kembali" aria-label="Aktifkan kembali stokis"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-emerald-600 hover:bg-emerald-50 transition-colors">
                                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 3-6.7"/><path d="M3 4v5h5"/></svg>
                                        </button>
                                    </form>

                                    {{-- Hapus --}}
                                    <form action="{{ route('stokis-pasif.destroy', $item->id) }}" method="POST"
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
                                    Belum ada stokis pasif. Pindahkan dari halaman Stokis Aktif.
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