<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jakarta Aktif - biMBA AIUEO Logistik</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Poppins', sans-serif; }
        
        .form-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }
        
        .form-textarea {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 12px;
            padding: 12px 16px;
            font-size: 14px;
            min-height: 90px;
            resize: vertical;
        }
        .form-textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
        }

        .section-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .money-input {
            background: #f0f9ff;
            border-color: #93c5fd;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100">

    @include('partials.top-nav')

    <div class="max-w-4xl mx-auto px-4 py-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Data Jakarta Aktif</h1>
                <p class="text-gray-500 text-sm mt-1">
                    ID Pesan: <span class="font-semibold text-blue-600">{{ $item->id_pesan }}</span>
                </p>
            </div>
            <a href="{{ route('order.jakarta-aktif') }}" 
               class="inline-flex items-center gap-2 bg-gray-600 hover:bg-gray-700 text-white px-5 py-2.5 rounded-xl text-sm font-semibold transition">
                ← Kembali
            </a>
        </div>

        <form method="POST" action="{{ route('order.jakarta-aktif.update', $item->id) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 md:p-8 space-y-6">

                    {{-- ==================== 1. INFORMASI UNIT ==================== --}}
                    <div class="section-card">
                        <div class="section-title">
                            <span class="text-blue-600">①</span> Informasi Unit
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label class="form-label">Nama Unit</label>
                                <input type="text" name="nama_unit" 
                                       value="{{ old('nama_unit', $item->nama_unit) }}" 
                                       class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Cabang / Billing Last Name</label>
                                <input type="text" name="billing_last_name" 
                                       value="{{ old('billing_last_name', $item->billing_last_name) }}" 
                                       class="form-input">
                            </div>

                            <div class="md:col-span-2">
                                <label class="form-label">Pesanan</label>
                                <input type="text" name="pesanan" 
                                       value="{{ old('pesanan', $item->pesanan) }}" 
                                       class="form-input">
                            </div>
                        </div>
                    </div>

                    {{-- ==================== 2. ALAMAT & PENGIRIMAN ==================== --}}
                    <div class="section-card">
                        <div class="section-title">
                            <span class="text-blue-600">②</span> Alamat & Pengiriman
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <label class="form-label">Alamat Pengiriman</label>
                                <textarea name="alamat_pengiriman" rows="3" class="form-textarea"
                                          placeholder="Alamat lengkap...">{{ old('alamat_pengiriman', $item->kirim ?? $item->alamat_kirim ?? '') }}</textarea>
                            </div>

                            <div>
                                <label class="form-label">Ekspedisi</label>
                                <select name="ekspedisi" class="form-input">
                                    <option value="">-- Pilih Ekspedisi --</option>
                                    @foreach(['Ambil Sendiri', 'Driver', 'JNE', 'Lion Parcel', 'TIKI', 'J&T', 'SiCepat', 'Pos Indonesia'] as $exp)
                                        <option value="{{ $exp }}" {{ old('ekspedisi', $item->ekspedisi) == $exp ? 'selected' : '' }}>
                                            {{ $exp }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Service Pengiriman</label>
                                <input type="text" name="service_pengiriman" 
                                       value="{{ old('service_pengiriman', $item->service_pengiriman) }}" 
                                       class="form-input"
                                       placeholder="YES, REG, OKE, REGPACK...">
                            </div>

                            <div>
                                <label class="form-label">Status Kirim</label>
                                <select name="status_kirim" class="form-input">
                                    <option value="Dikirim" {{ old('status_kirim', $item->status_kirim) == 'Dikirim' ? 'selected' : '' }}>Dikirim</option>
                                    <option value="Diambil" {{ old('status_kirim', $item->status_kirim) == 'Diambil' ? 'selected' : '' }}>Diambil</option>
                                    <option value="Belum Dikirim" {{ old('status_kirim', $item->status_kirim) == 'Belum Dikirim' ? 'selected' : '' }}>Belum Dikirim</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Status Pembayaran</label>
                                <select name="status_pembayaran" class="form-input">
                                    <option value="SETTLED" {{ old('status_pembayaran', $item->status_pembayaran) == 'SETTLED' ? 'selected' : '' }}>SETTLED</option>
                                    <option value="SUCCESS" {{ old('status_pembayaran', $item->status_pembayaran) == 'SUCCESS' ? 'selected' : '' }}>SUCCESS</option>
                                    <option value="REFUND" {{ old('status_pembayaran', $item->status_pembayaran) == 'REFUND' ? 'selected' : '' }}>REFUND</option>
                                    <option value="REFUNDED" {{ old('status_pembayaran', $item->status_pembayaran) == 'REFUNDED' ? 'selected' : '' }}>REFUNDED</option>
                                    <option value="PENDING" {{ old('status_pembayaran', $item->status_pembayaran) == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                                    <option value="MANUAL" {{ old('status_pembayaran', $item->status_pembayaran) == 'MANUAL' ? 'selected' : '' }}>MANUAL</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== 3. NOMINAL & KEUANGAN ==================== --}}
                    <div class="section-card" style="background: #eff6ff; border-color: #bfdbfe;">
                        <div class="section-title">
                            <span class="text-blue-600">③</span> Nominal & Keuangan
                            <span class="ml-2 text-xs font-medium text-red-500 bg-red-50 px-2 py-0.5 rounded-full">Penting untuk Refund</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                            <div>
                                <label class="form-label">Harga (Rp)</label>
                                <input type="number" step="0.01" name="harga" 
                                       value="{{ old('harga', $item->harga) }}" 
                                       class="form-input money-input">
                            </div>

                            <div>
                                <label class="form-label">Diskon (Rp)</label>
                                <input type="number" step="0.01" name="diskon" 
                                       value="{{ old('diskon', $item->diskon) }}" 
                                       class="form-input money-input">
                            </div>

                            <div>
                                <label class="form-label">Ongkir (Rp)</label>
                                <input type="number" step="0.01" name="ongkir" 
                                       value="{{ old('ongkir', $item->ongkir) }}" 
                                       class="form-input money-input">
                            </div>

                            <div>
                                <label class="form-label">Fee Payment (Rp)</label>
                                <input type="number" step="0.01" name="fee_payment" 
                                       value="{{ old('fee_payment', $item->fee_payment) }}" 
                                       class="form-input money-input">
                            </div>

                            <div>
                                <label class="form-label">Berat (gram)</label>
                                <input type="number" step="0.01" name="berat" 
                                       value="{{ old('berat', $item->berat) }}" 
                                       class="form-input">
                            </div>

                            <div>
                                <label class="form-label text-blue-700">Order Total / Nominal (Rp)</label>
                                <input type="number" step="0.01" name="total" 
                                       value="{{ old('total', $item->total) }}" 
                                       class="form-input money-input border-blue-400 text-base font-bold">
                                <p class="text-xs text-gray-500 mt-1.5">Ubah nilai ini saat terjadi refund</p>
                            </div>
                        </div>
                    </div>

                    {{-- ==================== 4. CATATAN ==================== --}}
                    <div class="section-card">
                        <div class="section-title">
                            <span class="text-blue-600">④</span> Catatan
                        </div>
                        <textarea name="catatan" rows="3" class="form-textarea"
                                  placeholder="Contoh: Partial refund Rp 50.000 karena barang rusak...">{{ old('catatan', $item->catatan) }}</textarea>
                    </div>

                </div>

                {{-- Tombol Aksi --}}
                <div class="bg-gray-50 border-t border-gray-200 px-6 py-5 flex flex-col sm:flex-row gap-3">
                    <button type="submit" 
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold transition">
                        💾 Simpan Perubahan
                    </button>
                    <a href="{{ route('order.jakarta-aktif') }}" 
                       class="flex-1 text-center border border-gray-300 hover:bg-white py-3 rounded-xl font-semibold text-gray-700 transition">
                        Batal
                    </a>
                </div>
            </div>
        </form>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function () {
    const totalInput = document.querySelector('input[name="total"]');
    const statusSelect = document.querySelector('select[name="status_pembayaran"]');

    if (!totalInput || !statusSelect) return;

    function checkTotal() {
        const value = parseFloat(totalInput.value);

        if (!isNaN(value) && value === 0) {
            // Otomatis set ke REFUND
            statusSelect.value = 'REFUND';
            
            // Optional: kasih highlight
            statusSelect.classList.add('border-red-400', 'bg-red-50');
            totalInput.classList.add('border-red-400', 'bg-red-50');
        } else {
            // Hapus highlight kalau bukan 0
            statusSelect.classList.remove('border-red-400', 'bg-red-50');
            totalInput.classList.remove('border-red-400', 'bg-red-50');
        }
    }

    // Cek saat mengetik / berubah
    totalInput.addEventListener('input', checkTotal);
    totalInput.addEventListener('change', checkTotal);

    // Cek sekali saat halaman load
    checkTotal();
});
</script>

</body>
</html>