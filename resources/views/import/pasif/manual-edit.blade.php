<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pasif Manual - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    @include('partials.top-nav')

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <div class="flex h-screen overflow-hidden">
        <div class="flex-1 overflow-auto">
            <div class="p-8 max-w-7xl mx-auto">

                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-gray-800">Edit Pasif Manual</h2>
                    <p class="text-gray-500 mt-1">Ubah data periode & unit pasif manual</p>
                </div>

                @if(session('error'))
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-2xl">
                        {{ session('error') }}
                    </div>
                @endif

                <form action="{{ route('import.pasif.manual.update', $periode->id) }}" method="POST" id="formPasifManual">
                    @csrf
                    @method('PUT')

                    {{-- Header Periode --}}
                    <div class="bg-white rounded-3xl shadow p-8 mb-8 border border-gray-100">
                        <h3 class="text-xl font-semibold mb-6 text-rose-700">Informasi Periode</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Edisi <span class="text-red-500">*</span></label>
                                <input type="text" name="edisi" value="{{ old('edisi', $periode->edisi) }}" required
                                       class="w-full rounded-2xl border-gray-300 focus:border-rose-500 focus:ring focus:ring-rose-200">
                                @error('edisi') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Judul</label>
                                <input type="text" name="judul" value="{{ old('judul', $periode->judul) }}"
                                       class="w-full rounded-2xl border-gray-300 focus:border-rose-500 focus:ring focus:ring-rose-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                                <input type="text" name="periode" value="{{ old('periode', $periode->periode) }}"
                                       class="w-full rounded-2xl border-gray-300 focus:border-rose-500 focus:ring focus:ring-rose-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                                <input type="text" name="bulan" value="{{ old('bulan', $periode->bulan) }}"
                                       class="w-full rounded-2xl border-gray-300 focus:border-rose-500 focus:ring focus:ring-rose-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                                <input type="text" name="tahun" value="{{ old('tahun', $periode->tahun) }}"
                                       class="w-full rounded-2xl border-gray-300 focus:border-rose-500 focus:ring focus:ring-rose-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No PS</label>
                                <input type="text" name="no_ps" value="{{ old('no_ps', $periode->no_ps) }}"
                                       class="w-full rounded-2xl border-gray-300 focus:border-rose-500 focus:ring focus:ring-rose-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                                <select name="status" class="w-full rounded-2xl border-gray-300 focus:border-rose-500 focus:ring focus:ring-rose-200">
                                    <option value="aktif" {{ old('status', $periode->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                                    <option value="nonaktif" {{ old('status', $periode->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Unit --}}
                    <div class="bg-white rounded-3xl shadow p-8 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-rose-700">Daftar Unit (Transaksi)</h3>
                            <button type="button" onclick="tambahBaris()"
                                    class="px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-2xl text-sm font-medium transition">
                                + Tambah Unit
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-sm" id="tabelUnit">
                                <thead>
                                    <tr class="bg-gray-50 text-left">
                                        <th class="px-3 py-3 rounded-l-xl whitespace-nowrap">No</th>
                                        <th class="px-3 py-3 whitespace-nowrap">Id Pesan</th>
                                        <th class="px-3 py-3 whitespace-nowrap">Tgl Pesan</th>                                     
                                        <th class="px-3 py-3 whitespace-nowrap">Nama Unit <span class="text-red-500">*</span></th>
                                        <th class="px-3 py-3 whitespace-nowrap">Label</th>
                                        <th class="px-3 py-3 whitespace-nowrap">Jumlah <span class="text-red-500">*</span></th>
                                        <th class="px-3 py-3 whitespace-nowrap">Ekspedisi</th>
                                        <th class="px-3 py-3 whitespace-nowrap">Service</th>
                                        <th class="px-3 py-3 rounded-r-xl">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="bodyUnit">
                                    @foreach($periode->transaksis as $i => $item)
                                    <tr class="border-b">
                                        <td class="px-3 py-3 text-center">{{ $i + 1 }}</td>
                                        <td class="px-3 py-3">
                                            <input type="text" name="items[{{ $i }}][id_pesan]" value="{{ old("items.$i.id_pesan", $item->id_pesan) }}"
                                                   class="w-28 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="date" name="items[{{ $i }}][tgl_pesan]"
                                                   value="{{ old("items.$i.tgl_pesan", $item->tgl_pesan ? \Carbon\Carbon::parse($item->tgl_pesan)->format('Y-m-d') : '') }}"
                                                   class="w-36 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="text" name="items[{{ $i }}][nama_unit]" value="{{ old("items.$i.nama_unit", $item->nama_unit) }}" required
                                                   class="w-48 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="text" name="items[{{ $i }}][label]" value="{{ old("items.$i.label", $item->label) }}"
                                                   class="w-24 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="number" name="items[{{ $i }}][jumlah]" min="1" value="{{ old("items.$i.jumlah", $item->jumlah) }}" required
                                                   class="w-20 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                                        </td>


                                        <td class="px-3 py-3">
                                            <input type="text" name="items[{{ $i }}][ekspedisi]" value="{{ old("items.$i.ekspedisi", $item->ekspedisi) }}" placeholder="Lion Parcel"
                                                   class="w-32 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                                        </td>
                                        <td class="px-3 py-3">
                                            <input type="text" name="items[{{ $i }}][service_pengiriman]" value="{{ old("items.$i.service_pengiriman", $item->service_pengiriman) }}" placeholder="REGPACK"
                                                   class="w-28 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                                        </td>
                                        <td class="px-3 py-3 text-center">
                                            <button type="button" onclick="hapusBaris(this)" class="text-red-500 hover:text-red-700 text-lg">×</button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-8 flex items-center gap-4">
                            <button type="submit"
                                    class="px-8 py-3 bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-2xl shadow transition">
                                Simpan Perubahan
                            </button>
                            <a href="{{ route('import.pasif.manual.show', $periode->id) }}"
                               class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-2xl transition">
                                Batal
                            </a>
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        let rowIndex = {{ $periode->transaksis->count() }};

        function tambahBaris() {
            const tbody = document.getElementById('bodyUnit');
            const tr = document.createElement('tr');
            tr.className = 'border-b';
            tr.innerHTML = `
                <td class="px-3 py-3 text-center">${rowIndex + 1}</td>
                <td class="px-3 py-3">
                    <input type="text" name="items[${rowIndex}][id_pesan]"
                           class="w-28 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                </td>
                <td class="px-3 py-3">
                    <input type="date" name="items[${rowIndex}][tgl_pesan]"
                           class="w-36 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                </td>
                
                <td class="px-3 py-3">
                    <input type="text" name="items[${rowIndex}][nama_unit]" required
                           class="w-48 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                </td>
                <td class="px-3 py-3">
                    <input type="text" name="items[${rowIndex}][label]" placeholder="M161"
                           class="w-24 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                </td>
                <td class="px-3 py-3">
                    <input type="number" name="items[${rowIndex}][jumlah]" min="1" value="1" required
                           class="w-20 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                </td>
                
                <td class="px-3 py-3">
                    <input type="text" name="items[${rowIndex}][ekspedisi]" placeholder="Lion Parcel"
                           class="w-32 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                </td>
                <td class="px-3 py-3">
                    <input type="text" name="items[${rowIndex}][service_pengiriman]" placeholder="REGPACK"
                           class="w-28 rounded-xl border-gray-300 text-sm focus:border-rose-500">
                </td>
                <td class="px-3 py-3 text-center">
                    <button type="button" onclick="hapusBaris(this)" class="text-red-500 hover:text-red-700 text-lg">×</button>
                </td>
            `;
            tbody.appendChild(tr);
            rowIndex++;
            updateNomor();
        }

        function hapusBaris(btn) {
            const tbody = document.getElementById('bodyUnit');
            if (tbody.children.length <= 1) {
                alert('Minimal harus ada 1 baris.');
                return;
            }
            btn.closest('tr').remove();
            updateNomor();
        }

        function updateNomor() {
            document.querySelectorAll('#bodyUnit tr').forEach((row, i) => {
                row.querySelector('td:first-child').textContent = i + 1;
            });
        }
    </script>
</body>
</html>