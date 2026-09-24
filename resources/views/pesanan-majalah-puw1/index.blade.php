@extends('layouts.panel')

@section('title', 'Pesanan Majalah PUW1')

@push('styles')
<style>
    /* ===== Tabel data ===== */
    .table-wrap { overflow: auto; max-height: 68vh; }
    .data-table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        min-width: 900px;
        font-size: 0.8125rem;
    }
    .data-table th,
    .data-table td {
        padding: 10px 12px;
        text-align: left;
        vertical-align: middle;
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
    .cell-clip { max-width: 300px; overflow: hidden; text-overflow: ellipsis; }

    /* Sembunyikan teks "Showing ..." bawaan pagination Laravel */
    .pager nav p { display: none; }
</style>
@endpush

@section('content')

    @php
        $namaBulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];

        // "2026-03" -> "Maret"; null bila format tidak cocok
        $namaPeriode = function ($p) use ($namaBulan) {
            return preg_match('/^\d{4}-(\d{2})$/', (string) $p, $m) ? ($namaBulan[(int) $m[1]] ?? null) : null;
        };

        // Filter: [name, label, placeholder, daftar opsi]
        $filters = [
            ['judul', 'Judul', 'Semua judul', $listJudul],
            ['bulan', 'Bulan / Edisi', 'Semua bulan', $listBulan],
            ['tahun', 'Tahun', 'Semua tahun', $listTahun],
            ['periode', 'Periode', 'Semua periode', $listPeriode],
        ];

        $kolom = ['No', 'Judul Pesanan', 'No. PS', 'Bulan / Edisi', 'Tahun', 'Periode', 'Jumlah Unit', 'Total Pesanan'];
        $kolomTengah = [0, 2, 4, 5, 6, 7];

        $inp = 'w-full bg-white border border-navy-950/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
        $lbl = 'block text-sm text-navy-950/60 mb-1.5';
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Pesanan Majalah PUW1</h1>
            <p class="mt-1 text-sm text-navy-950/55">Daftar periode pesanan majalah Sahabat biMBA PUW I.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('ops2.index') }}"
               class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                Kembali
            </a>
            <button type="button" onclick="openImportModal()"
                    class="inline-flex items-center gap-2 bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-5 py-2.5 rounded-xl">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 16v3a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1v-3"/></svg>
                Import Excel
            </button>
        </div>
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

    {{-- ============ FILTER ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <form method="GET" action="{{ route('pesanan-majalah-puw1.index') }}">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($filters as [$nama, $label, $ph, $opsi])
                    <div>
                        <label for="f_{{ $nama }}" class="{{ $lbl }}">{{ $label }}</label>
                        <select id="f_{{ $nama }}" name="{{ $nama }}" class="{{ $inp }}">
                            <option value="">{{ $ph }}</option>
                            @foreach ($opsi as $o)
                                <option value="{{ $o }}" {{ request($nama) == $o ? 'selected' : '' }}>
                                    {{ $nama === 'periode' ? ($namaPeriode($o) ?? $o) : $o }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            </div>

            <div class="mt-5 flex items-center gap-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 bg-navy-800 hover:bg-navy-900 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                    Terapkan filter
                </button>
                <a href="{{ route('pesanan-majalah-puw1.index') }}"
                   class="text-sm font-medium text-navy-950/60 hover:text-rust-600 px-3 py-2.5">Reset</a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL ============ --}}
    <div class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach ($kolom as $i => $judul)
                            <th class="{{ in_array($i, $kolomTengah) ? 'ctr' : '' }}">{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                        @php
                            $judulText = ($item->judul ?? 'Tanpa Judul')
                                       . ($item->bulan ? ' — ' . $item->bulan : '')
                                       . ($item->tahun ? ' ' . $item->tahun : '');
                        @endphp
                        <tr>
                            <td class="ctr">{{ ($data->firstItem() ?? 1) + $loop->index }}</td>

                            <td class="cell-clip" title="{{ $judulText }}">
                                <a href="{{ route('pesanan-majalah-puw1.show', $item->id) }}"
                                   class="font-semibold text-navy-700 hover:text-rust-600 hover:underline">{{ $judulText }}</a>
                            </td>

                            {{-- No. PS (klik untuk mengubah, tersimpan otomatis) --}}
                            <td class="ctr">
                                <div class="no-ps-wrapper inline-flex items-center justify-center"
                                     data-url="{{ url('pesanan-majalah-puw1/' . $item->id . '/update-no-ps') }}">
                                    <span class="no-ps-text cursor-pointer hover:bg-navy-700/10 px-2 py-1 rounded transition-colors"
                                          title="Klik untuk mengubah">{{ $item->no_ps ?: '-' }}</span>
                                    <input type="text"
                                           class="no-ps-input hidden w-28 bg-white border border-navy-700 rounded-lg px-2 py-1 text-sm text-center focus:outline-none"
                                           value="{{ $item->no_ps }}" data-awal="{{ $item->no_ps }}">
                                </div>
                            </td>

                            <td>{{ $item->bulan ?? '-' }}</td>
                            <td class="ctr">{{ $item->tahun ?? '-' }}</td>
                            <td class="ctr">{{ $namaPeriode($item->periode) ?? '-' }}</td>
                            <td class="ctr font-medium">{{ number_format($item->units_count ?? 0, 0, ',', '.') }}</td>
                            <td class="ctr font-semibold text-emerald-700">{{ number_format(round((float) ($item->units_sum_jumlah_pesanan ?? 0)), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($kolom) }}" class="!text-center py-16 text-navy-950/50">
                                <p class="font-semibold text-navy-950/70">Belum ada data</p>
                                <p class="mt-1">Silakan import file Excel PUW1.</p>
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
            <span class="font-semibold text-navy-950">{{ number_format($data->firstItem() ?? 0, 0, ',', '.') }}</span>
            sampai
            <span class="font-semibold text-navy-950">{{ number_format($data->lastItem() ?? 0, 0, ',', '.') }}</span>
            dari
            <span class="font-semibold text-navy-950">{{ number_format($data->total(), 0, ',', '.') }}</span>
            periode
        </p>
        <div class="pager max-w-full overflow-x-auto">
            {{ $data->withQueryString()->onEachSide(1)->links('pagination::tailwind') }}
        </div>
    </div>

    {{-- ============ MODAL IMPORT ============ --}}
    <div id="importModal" class="hidden fixed inset-0 z-50 bg-navy-950/50 items-center justify-center px-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
            <div class="flex items-start justify-between px-6 py-5 border-b border-navy-950/10">
                <div>
                    <h2 class="text-lg font-bold text-navy-950">Import Pesanan Majalah PUW1</h2>
                    <p class="text-sm text-navy-950/55 mt-0.5">Pilih periode dan file Excel PUW1.</p>
                </div>
                <button type="button" onclick="closeImportModal()" aria-label="Tutup"
                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-navy-950/40 hover:text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="formImportPuw1" action="{{ route('pesanan-majalah-puw1.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf

                <div>
                    <label for="periodeImport" class="{{ $lbl }}">Periode pesanan majalah</label>
                    <select id="periodeImport" name="periode" required class="{{ $inp }}">
                        <option value="">Pilih periode</option>
                        @foreach ($periodeImport as $periode)
                            <option value="{{ $periode['value'] }}">{{ $periode['label'] }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-4">
                    <label for="fileImportPuw1" class="{{ $lbl }}">File Excel PUW1</label>
                    <input id="fileImportPuw1" type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="block w-full text-sm text-navy-950/60
                                  file:mr-4 file:py-2.5 file:px-5
                                  file:rounded-xl file:border-0
                                  file:text-sm file:font-semibold
                                  file:bg-navy-700/10 file:text-navy-700
                                  hover:file:bg-navy-700/20">
                    <p class="mt-1.5 text-xs text-navy-950/45">Format yang didukung: XLSX, XLS, atau CSV.</p>
                </div>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" onclick="closeImportModal()"
                            class="text-sm font-medium text-navy-950/60 hover:text-navy-950 px-4 py-2.5">Batal</button>
                    <button type="submit" id="btnImportPuw1"
                            class="bg-rust-500 hover:bg-rust-600 disabled:opacity-60 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                        Import data
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---------- Modal import ----------
    var modal     = document.getElementById('importModal');
    var form      = document.getElementById('formImportPuw1');
    var periode   = document.getElementById('periodeImport');
    var tombol    = document.getElementById('btnImportPuw1');
    var fileInput = document.getElementById('fileImportPuw1');

    window.openImportModal = function () {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.classList.add('overflow-hidden');
        periode.value = '';
        tombol.disabled = false;
        tombol.textContent = 'Import data';
    };
    window.closeImportModal = function () {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.classList.remove('overflow-hidden');
    };

    form.addEventListener('submit', function (e) {
        if (!periode.value) {
            e.preventDefault();
            alert('Silakan pilih periode terlebih dahulu.');
            periode.focus();
            return;
        }
        if (!fileInput.files.length) {
            e.preventDefault();
            alert('Silakan pilih file Excel terlebih dahulu.');
            return;
        }
        tombol.disabled = true;
        tombol.textContent = 'Sedang import...';
    });

    modal.addEventListener('click', function (e) { if (e.target === modal) closeImportModal(); });

    // ---------- Edit No. PS langsung di tabel ----------
    var token = '{{ csrf_token() }}';

    // Klik teks -> tampilkan input
    document.addEventListener('click', function (e) {
        var teks = e.target.closest('.no-ps-text');
        if (!teks) return;
        var input = teks.closest('.no-ps-wrapper').querySelector('.no-ps-input');
        teks.classList.add('hidden');
        input.classList.remove('hidden');
        input.focus();
        input.select();
    });

    // Enter = selesai (menyimpan lewat blur), Esc di modal = tutup
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.closest('.no-ps-input')) {
            e.preventDefault();
            e.target.blur();
        }
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeImportModal();
    });

    // Keluar dari input -> simpan bila ada perubahan
    document.addEventListener('focusout', function (e) {
        var input = e.target.closest('.no-ps-input');
        if (!input) return;

        var wrap  = input.closest('.no-ps-wrapper');
        var teks  = wrap.querySelector('.no-ps-text');
        var nilai = input.value.trim();
        var awal  = (input.dataset.awal || '').trim();

        input.classList.add('hidden');
        teks.classList.remove('hidden');
        if (nilai === awal) return;

        teks.textContent = nilai || '-';

        fetch(wrap.dataset.url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ no_ps: nilai })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) throw new Error(data.message || 'Gagal menyimpan No. PS');
            input.dataset.awal = nilai;
            teks.classList.add('bg-emerald-100');
            setTimeout(function () { teks.classList.remove('bg-emerald-100'); }, 800);
        })
        .catch(function (err) {
            // kembalikan ke nilai sebelumnya
            input.value = awal;
            teks.textContent = awal || '-';
            alert(err.message || 'Terjadi kesalahan saat menyimpan No. PS');
        });
    });
});
</script>
@endpush