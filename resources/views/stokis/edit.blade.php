<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Stokis Mitra - biMBA AIUEO</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>

<body class="bg-gray-50">

@include('partials.top-nav')

<div class="max-w-4xl mx-auto px-6 py-6">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Edit Stokis Mitra</h1>
        <p class="text-gray-500">{{ $stokis->nama_stokis_db_kemitraan ?? $stokis->no_cab }}</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl">
            <p class="font-semibold mb-1">Periksa kembali isian berikut:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('stokis.update', $stokis->id) }}" method="POST"
          class="bg-white rounded-3xl shadow p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm text-gray-600 mb-1">No Cab</label>
                <input type="text" name="no_cab" value="{{ old('no_cab', $stokis->no_cab) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama Stokis (DB Kemitraan)</label>
                <input type="text" name="nama_stokis_db_kemitraan" value="{{ old('nama_stokis_db_kemitraan', $stokis->nama_stokis_db_kemitraan) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama Stokis (biMBA Shop)</label>
                <input type="text" name="nama_stokis_db_bimbashop" value="{{ old('nama_stokis_db_bimbashop', $stokis->nama_stokis_db_bimbashop) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">No Induk Mitra</label>
                <input type="text" name="no_induk_mitra" value="{{ old('no_induk_mitra', $stokis->no_induk_mitra) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Nama Mitra</label>
                <input type="text" name="nama_mitra" value="{{ old('nama_mitra', $stokis->nama_mitra) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $stokis->email) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">No HP</label>
                <input type="text" name="no_hp" value="{{ old('no_hp', $stokis->no_hp) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">DB Kemitraan & biMBA Shop</label>
                <input type="text" name="db_kemitraan_db_bimbashop" value="{{ old('db_kemitraan_db_bimbashop', $stokis->db_kemitraan_db_bimbashop) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Ops Stokist</label>
                <input type="text" name="ops_stokist" value="{{ old('ops_stokist', $stokis->ops_stokist) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm text-gray-600 mb-1">Item SKU</label>
                <input type="text" name="item_sku"
                       value="{{ old('item_sku', is_array($stokis->item_sku) ? implode(', ', $stokis->item_sku) : $stokis->item_sku) }}"
                       class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Form Pembukaan Unit Aktif</label>
            <textarea name="related_form_pembukaan_unit_aktif" rows="2"
                      class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">{{ old('related_form_pembukaan_unit_aktif', $stokis->related_form_pembukaan_unit_aktif) }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Formulir Kerjasama English</label>
            <textarea name="related_formulir_kerjasama_english" rows="2"
                      class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">{{ old('related_formulir_kerjasama_english', $stokis->related_formulir_kerjasama_english) }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Unit biMBA-AIUEO</label>
            <textarea name="related_unit_bimba_aiueo" rows="2"
                      class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">{{ old('related_unit_bimba_aiueo', $stokis->related_unit_bimba_aiueo) }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Formulir Kerjasama MK/MM</label>
            <textarea name="related_formulir_kerjasama_mk_mm" rows="2"
                      class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">{{ old('related_formulir_kerjasama_mk_mm', $stokis->related_formulir_kerjasama_mk_mm) }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-gray-600 mb-1">Pengajuan Perubahan</label>
            <textarea name="related_pengajuan_perubahan" rows="2"
                      class="w-full border border-gray-300 rounded-2xl px-4 py-2.5 focus:outline-none focus:border-blue-500">{{ old('related_pengajuan_perubahan', $stokis->related_pengajuan_perubahan) }}</textarea>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-2xl font-semibold">
                💾 Simpan Perubahan
            </button>
            <a href="{{ route('stokis.index') }}"
               class="text-gray-500 hover:text-gray-700 px-4 py-3">
                Batal
            </a>
        </div>
    </form>

</div>
</body>
</html>