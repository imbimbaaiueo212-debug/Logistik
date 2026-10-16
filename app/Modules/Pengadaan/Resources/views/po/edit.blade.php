@extends('layouts.app')

@section('title', 'Edit Purchase Order')

@section('content')
<div class="bg-white p-6 rounded-2xl shadow">
    <h3 class="text-xl font-semibold mb-6">Edit Purchase Order #{{ $po->id }}</h3>

    <form action="{{ route('po.update', $po) }}" method="POST">
        @csrf
        @method('PUT')

        <!-- Supplier -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
            <select id="supplier" name="supplier_id" 
                    class="border border-gray-300 p-3 w-full rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <option value="">Pilih Supplier</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" 
                        {{ old('supplier_id', $po->supplier_id) == $s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Table Products -->
        <table class="w-full border border-gray-200 mb-6 rounded-lg overflow-hidden">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left font-medium text-gray-600">Product</th>
                    <th class="p-3 text-left font-medium text-gray-600">Harga (Rp)</th>
                    <th class="p-3 text-left font-medium text-gray-600">Qty</th>
                    <th class="p-3 text-left font-medium text-gray-600">Subtotal (Rp)</th>
                </tr>
            </thead>
            <tbody id="po-body" class="divide-y divide-gray-200">
                <!-- Diisi oleh JavaScript -->
            </tbody>
        </table>

        <div class="text-right mb-6">
            <h3 class="text-xl font-bold">
                Total: Rp <span id="total" class="text-blue-600">0</span>
            </h3>
        </div>

        <div class="flex gap-3">
            <button type="submit" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition">
                Update PO
            </button>
            <a href="{{ route('po.index') }}" 
               class="px-6 py-3 text-gray-600 hover:text-gray-800 font-medium transition">
                Batal
            </a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supplierSelect = document.getElementById('supplier');
    const poItems = @json($po->items);           // Data item PO existing (paling aman)
    let currentProducts = [];                    // Simpan data product yang sedang ditampilkan

    function loadProducts(supplierId) {
        fetch(`/get-products/${supplierId}`)
            .then(response => {
                if (!response.ok) throw new Error('Gagal mengambil data produk');
                return response.json();
            })
            .then(products => {
                currentProducts = products;
                const tbody = document.getElementById('po-body');
                tbody.innerHTML = '';

                products.forEach((product, index) => {
                    // Cari item yang sudah ada di PO
                    const existing = poItems.find(item => parseInt(item.product_id) === parseInt(product.id));
                    
                    const price = product.pivot?.price 
                               || product.price 
                               || 0;
                    
                    const qty = existing ? parseFloat(existing.qty) : 0;
                    const subtotal = qty * price;

                    const rowHTML = `
                        <tr class="hover:bg-gray-50">
                            <td class="p-3">
                                ${product.name}
                                <input type="hidden" name="products[${index}][product_id]" value="${product.id}">
                            </td>
                            <td class="p-3 font-medium">
                                ${parseFloat(price).toLocaleString('id-ID')}
                                <input type="hidden" name="products[${index}][price]" value="${price}">
                            </td>
                            <td class="p-3">
                                <input type="number" 
                                       name="products[${index}][qty]" 
                                       class="qty border border-gray-300 p-2 w-24 rounded focus:ring-2 focus:ring-blue-500 text-center" 
                                       value="${qty}" 
                                       min="0"
                                       data-price="${price}">
                            </td>
                            <td class="p-3 subtotal font-medium text-right">${Math.round(subtotal).toLocaleString('id-ID')}</td>
                        </tr>
                    `;

                    tbody.innerHTML += rowHTML;
                });

                attachQuantityEvents();
                calculateTotal();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Gagal memuat daftar produk. Silakan coba lagi.');
            });
    }

    function attachQuantityEvents() {
        document.querySelectorAll('.qty').forEach(input => {
            input.addEventListener('input', function() {
                const qty = parseFloat(this.value) || 0;
                const price = parseFloat(this.dataset.price) || 0;
                const subtotalEl = this.closest('tr').querySelector('.subtotal');
                
                const subtotal = qty * price;
                subtotalEl.textContent = Math.round(subtotal).toLocaleString('id-ID');
                
                calculateTotal();
            });
        });
    }

    function calculateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(el => {
            const value = parseFloat(el.textContent.replace(/[^0-9.-]+/g,"")) || 0;
            total += value;
        });
        
        document.getElementById('total').textContent = Math.round(total).toLocaleString('id-ID');
    }

    // Inisialisasi saat halaman pertama kali dimuat
    if (supplierSelect.value) {
        loadProducts(supplierSelect.value);
    }

    // Event ketika supplier diubah
    supplierSelect.addEventListener('change', function() {
        const supplierId = this.value;
        
        if (supplierId) {
            loadProducts(supplierId);
        } else {
            document.getElementById('po-body').innerHTML = '';
            document.getElementById('total').textContent = '0';
        }
    });
});
</script>
@endsection