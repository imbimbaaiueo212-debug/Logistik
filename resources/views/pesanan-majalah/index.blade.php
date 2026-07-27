<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Majalah - biMBA AIUEO Logistik</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        table {
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px 6px;
            font-size: 0.8rem;
        }

        th {
            background-color: #f1f5f9;
            font-weight: 600;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .modal-open {
            overflow: hidden;
        }
    </style>
</head>

<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-screen-2xl mx-auto px-6 py-6">

    {{-- ========================================================= --}}
    {{-- HEADER --}}
    {{-- ========================================================= --}}
    <div class="flex flex-col lg:flex-row lg:justify-between lg:items-center gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <a href="{{ route('database-user.index') }}"
               class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                ← Kembali
            </a>
        </div>
        <h1 class="text-3xl font-bold text-gray-800">
            Pesanan Majalah
        </h1>
        <p class="text-gray-600 mt-1">
            Daftar periode pesanan majalah. Klik judul untuk melihat detail data unit.
        </p>
    </div>

    <div class="flex flex-wrap gap-3">
        <button type="button" onclick="openImportModal()"
            class="bg-green-600 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-green-700 transition flex items-center gap-2">
            📥 Import Excel
        </button>
    </div>
</div>

    {{-- ========================================================= --}}
    {{-- ALERT SUCCESS --}}
    {{-- ========================================================= --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-5 py-4 rounded-2xl">
            <div class="flex items-center gap-2">
                <span>✅</span>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- ALERT ERROR --}}
    {{-- ========================================================= --}}
    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
            <div class="flex items-center gap-2">
                <span>❌</span>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- VALIDATION ERROR --}}
    {{-- ========================================================= --}}
    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
            <div class="font-semibold mb-2">Terjadi kesalahan:</div>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ========================================================= --}}
    {{-- FILTER --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow p-6 mb-8">
        <form method="GET" action="{{ route('pesanan-majalah.index') }}"
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                <input type="text" name="judul" value="{{ request('judul') }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                    placeholder="Cari judul...">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Bulan / Edisi</label>
                <input type="text" name="bulan" value="{{ request('bulan') }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                    placeholder="Contoh: JULI M159">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                <input type="number" name="tahun" value="{{ request('tahun') }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                    placeholder="2026">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                <input type="text" name="periode" value="{{ request('periode') }}"
                    class="w-full border border-gray-300 rounded-xl px-4 py-2 focus:outline-none focus:border-blue-500"
                    placeholder="2026-07">
            </div>

            <div class="flex items-end gap-3 lg:col-span-4">
                <button type="submit"
                    class="bg-blue-600 text-white px-8 py-3 rounded-2xl font-semibold hover:bg-blue-700 transition">
                    🔍 Terapkan Filter
                </button>

                <a href="{{ route('pesanan-majalah.index') }}"
                    class="bg-gray-500 text-white px-6 py-3 rounded-2xl font-semibold hover:bg-gray-600 transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    {{-- ========================================================= --}}
    {{-- TABEL (HANYA LEVEL PERIODE) --}}
    {{-- ========================================================= --}}
    <div class="bg-white rounded-3xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-gray-100 border-b-2 border-gray-300">
                    <th class="px-4 py-4 text-center">No</th>
                    <th class="px-4 py-4">Judul Pesanan</th>
                    <th class="px-4 py-4">Bulan / Edisi</th>
                    <th class="px-4 py-4 text-center">Tahun</th>
                    <th class="px-4 py-4 text-center">Periode</th>
                    <th class="px-4 py-4 text-center">Jumlah Unit</th>
                    <th class="px-4 py-4 text-center">Total Pesanan</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @php
                    $no = $data->firstItem() ?? 1;
                @endphp

                @forelse($data as $item)
                    @php
                        // Hitung jumlah unit & total pesanan
                        $totalUnits = $item->kabupaten->sum(fn ($kab) => $kab->units->count());
                        $totalPesanan = $item->kabupaten->sum(fn ($kab) => $kab->units->sum('jumlah_pesanan'));
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-4 text-center">{{ $no++ }}</td>

                        {{-- Judul digabung & bisa diklik --}}
                        <td class="px-4 py-4">
                            <a href="{{ route('pesanan-majalah.show', $item->id) }}"
                               class="font-semibold text-blue-700 hover:text-blue-900 hover:underline">
                                {{ $item->judul ?? 'Tanpa Judul' }}
                                @if($item->bulan)
                                    — {{ $item->bulan }}
                                @endif
                                @if($item->tahun)
                                    {{ $item->tahun }}
                                @endif
                            </a>
                        </td>

                        <td class="px-4 py-4">{{ $item->bulan ?? '-' }}</td>
                        <td class="px-4 py-4 text-center">{{ $item->tahun ?? '-' }}</td>
                        <td class="px-4 py-4 text-center">{{ $item->periode ?? '-' }}</td>
                        <td class="px-4 py-4 text-center font-medium">{{ $totalUnits }}</td>
                        <td class="px-4 py-4 text-center font-semibold">{{ number_format($totalPesanan) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-20 text-gray-500">
                            <div class="text-5xl mb-4">📚</div>
                            <p class="text-lg font-semibold">Belum Ada Data</p>
                            <p class="text-sm mt-1">Belum ada periode pesanan majalah.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ========================================================= --}}
    {{-- PAGINATION --}}
    {{-- ========================================================= --}}
    @if($data->count() > 0)
        <div class="mt-6 flex flex-col md:flex-row md:justify-between md:items-center gap-4 text-sm text-gray-600">
            <div>
                Menampilkan <strong>{{ $data->total() }}</strong> periode
            </div>
            <div>
                {{ $data->links() }}
            </div>
        </div>
    @endif

</div>

{{-- ========================================================= --}}
{{-- MODAL IMPORT --}}
{{-- ========================================================= --}}
<div id="importModal" class="hidden fixed inset-0 z-50 bg-black/50 flex items-center justify-center px-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg" onclick="event.stopPropagation()">

        {{-- HEADER --}}
        <div class="flex justify-between items-center px-6 py-5 border-b">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Import Pesanan Majalah</h2>
                <p class="text-sm text-gray-500 mt-1">Pilih bulan dan tahun tujuan import data.</p>
            </div>
            <button type="button" onclick="closeImportModal()"
                class="text-gray-500 hover:text-red-600 text-2xl font-bold">&times;</button>
        </div>

        {{-- FORM --}}
        <form id="formImportMajalah"
            action="{{ route('pesanan-majalah.import') }}"
            method="POST"
            enctype="multipart/form-data"
            class="p-6">
            @csrf

            {{-- PERIODE --}}
            <div class="mb-5">
                <label for="periodeImport" class="block text-sm font-semibold text-gray-700 mb-2">
                    Periode Pesanan Majalah
                </label>

                <select id="periodeImport" name="periode" required
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">-- Pilih Periode --</option>

                    @php
                        $tanggalMulai = now()->startOfMonth();
                    @endphp

                    @for($i = 0; $i <= 12; $i++)
                        @php
                            $tanggal = $tanggalMulai->copy()->addMonths($i);
                            $value   = $tanggal->format('Y-m');
                            $label   = $tanggal->translatedFormat('F Y');
                        @endphp
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endfor
                </select>

                <p class="text-xs text-gray-500 mt-2">
                    Pilih bulan dan tahun untuk data pesanan majalah yang akan diimport.
                </p>
            </div>

            {{-- FILE --}}
            <div class="mb-5">
                <label for="fileImportMajalah" class="block text-sm font-semibold text-gray-700 mb-2">
                    File Excel
                </label>

                <input id="fileImportMajalah" type="file" name="file"
                    accept=".xlsx,.xls,.csv" required
                    class="w-full border border-gray-300 rounded-xl px-4 py-3 bg-white">

                <p class="text-xs text-gray-500 mt-2">
                    Format yang didukung: XLSX, XLS, atau CSV.
                </p>
            </div>

            {{-- BUTTON --}}
            <div class="flex justify-end gap-3">
                <button type="button" onclick="closeImportModal()"
                    class="bg-gray-500 text-white px-5 py-3 rounded-xl font-semibold hover:bg-gray-600 transition">
                    Batal
                </button>

                <button type="submit" id="btnImportMajalah"
                    class="bg-green-600 text-white px-5 py-3 rounded-xl font-semibold hover:bg-green-700 transition">
                    📥 Import Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal     = document.getElementById('importModal');
    const form      = document.getElementById('formImportMajalah');
    const periode   = document.getElementById('periodeImport');
    const button    = document.getElementById('btnImportMajalah');
    const fileInput = document.getElementById('fileImportMajalah');

    // Buka Modal
    window.openImportModal = function () {
        modal.classList.remove('hidden');
        document.body.classList.add('modal-open');
        periode.value = '';
        button.disabled = false;
        button.innerHTML = '📥 Import Data';
    };

    // Tutup Modal
    window.closeImportModal = function () {
        modal.classList.add('hidden');
        document.body.classList.remove('modal-open');
    };

    // Validasi saat submit
    form.addEventListener('submit', function (event) {
        if (!periode.value) {
            event.preventDefault();
            alert('Silakan pilih periode pesanan majalah terlebih dahulu.');
            periode.focus();
            return;
        }

        if (!fileInput.files.length) {
            event.preventDefault();
            alert('Silakan pilih file Excel terlebih dahulu.');
            return;
        }

        button.disabled = true;
        button.innerHTML = '⏳ Sedang Import...';
    });

    // Klik di luar modal
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            closeImportModal();
        }
    });

    // ESC untuk menutup
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeImportModal();
        }
    });
});
</script>

</body>
</html>