@extends('layouts.panel')

@section('title', 'Database Unit Kemitraan')

@push('styles')
<style>
    /* ===== Tabel data ===== */
    .table-wrap { overflow: auto; max-height: 68vh; }
    .data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: max-content;
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
    .data-table .num { text-align: right; font-variant-numeric: tabular-nums; }
    .data-table .ctr { text-align: center; }

    /* Teks panjang: dipotong "..." (isi lengkap tampil lewat atribut title) */
    .cell-clip { max-width: 220px; overflow: hidden; text-overflow: ellipsis; }
    /* Alamat, detail, catatan: boleh turun baris */
    .cell-wrap { white-space: normal !important; min-width: 240px; max-width: 320px; }

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
        $inp = 'w-full bg-white border border-navy-950/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
        $lbl = 'block text-xs font-medium text-navy-950/60 mb-1.5';

        // Format tanggal aman: objek tanggal dipakai langsung, teks biasa ditampilkan apa adanya
        $fmt = fn ($v, $f) => $v instanceof \DateTimeInterface ? $v->format($f) : (filled($v) ? $v : '-');

        // Definisi kolom tabel: [judul, nama field (atau daftar field cadangan), tipe]
        // Tipe: text, clip, wrap, center, num, money, date, datetime, badge
        $kolom = [
            ['ID Record', 'id_record', 'center'],
            ['No Cab', 'no_cab'],
            ['biMBA AIUEO Unit', 'bimba_aiueo_unit', 'clip'],
            ['Jenis Unit', 'status'],
            ['Status Pengelolaan', 'status_pengelolaan', 'badge'],
            ['Mitra Pengelolaan', 'mitra_pengelolaan'],
            ['Status Operasional', 'ops'],
            ['No Telp Unit', 'no_telp_unit'],
            ['Email Unit', 'email_unit', 'clip'],
            ['Alamat Unit', 'alamat_unit', 'wrap'],
            ['RT', 'rt', 'center'],
            ['RW', 'rw', 'center'],
            ['Provinsi', 'provinsi'],
            ['Kab/Kota', 'kab_kota'],
            ['Kecamatan', 'kecamatan'],
            ['Kel/Desa', 'kel_desa'],
            ['Kode Pos', 'kode_pos'],
            ['No Induk Mitra', 'no_induk_mitra'],
            ['Nama Mitra', 'nama_mitra', 'clip'],
            ['Email Mitra', ['email', 'email_mitra'], 'clip'],
            ['No HP Mitra', ['no_hp', 'no_hp_mitra']],
            ['Bank', 'bank'],
            ['No Rekening', 'no_rekening'],
            ['Atas Nama', 'atas_nama', 'clip'],
            ['No Akta', 'no_akta'],
            ['Tgl Akta', 'tgl_akta', 'date'],
            ['Nilai Lisensi', 'nilai_lisensi', 'money'],
            ['% Mitra', 'persen_mitra', 'num'],
            ['% YPAI', 'persen_ypai', 'num'],
            ['Awal', 'awal', 'date'],
            ['Akhir', 'akhir', 'date'],
            ['Perpanjang', 'perpanjang', 'date'],
            ['Tutup', 'tutup', 'date'],
            ['JMP', 'jmp'],
            ['LPM', 'lpm'],
            ['Pengembalian', 'pengembalian'],
            ['Tanggal VA BCA', 'tanggal', 'date'],
            ['VA Mandiri Royalti', 'va_mandiri_royalti'],
            ['VA Mandiri Lisensi', 'va_mandiri_lisensi'],
            ['Marketing', 'marketing'],
            ['Koorwil/KPK/Sos', 'koorwil_kpk_sos'],
            ['Detail', 'detail', 'wrap'],
            ['Note', 'note', 'wrap'],
            ['Updated By', 'updated_by'],
            ['Last Updated', 'last_updated', 'datetime'],
            ['Sisa 3', 'sisa_3'],
            ['Sisa 1', 'sisa_1'],
            ['Sisa 2', 'sisa_2'],
            ['Sisa 4', 'sisa_4'],
            ['Sisa F', 'sisa_f'],
            ['Masa Kontrak', 'masa_kontrak'],
            ['Sisa', 'sisa'],
            ['Sisa RR', 'sisa_rr'],
            ['No Lokasi', 'no_lokasi'],
            ['Kategori Perubahan', 'kategori_perubahan'],
            ['PDF', 'pdf', 'clip'],
            ['Update PDF', 'update_pdf', 'clip'],
            ['Vendor Stokis 1', 'vendor_stokis_1'],
            ['Vendor Stokis 2', 'vendor_stokis_2'],
            ['Alamat Saat Ini', 'alamat_saat_ini', 'wrap'],
            ['Alamat Mitra', 'alamat_mitra', 'wrap'],
            ['No Cab BiMBA Unit', 'no_cab_bimba_unit'],
            ['LEN Perubahan Unit', 'len_perubahan_unit'],
            ['Kirim Email Lisensi', 'kirim_email_lisensi'],
            ['Jakarta', 'jakarta'],
            ['Tanggal Update', 'tanggal_update', 'date'],
            ['Akun Facebook', 'akun_facebook', 'clip'],
            ['Akun Instagram', 'akun_instagram', 'clip'],
            ['Akun Media Sosial', 'akun_media_sosial_unit_bimba_aiueo', 'clip'],
        ];

        // Kelas CSS per tipe kolom
        $kelasTipe = [
            'center' => 'ctr', 'badge' => 'ctr',
            'num' => 'num', 'money' => 'num',
            'clip' => 'cell-clip', 'wrap' => 'cell-wrap',
        ];
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Database Unit Kemitraan</h1>
            <p class="mt-1 text-sm text-navy-950/55">Data dari tabel unit_kemitraan</p>
        </div>

        <div class="flex flex-wrap gap-2">
            <button type="button"
                    onclick="document.getElementById('importForm').classList.toggle('hidden')"
                    class="inline-flex items-center gap-2 bg-white hover:bg-navy-950/[0.03] border border-navy-950/10 transition-colors text-navy-950 text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                Import dari Excel
            </button>
            <a href="{{ route('unit-kemitraan.create') }}"
               class="inline-flex items-center gap-2 bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Tambah Unit Baru
            </a>
        </div>
    </div>

    {{-- ============ NOTIFIKASI ============ --}}
    @if (session('success'))
        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            <span class="font-semibold">Berhasil.</span> {{ session('success') }}
        </div>
    @endif

    @if (session('warning'))
        <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
            <span class="font-semibold">Perhatian.</span> {{ session('warning') }}

            @if (session('import_errors'))
                <div class="mt-3">
                    <p class="font-semibold mb-1">Detail error:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach (session('import_errors') as $err)
                            <li>
                                No Cab: <strong>{{ $err['no_cab'] }}</strong>
                                (ID: {{ $err['id_record'] }}): {{ $err['reason'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endif

    @if (session('error'))
        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <span class="font-semibold">Gagal.</span> {{ session('error') }}
        </div>
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
        <h2 class="text-base font-semibold text-navy-950">Import data unit kemitraan</h2>

        <form action="{{ route('unit-kemitraan.import.store') }}" method="POST" enctype="multipart/form-data" class="mt-4">
            @csrf
            <div class="flex flex-col md:flex-row md:items-end gap-4">
                <div class="flex-1 min-w-0">
                    <label for="import_file" class="{{ $lbl }}">Pilih file Excel atau CSV</label>
                    <input id="import_file" type="file" name="import_file" accept=".xlsx,.xls,.csv" required
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
                        Import sekarang
                    </button>
                    <button type="button"
                            onclick="document.getElementById('importForm').classList.add('hidden')"
                            class="text-sm font-medium text-navy-950/60 hover:text-navy-950 px-4 py-2.5">
                        Batal
                    </button>
                </div>
            </div>
        </form>

        <p class="mt-3 text-xs text-navy-950/45">Format yang didukung: .xlsx, .xls, .csv.</p>
    </div>

    {{-- ============ FILTER ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div>
                <label for="f_no_cab" class="{{ $lbl }}">No Cab</label>
                <input id="f_no_cab" type="text" name="no_cab" value="{{ request('no_cab') }}" placeholder="Cari No Cab" class="{{ $inp }}">
            </div>

            <div>
                <label for="f_nama_mitra" class="{{ $lbl }}">Nama Mitra</label>
                <input id="f_nama_mitra" type="text" name="nama_mitra" value="{{ request('nama_mitra') }}" placeholder="Nama mitra" class="{{ $inp }}">
            </div>

            <div>
                <label for="f_status_pengelolaan" class="{{ $lbl }}">Status Pengelolaan</label>
                <select id="f_status_pengelolaan" name="status_pengelolaan" class="{{ $inp }}">
                    <option value="">Semua status pengelolaan</option>
                    @foreach (['Unit Aktif' => 'Unit Aktif', 'Unit Pasif' => 'Unit Pasif', 'all' => 'Tampilkan semua'] as $nilai => $teks)
                        <option value="{{ $nilai }}" {{ request('status_pengelolaan') == $nilai ? 'selected' : '' }}>{{ $teks }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f_mitra_pengelolaan" class="{{ $lbl }}">Mitra Pengelolaan</label>
                <select id="f_mitra_pengelolaan" name="mitra_pengelolaan" class="{{ $inp }}">
                    <option value="">Semua mitra pengelolaan</option>
                    @foreach (['YPAI', 'PUW1 | ops1'] as $nilai)
                        <option value="{{ $nilai }}" {{ request('mitra_pengelolaan') == $nilai ? 'selected' : '' }}>{{ $nilai }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f_status" class="{{ $lbl }}">Status</label>
                <select id="f_status" name="status" class="{{ $inp }}">
                    <option value="">Semua status</option>
                    @foreach (['MM', 'MM 1', 'Aktif 1', 'MK 1', 'MK', 'MK Rinda', 'MKU', 'MKU 1', 'E-biMBA Aktif'] as $nilai)
                        <option value="{{ $nilai }}" {{ request('status') == $nilai ? 'selected' : '' }}>{{ $nilai }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="f_provinsi" class="{{ $lbl }}">Provinsi</label>
                <input id="f_provinsi" type="text" name="provinsi" value="{{ request('provinsi') }}" placeholder="Provinsi" class="{{ $inp }}">
            </div>

            <div class="sm:col-span-2">
                <label for="f_search" class="{{ $lbl }}">Pencarian umum</label>
                <input id="f_search" type="text" name="search" value="{{ request('search') }}" placeholder="Cari No Cab, nama unit, atau alamat" class="{{ $inp }}">
            </div>

            <div class="sm:col-span-2 lg:col-span-4 flex items-center gap-2">
                <button type="submit"
                        class="bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                    Terapkan filter
                </button>
                <a href="{{ route('unit-kemitraan.index') }}"
                   class="text-sm font-medium text-navy-950/60 hover:text-rust-600 px-4 py-2.5">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach ($kolom as $i => $kol)
                            <th class="{{ $i === 0 ? 'sticky-l' : '' }} {{ $kelasTipe[$kol[2] ?? 'text'] ?? '' }}">{{ $kol[0] }}</th>
                        @endforeach
                        <th class="ctr sticky-r">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($unitKemitraans as $unit)
                        <tr>
                            @foreach ($kolom as $i => $kol)
                                @php
                                    $tipe = $kol[2] ?? 'text';

                                    // Ambil field pertama yang tidak null (untuk kolom dengan field cadangan)
                                    $val = null;
                                    foreach ((array) $kol[1] as $field) {
                                        if (! is_null($unit->{$field})) { $val = $unit->{$field}; break; }
                                    }

                                    $tampil = match ($tipe) {
                                        'date'     => $fmt($val, 'd/m/Y'),
                                        'datetime' => $fmt($val, 'd/m/Y H:i'),
                                        'money'    => filled($val) ? number_format((float) $val, 2, ',', '.') : '-',
                                        default    => filled($val) ? $val : '-',
                                    };
                                @endphp

                                <td class="{{ $i === 0 ? 'sticky-l font-medium' : '' }} {{ $kelasTipe[$tipe] ?? '' }}"
                                    @if ($tipe === 'clip') title="{{ $tampil }}" @endif>
                                    @if ($tipe === 'badge')
                                        @if ($val === 'Unit Aktif')
                                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-xs font-semibold">Unit Aktif</span>
                                        @elseif ($val === 'Unit Pasif')
                                            <span class="inline-block px-2.5 py-0.5 rounded-full bg-red-100 text-red-700 text-xs font-semibold">Unit Pasif</span>
                                        @else
                                            <span class="text-navy-950/35">-</span>
                                        @endif
                                    @else
                                        {{ $tampil }}
                                    @endif
                                </td>
                            @endforeach

                            {{-- Aksi --}}
                            <td class="ctr sticky-r">
                                <div class="inline-flex items-center gap-0.5">
                                    <a href="{{ route('unit-kemitraan.show', $unit) }}" title="Lihat" aria-label="Lihat unit"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-navy-700 hover:bg-navy-700/10 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z"/><circle cx="12" cy="12" r="2.6"/></svg>
                                    </a>
                                    <a href="{{ route('unit-kemitraan.edit', $unit) }}" title="Edit" aria-label="Edit unit"
                                       class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-navy-950/40 hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h4L19 9a2.1 2.1 0 0 0-3-3L5 17l-1 3Z"/><path d="m14.5 7.5 2 2"/></svg>
                                    </a>
                                    <form action="{{ route('unit-kemitraan.destroy', $unit) }}" method="POST"
                                          onsubmit="return confirm('Hapus unit ini? Data yang dihapus tidak bisa dikembalikan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" aria-label="Hapus unit"
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
                                Belum ada data unit kemitraan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ PAGINATION ============ --}}
    @if ($unitKemitraans->count() > 0)
        <div class="mt-5 flex flex-col lg:flex-row items-center justify-between gap-4">
            <p class="text-sm text-navy-950/60">
                Menampilkan
                <span class="font-semibold text-navy-950">{{ number_format($unitKemitraans->count(), 0, ',', '.') }}</span>
                dari
                <span class="font-semibold text-navy-950">{{ number_format($unitKemitraans->total(), 0, ',', '.') }}</span>
                data
            </p>
            <div class="pager max-w-full overflow-x-auto">
                {{ $unitKemitraans->onEachSide(1)->links('pagination::tailwind') }}
            </div>
        </div>
    @endif

@endsection