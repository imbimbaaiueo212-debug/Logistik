@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="bg-white rounded-2xl shadow p-8 max-w-3xl mx-auto">
    <h2 class="text-2xl font-semibold mb-6">Edit Product</h2>

    <form action="{{ route('products.update', $product->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Nama Produk <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">SKU / Label</label>
                <input type="text" name="sku" value="{{ old('sku', $product->sku ?? $product->label) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <!-- Kolom lainnya tetap sama seperti create, tapi pakai value dari $product -->

            <div>
                <label class="block text-sm font-medium mb-1">Jenis</label>
                <input type="text" name="jenis" value="{{ old('jenis', $product->jenis) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Satuan</label>
                <input type="text" name="satuan" value="{{ old('satuan', $product->satuan) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Hal</label>
                <input type="number" name="hal" value="{{ old('hal', $product->hal) }}" min="1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Lembar</label>
                <input type="number" name="lembar" value="{{ old('lembar', $product->lembar) }}" min="1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Kertas</label>
                <input type="text" name="kertas" value="{{ old('kertas', $product->kertas) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Berat Satuan (kg)</label>
                <input type="number" name="berat_satuan" id="berat_satuan" step="0.001"
                       value="{{ old('berat_satuan', $product->berat_satuan) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Isi</label>
                <input type="number" name="isi" id="isi" value="{{ old('isi', $product->isi) }}" min="1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Berat Paket (kg)</label>
                <input type="text" id="berat_paket_display" readonly
                       value="{{ $product->berat_paket ? number_format($product->berat_paket, 3) . ' kg' : '' }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3 bg-gray-100">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Harga Beli (Rp)</label>
                <input type="number" name="harga_beli" id="harga_beli" step="0.01"
                       value="{{ old('harga_beli', $product->harga_beli) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Harga Jual (Rp)</label>
                <input type="number" name="harga_jual" step="0.01"
                       value="{{ old('harga_jual', $product->harga_jual) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
                <small class="text-gray-500">Bisa diubah manual jika diperlukan</small>
            </div>

            <!-- Role, Status, Tanggal Rilis tetap sama seperti sebelumnya -->

            <div>
                <label class="block text-sm font-medium mb-1">Role</label>
                <select name="role" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <option value="">-- Pilih Role --</option>
                    <option value="jual" {{ old('role', $product->role) == 'jual' ? 'selected' : '' }}>Jual</option>
                    <option value="tidak_dijual" {{ old('role', $product->role) == 'tidak_dijual' ? 'selected' : '' }}>Tidak Dijual</option>
                    <option value="stock" {{ old('role', $product->role) == 'stock' ? 'selected' : '' }}>Stock</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Status</label>
                <input type="text" name="status" value="{{ old('status', $product->status) }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium mb-1">Tanggal Rilis</label>
                <input type="date" name="tanggal_rilis" 
                       value="{{ old('tanggal_rilis', $product->tanggal_rilis ? $product->tanggal_rilis->format('Y-m-d') : '') }}"
                       class="w-full border border-gray-300 rounded-lg px-4 py-3">
            </div>

        </div>

        <div class="mt-8 flex gap-4">
            <button type="submit" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white py-3 rounded-lg font-medium">
                Simpan Perubahan
            </button>
            <a href="{{ route('products.index') }}" class="flex-1 bg-gray-300 hover:bg-gray-400 text-center py-3 rounded-lg font-medium">
                Kembali
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Script hitung berat paket (sama seperti create)
    const beratSatuan = document.getElementById('berat_satuan');
    const isi = document.getElementById('isi');
    const display = document.getElementById('berat_paket_display');

    function hitungBeratPaket() {
        const berat = parseFloat(beratSatuan.value) || 0;
        const jumlah = parseFloat(isi.value) || 0;
        if (berat > 0 && jumlah > 0) {
            display.value = (berat * jumlah).toFixed(3) + ' kg';
        } else {
            display.value = '';
        }
    }

    beratSatuan.addEventListener('input', hitungBeratPaket);
    isi.addEventListener('input', hitungBeratPaket);
    hitungBeratPaket(); // hitung saat load
});
</script>

@endsection