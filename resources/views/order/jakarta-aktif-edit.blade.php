<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jakarta Aktif - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        .form-input {
            @apply w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500;
        }
        .form-textarea {
            @apply w-full border border-gray-300 rounded-xl px-4 py-3 focus:outline-none focus:border-blue-500 min-h-[100px] resize-y;
        }
    </style>
</head>
<body class="bg-gray-50">

    <!-- Top Navigation -->
    @include('partials.top-nav')

    <!-- Main Content -->
    <div class="max-w-screen-2xl mx-auto px-6 py-6">

        <div class="max-w-2xl mx-auto">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">Edit Data Jakarta Aktif</h1>
                    <p class="text-gray-600">ID Pesan: <strong>{{ $item->id_pesan }}</strong></p>
                </div>
                <a href="{{ route('order.jakarta-aktif') }}" 
                   class="bg-gray-600 text-white px-5 py-3 rounded-2xl font-semibold hover:bg-gray-700 flex items-center gap-2">
                    ← Kembali
                </a>
            </div>

            <div class="bg-white rounded-3xl shadow p-8">
                <form method="POST" action="{{ route('order.jakarta-aktif.update', $item->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Unit</label>
                            <input type="text" name="nama_unit" value="{{ old('nama_unit', $item->nama_unit) }}" 
                                   class="form-input">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cabang</label>
                            <input type="text" name="billing_last_name" value="{{ old('billing_last_name', $item->billing_last_name) }}" 
                                   class="form-input">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Pesanan</label>
                            <input type="text" name="pesanan" value="{{ old('pesanan', $item->pesanan) }}" 
                                   class="form-input">
                        </div>

                        <!-- ALAMAT PENGIRIMAN -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Pengiriman</label>
                            <textarea name="alamat_pengiriman" 
                                    class="form-textarea"
                                    placeholder="Masukkan alamat lengkap pengiriman..."
                                    rows="4">{{ old('alamat_pengiriman', $item->kirim ?? '') }}</textarea>
                            <p class="text-xs text-gray-500 mt-1">Alamat lengkap termasuk RT/RW, kelurahan, kecamatan, kota, dan kode pos.</p>
                        </div>

                        <!-- SERVICE PENGIRIMAN (BARU) -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Service Pengiriman</label>
                            <input type="text" name="service_pengiriman" 
                                   value="{{ old('service_pengiriman', $item->service_pengiriman ?? '') }}" 
                                   class="form-input"
                                   placeholder="Contoh: YES, REG, OKE, CTC, etc">
                            <p class="text-xs text-gray-500 mt-1">Jenis layanan kurir (contoh: YES, REG, OKE, Same Day, dll)</p>
                        </div>

                        <!-- EKSPEDISI DROPDOWN -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ekspedisi</label>
                            <select name="ekspedisi" class="form-input">
                                <option value="" {{ empty(old('ekspedisi', $item->ekspedisi)) ? 'selected' : '' }}>-- Pilih Ekspedisi --</option>
                                <option value="Ambil Sendiri" {{ old('ekspedisi', $item->ekspedisi) == 'Ambil Sendiri' ? 'selected' : '' }}>Ambil Sendiri</option>
                                <option value="Driver" {{ old('ekspedisi', $item->ekspedisi) == 'Driver' ? 'selected' : '' }}>Driver</option>
                                <option value="JNE" {{ old('ekspedisi', $item->ekspedisi) == 'JNE' ? 'selected' : '' }}>JNE</option>
                                <option value="Lion Parcel" {{ old('ekspedisi', $item->ekspedisi) == 'Lion Parcel' ? 'selected' : '' }}>Lion Parcel</option>
                                <option value="TIKI" {{ old('ekspedisi', $item->ekspedisi) == 'TIKI' ? 'selected' : '' }}>TIKI</option>
                                <option value="J&T" {{ old('ekspedisi', $item->ekspedisi) == 'J&T' ? 'selected' : '' }}>J&T</option>
                                <option value="SiCepat" {{ old('ekspedisi', $item->ekspedisi) == 'SiCepat' ? 'selected' : '' }}>SiCepat</option>
                                <option value="Pos Indonesia" {{ old('ekspedisi', $item->ekspedisi) == 'Pos Indonesia' ? 'selected' : '' }}>Pos Indonesia</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Kirim</label>
                            <select name="status_kirim" class="form-input">
                                <option value="Dikirim" {{ old('status_kirim', $item->status_kirim) == 'Dikirim' ? 'selected' : '' }}>✅ Dikirim</option>
                                <option value="Belum Dikirim" {{ old('status_kirim', $item->status_kirim) == 'Belum Dikirim' ? 'selected' : '' }}>⏳ Belum Dikirim</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status Pembayaran</label>
                            <input type="text" name="status_pembayaran" value="{{ old('status_pembayaran', $item->status_pembayaran) }}" 
                                   class="form-input">
                        </div>
                        
                    </div>

                    <div class="mt-8 flex gap-4">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 text-white py-3.5 rounded-2xl font-semibold hover:bg-blue-700 transition">
                            💾 Simpan Perubahan
                        </button>
                        <a href="{{ route('order.jakarta-aktif') }}" 
                           class="flex-1 text-center border border-gray-300 py-3.5 rounded-2xl font-semibold hover:bg-gray-50 transition">
                            Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

    </div>

</body>
</html>