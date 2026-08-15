<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data DLC - biMBA AIUEO Logistik</title>
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

        <h2 class="text-3xl font-bold text-gray-800 mb-8">Tambah Data Pemesanan DLC</h2>

        @if($errors->any())
            <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-xl mb-6">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('import.dlc.store') }}" method="POST" class="bg-white rounded-3xl shadow p-8">
            @csrf

            <!-- Header Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
                
                <!-- Edisi (Dropdown) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Edisi *</label>
                    <select name="edisi" id="edisi" required
                            class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            onchange="isiOtomatis()">
                        <option value="">-- Pilih Edisi --</option>
                        @for($i = 150; $i <= 180; $i++)
                            <option value="M{{ $i }}" {{ old('edisi') == "M$i" ? 'selected' : '' }}>
                                M{{ $i }}
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- Periode (manual) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Periode *</label>
                    <input type="text" name="periode" id="periode" value="{{ old('periode') }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Contoh: 23-31 2026" required>
                </div>

                <!-- Judul (otomatis) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Judul</label>
                    <input type="text" name="judul" id="judul" value="{{ old('judul') }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Akan terisi otomatis" readonly>
                </div>

                <!-- Bulan (otomatis) -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Bulan</label>
                    <input type="text" name="bulan" id="bulan" value="{{ old('bulan') }}"
                           class="w-full border border-gray-300 rounded-xl px-4 py-2.5 bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Akan terisi otomatis" readonly>
                </div>
            </div>

            <!-- Daftar Unit -->
            <!-- Daftar Unit -->
<div class="mb-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">Daftar Unit & Qty</h3>

    <div class="space-y-3">
        <!-- Anggrek 1 -->
        <div class="flex items-center gap-4">
            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 font-medium text-gray-800">
                Anggrek 1
            </div>
            <input type="hidden" name="items[0][nama_unit]" value="Anggrek 1">
            <input type="number" name="items[0][qty]" min="0" value="{{ old('items.0.qty') }}"
                   class="w-32 border border-gray-300 rounded-xl px-4 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Qty">
        </div>

        <!-- Anggrek 2 -->
        <div class="flex items-center gap-4">
            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 font-medium text-gray-800">
                Anggrek 2
            </div>
            <input type="hidden" name="items[1][nama_unit]" value="Anggrek 2">
            <input type="number" name="items[1][qty]" min="0" value="{{ old('items.1.qty') }}"
                   class="w-32 border border-gray-300 rounded-xl px-4 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Qty">
        </div>

        <!-- Anggrek 3 -->
        <div class="flex items-center gap-4">
            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 font-medium text-gray-800">
                Anggrek 3
            </div>
            <input type="hidden" name="items[2][nama_unit]" value="Anggrek 3">
            <input type="number" name="items[2][qty]" min="0" value="{{ old('items.2.qty') }}"
                   class="w-32 border border-gray-300 rounded-xl px-4 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Qty">
        </div>

        <!-- Anggrek 5 -->
        <div class="flex items-center gap-4">
            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 font-medium text-gray-800">
                Anggrek 5
            </div>
            <input type="hidden" name="items[3][nama_unit]" value="Anggrek 5">
            <input type="number" name="items[3][qty]" min="0" value="{{ old('items.3.qty') }}"
                   class="w-32 border border-gray-300 rounded-xl px-4 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Qty">
        </div>

        <!-- Dhuafa -->
        <div class="flex items-center gap-4">
            <div class="flex-1 bg-gray-50 border border-gray-200 rounded-xl px-4 py-2.5 font-medium text-gray-800">
                Dhuafa
            </div>
            <input type="hidden" name="items[4][nama_unit]" value="Dhuafa">
            <input type="number" name="items[4][qty]" min="0" value="{{ old('items.4.qty') }}"
                   class="w-32 border border-gray-300 rounded-xl px-4 py-2.5 text-right focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Qty">
        </div>
    </div>
</div>

            <!-- Tombol -->
            <div class="flex gap-3 pt-4 border-t">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-medium transition">
                    Simpan Data DLC
                </button>
                <a href="{{ route('import.dlc.index') }}"
                   class="px-6 py-2.5 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
            </div>
        </form>

    </div>
</div>

<script>
    // Mapping Edisi → Bulan (berdasarkan M159 = Juli)
    const mappingBulan = {
        150: 'Oktober', 151: 'November', 152: 'Desember',
        153: 'Januari', 154: 'Februari', 155: 'Maret',
        156: 'April', 157: 'Mei', 158: 'Juni',
        159: 'Juli', 160: 'Agustus', 161: 'September',
        162: 'Oktober', 163: 'November', 164: 'Desember',
        165: 'Januari', 166: 'Februari', 167: 'Maret',
        168: 'April', 169: 'Mei', 170: 'Juni',
        171: 'Juli', 172: 'Agustus', 173: 'September',
        174: 'Oktober', 175: 'November', 176: 'Desember',
        177: 'Januari', 178: 'Februari', 179: 'Maret',
        180: 'April'
    };

    function isiOtomatis() {
        const edisiSelect = document.getElementById('edisi');
        const judulInput  = document.getElementById('judul');
        const bulanInput  = document.getElementById('bulan');

        const edisi = edisiSelect.value; // contoh: M160

        if (!edisi) {
            judulInput.value = '';
            bulanInput.value = '';
            return;
        }

        // Ambil angka dari M160 → 160
        const nomor = parseInt(edisi.replace('M', ''));
        const bulan = mappingBulan[nomor] || '';

        bulanInput.value = bulan;
        judulInput.value = bulan ? `Majalah ${edisi} (${bulan})` : `Majalah ${edisi}`;
    }

    // Jalankan saat halaman load (kalau ada old value)
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('edisi').value) {
            isiOtomatis();
        }
    });

    // Tambah baris unit
    let index = 1;
    function tambahBaris() {
        const container = document.getElementById('items-container');
        const div = document.createElement('div');
        div.className = 'flex gap-3 item-row';
        div.innerHTML = `
            <input type="text" name="items[${index}][nama_unit]" 
                   class="flex-1 border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Nama Unit">
            <input type="number" name="items[${index}][qty]" min="1"
                   class="w-32 border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500"
                   placeholder="Qty">
        `;
        container.appendChild(div);
        index++;
    }
</script>

</body>
</html>