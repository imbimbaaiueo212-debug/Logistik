<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data DLC - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
@include('partials.top-nav')

<div class="flex h-screen">
    <div class="flex-1 p-8 overflow-auto">

        <div class="max-w-6xl mx-auto">
            <div class="flex justify-between items-center mb-8">
                <h2 class="text-3xl font-bold text-gray-800">Edit Data DLC</h2>
                <a href="{{ route('import.dlc.index') }}" class="text-gray-600 hover:text-gray-800 font-medium">
                    ← Kembali
                </a>
            </div>

            @if($errors->any())
                <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-xl mb-6">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('import.dlc.update', $periode->id) }}" method="POST" id="form-edit-dlc">
                @csrf
                @method('PUT')

                {{-- ==================== HEADER ==================== --}}
                <div class="bg-white rounded-3xl shadow p-8 mb-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-5">Data Periode</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Edisi <span class="text-red-500">*</span></label>
                            <input type="text" name="edisi" value="{{ old('edisi', $periode->edisi) }}"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Judul</label>
                            <input type="text" name="judul" value="{{ old('judul', $periode->judul) }}"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                            <input type="text" name="periode" value="{{ old('periode', $periode->periode) }}"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No PS</label>
                            <input type="text" name="no_ps" value="{{ old('no_ps', $periode->no_ps) }}"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500"
                                   placeholder="Contoh: PS/DLC/2026/001">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                            <input type="text" name="bulan" value="{{ old('bulan', $periode->bulan) }}"
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500">
                                <option value="aktif" {{ old('status', $periode->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                <option value="nonaktif" {{ old('status', $periode->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ==================== DAFTAR UNIT ==================== --}}
                <div class="bg-white rounded-3xl shadow p-8 mb-8">
                    <div class="flex justify-between items-center mb-5">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Unit</h3>
                        <button type="button" id="btn-tambah-unit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-xl text-sm font-medium transition">
                            + Tambah Unit
                        </button>
                    </div>

                    <div id="container-units" class="space-y-4">
                        @foreach($periode->pesanan as $index => $item)
                            <div class="unit-row border border-gray-200 rounded-2xl p-5 relative">
                                <input type="hidden" name="units[{{ $index }}][id]" value="{{ $item->id }}">

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Unit <span class="text-red-500">*</span></label>
                                        <input type="text" name="units[{{ $index }}][nama_unit]" 
                                               value="{{ old("units.$index.nama_unit", $item->nama_unit) }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                                        <input type="number" name="units[{{ $index }}][qty]" min="0"
                                               value="{{ old("units.$index.qty", $item->qty) }}"
                                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
                                    </div>
                                </div>

                                <button type="button" class="btn-hapus-unit absolute top-3 right-3 text-red-500 hover:text-red-700 text-sm font-medium">
                                    Hapus
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Tombol Simpan --}}
                <div class="flex justify-end gap-3">
                    <a href="{{ route('import.dlc.index') }}"
                       class="px-6 py-2.5 border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 font-medium transition">
                        Batal
                    </a>
                    <button type="submit"
                            class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let unitIndex = {{ $periode->pesanan->count() }};

document.getElementById('btn-tambah-unit').addEventListener('click', function () {
    const container = document.getElementById('container-units');

    const html = `
    <div class="unit-row border border-gray-200 rounded-2xl p-5 relative">
        <input type="hidden" name="units[${unitIndex}][id]" value="">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Nama Unit <span class="text-red-500">*</span></label>
                <input type="text" name="units[${unitIndex}][nama_unit]" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Qty <span class="text-red-500">*</span></label>
                <input type="number" name="units[${unitIndex}][qty]" min="0" value="0" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm" required>
            </div>
        </div>

        <button type="button" class="btn-hapus-unit absolute top-3 right-3 text-red-500 hover:text-red-700 text-sm font-medium">
            Hapus
        </button>
    </div>`;

    container.insertAdjacentHTML('beforeend', html);
    unitIndex++;
});

// Hapus unit
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-hapus-unit')) {
        e.target.closest('.unit-row').remove();
    }
});
</script>
</body>
</html>