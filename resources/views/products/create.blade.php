@extends('layouts.app')

@section('title', 'Tambah Product')

@section('content')

<div class="bg-white rounded-2xl shadow p-8 max-w-3xl mx-auto">
    <h2 class="text-2xl font-semibold mb-6">Tambah Product Baru</h2>

    <form action="{{ route('products.store') }}" method="POST">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <!-- SKU / Label digabung jadi satu -->
            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">SKU / Label</label>
                <input type="text" name="sku" value="{{ old('sku') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3"
                       placeholder="Masukkan SKU atau Label">
                <small class="text-gray-500">SKU unik jika diisi, atau bisa digunakan sebagai Label</small>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Jenis</label>
                <input type="text" name="jenis" value="{{ old('jenis') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3"
                       placeholder="contoh: Modul, Buku, dll">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Satuan</label>
                <input type="text" name="satuan" value="{{ old('satuan') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Hal (Halaman)</label>
                <input type="number" name="hal" value="{{ old('hal') }}" min="1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Lembar</label>
                <input type="number" name="lembar" value="{{ old('lembar') }}" min="1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Kertas</label>
                <input type="text" name="kertas" value="{{ old('kertas') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Berat Satuan (kg)</label>
                <input type="number" name="berat_satuan" id="berat_satuan" step="0.001" value="{{ old('berat_satuan') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Isi</label>
                <input type="number" name="isi" id="isi" value="{{ old('isi') }}" min="1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Berat Paket (kg)</label>
                <input type="text" id="berat_paket_display" readonly
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-100">
                <small class="text-gray-500">Otomatis: Berat Satuan × Isi</small>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Harga Beli (Rp)</label>
                <input type="number" name="harga_beli" id="harga_beli" step="0.01" value="{{ old('harga_beli') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Harga Jual (Rp) <span class="text-blue-600">(Otomatis)</span></label>
                <input type="text" id="harga_jual_display" readonly
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-100 font-medium">
                <small class="text-gray-500">Dihitung otomatis berdasarkan rumus di controller</small>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">-- Pilih Role --</option>
                    <option value="jual" {{ old('role') == 'jual' ? 'selected' : '' }}>Jual</option>
                    <option value="tidak_dijual" {{ old('role') == 'tidak_dijual' ? 'selected' : '' }}>Tidak Dijual</option>
                    <option value="stock" {{ old('role') == 'stock' ? 'selected' : '' }}>Stock</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <input type="text" name="status" value="{{ old('status') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Tanggal Rilis</label>
                <input type="date" name="tanggal_rilis" value="{{ old('tanggal_rilis') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

        </div>

        <div class="mt-8 flex gap-4">
            <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-lg font-medium">
                Simpan Product
            </button>
            <a href="{{ route('products.index') }}" 
               class="flex-1 bg-gray-300 hover:bg-gray-400 text-center py-3 rounded-lg font-medium">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const beratSatuan = document.getElementById('berat_satuan');
    const isi         = document.getElementById('isi');
    const hargaBeli   = document.getElementById('harga_beli');
    const displayBerat = document.getElementById('berat_paket_display');
    const displayHarga = document.getElementById('harga_jual_display');

    function hitungBeratPaket() {
        const berat = parseFloat(beratSatuan.value) || 0;
        const jumlah = parseFloat(isi.value) || 0;
        if (berat > 0 && jumlah > 0) {
            displayBerat.value = (berat * jumlah).toFixed(3) + ' kg';
        } else {
            displayBerat.value = '';
        }
    }

    function hitungHargaJual() {
        const hb = parseFloat(hargaBeli.value) || 0;
        const isinya = parseInt(isi.value) || 0;
        const jenis = document.querySelector('input[name="jenis"]').value.toLowerCase().trim();

        if (hb > 0 && isinya > 0) {
            const multiplier = jenis.includes('modul') ? 1.49 : 1.20;
            const hargaDasar = hb * multiplier;
            const hargaJual  = hargaDasar * isinya;
            const hargaBulat = Math.ceil(hargaJual / 50) * 50;

            displayHarga.value = 'Rp ' + hargaBulat.toLocaleString('id-ID');
        } else {
            displayHarga.value = '';
        }
    }

    // Event listeners
    beratSatuan.addEventListener('input', hitungBeratPaket);
    isi.addEventListener('input', () => {
        hitungBeratPaket();
        hitungHargaJual();
    });
    hargaBeli.addEventListener('input', hitungHargaJual);
    document.querySelector('input[name="jenis"]').addEventListener('input', hitungHargaJual);
});
</script>

@endsection
