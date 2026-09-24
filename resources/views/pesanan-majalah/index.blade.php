@extends('layouts.panel')

@section('title', 'Pesanan Majalah')

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

        $kolom = ['No', 'Judul Pesanan', 'No PS', 'Bulan / Edisi', 'Tahun', 'Periode', 'Jumlah Unit', 'Total Pesanan'];

        $inp = 'w-full bg-white border border-navy-950/10 rounded-xl px-3.5 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
        $lbl = 'block text-sm text-navy-950/60 mb-1.5';
    @endphp

    {{-- ============ JUDUL ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Pesanan Majalah</h1>
            <p class="mt-1 text-sm text-navy-950/55">Daftar periode pesanan majalah. Klik judul untuk melihat detail data unit.</p>
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
            <p class="font-semibold mb-1">Terjadi kesalahan:</p>
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    {{-- ============ FILTER ============ --}}
    <div class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-6">
        <form method="GET" action="{{ route('pesanan-majalah.index') }}">
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
                <a href="{{ route('pesanan-majalah.index') }}"
                   class="text-sm font-medium text-navy-950/60 hover:text-rust-600 px-3 py-2.5">Reset</a>
            </div>
        </form>
    </div>

    {{-- ============ TABEL (level periode) ============ --}}
    <div class="mt-4 bg-white rounded-2xl shadow-card overflow-hidden">
        <div class="table-wrap">
            <table class="data-table">
                <thead>
                    <tr>
                        @foreach ($kolom as $i => $judul)
                            <th class="{{ in_array($i, [0, 4, 5, 6, 7]) ? 'ctr' : '' }}">{{ $judul }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($data as $item)
                        @php
                            $totalUnits   = $item->kabupaten->sum(fn ($kab) => $kab->units->count());
                            $totalPesanan = $item->kabupaten->sum(fn ($kab) => $kab->units->sum('jumlah_pesanan'));
                            $judulText    = ($item->judul ?? 'Tanpa Judul')
                                          . ($item->bulan ? ' — ' . $item->bulan : '')
                                          . ($item->tahun ? ' ' . $item->tahun : '');
                        @endphp
                        <tr>
                            <td class="ctr">{{ ($data->firstItem() ?? 1) + $loop->index }}</td>

                            <td class="cell-clip" title="{{ $judulText }}">
                                <a href="{{ route('pesanan-majalah.show', $item->id) }}"
                                   class="font-semibold text-navy-700 hover:text-rust-600 hover:underline">{{ $judulText }}</a>
                            </td>

                            {{-- No PS (edit langsung di tabel) --}}
                            <td>
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                           class="no-ps-input w-28 bg-white border border-navy-950/10 rounded-lg px-2.5 py-1.5 text-sm focus:outline-none focus:border-navy-700"
                                           data-url="{{ url('pesanan-majalah/' . $item->id . '/update-no-ps') }}"
                                           value="{{ $item->no_ps ?? '' }}"
                                           placeholder="No PS">
                                    <button type="button"
                                            class="btn-save-no-ps hidden bg-navy-800 hover:bg-navy-900 text-white text-xs font-semibold px-3 py-1.5 rounded-lg">
                                        Simpan
                                    </button>
                                    <span class="save-status hidden text-xs"></span>
                                </div>
                            </td>

                            <td>{{ $item->bulan ?? '-' }}</td>
                            <td class="ctr">{{ $item->tahun ?? '-' }}</td>
                            <td class="ctr">{{ $namaPeriode($item->periode) ?? '-' }}</td>
                            <td class="ctr font-medium">{{ number_format($totalUnits, 0, ',', '.') }}</td>
                            <td class="ctr font-semibold">{{ number_format($totalPesanan, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($kolom) }}" class="!text-center py-16 text-navy-950/50">
                                <p class="font-semibold text-navy-950/70">Belum ada data</p>
                                <p class="mt-1">Belum ada periode pesanan majalah.</p>
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
                    <h2 class="text-lg font-bold text-navy-950">Import Pesanan Majalah</h2>
                    <p class="text-sm text-navy-950/55 mt-0.5">Pilih bulan dan tahun tujuan import data.</p>
                </div>
                <button type="button" onclick="closeImportModal()" aria-label="Tutup"
                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg text-navy-950/40 hover:text-red-600 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
                </button>
            </div>

            <form id="formImportMajalah" action="{{ route('pesanan-majalah.import') }}" method="POST" enctype="multipart/form-data" class="p-6">
                @csrf

                <div>
                    <label for="periodeImport" class="{{ $lbl }}">Periode pesanan majalah</label>
                    <select id="periodeImport" name="periode" required class="{{ $inp }}">
                        <option value="">Pilih periode</option>
                        @php $tanggalMulai = now()->startOfYear(); @endphp
                        @for ($i = 0; $i <= 23; $i++)
                            @php $tgl = $tanggalMulai->copy()->addMonths($i); @endphp
                            <option value="{{ $tgl->format('Y-m') }}">{{ $tgl->translatedFormat('F Y') }}</option>
                        @endfor
                    </select>
                    <p class="mt-1.5 text-xs text-navy-950/45">Rentang 24 bulan, dimulai dari Januari tahun ini.</p>
                </div>

                <div class="mt-4">
                    <label for="fileImportMajalah" class="{{ $lbl }}">File Excel</label>
                    <input id="fileImportMajalah" type="file" name="file" accept=".xlsx,.xls,.csv" required
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
                    <button type="submit" id="btnImportMajalah"
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
    var form      = document.getElementById('formImportMajalah');
    var periode   = document.getElementById('periodeImport');
    var tombol    = document.getElementById('btnImportMajalah');
    var fileInput = document.getElementById('fileImportMajalah');

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
            alert('Silakan pilih periode pesanan majalah terlebih dahulu.');
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
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeImportModal();
    });

    // ---------- Edit No PS langsung di tabel ----------
    var token = '{{ csrf_token() }}';

    document.addEventListener('input', function (e) {
        var el = e.target.closest('.no-ps-input');
        if (!el) return;
        var td = el.closest('td');
        td.querySelector('.btn-save-no-ps').classList.remove('hidden');
        var st = td.querySelector('.save-status');
        st.classList.add('hidden');
        st.textContent = '';
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' || !e.target.closest('.no-ps-input')) return;
        e.preventDefault();
        e.target.closest('td').querySelector('.btn-save-no-ps').click();
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-save-no-ps');
        if (!btn) return;

        var td    = btn.closest('td');
        var input = td.querySelector('.no-ps-input');
        var st    = td.querySelector('.save-status');

        btn.disabled = true;
        btn.textContent = '...';

        fetch(input.dataset.url, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': token
            },
            body: JSON.stringify({ no_ps: input.value.trim() })
        })
        .then(function (res) {
            if (!res.ok) throw new Error('gagal');
            btn.classList.add('hidden');
            btn.disabled = false;
            btn.textContent = 'Simpan';
            st.className = 'save-status text-xs text-emerald-600';
            st.textContent = 'Tersimpan';
            setTimeout(function () { st.classList.add('hidden'); }, 2000);
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'Simpan';
            st.className = 'save-status text-xs text-red-600';
            st.textContent = 'Gagal';
        });
    });
});
</script>
@endpush