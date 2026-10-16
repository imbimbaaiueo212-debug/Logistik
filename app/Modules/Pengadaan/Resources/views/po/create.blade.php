@extends('layouts.app')

@section('title', 'Purchase Order')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <form action="{{ route('po.store') }}" method="POST">
        @csrf

        <!-- Supplier -->
        <select id="supplier" name="supplier_id" class="border p-2 mb-4 w-full" required>
            <option value="">Pilih Supplier</option>
            @foreach($suppliers as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
            @endforeach
        </select>

        <!-- Table -->
        <table class="w-full border mb-4">
            <thead>
                <tr>
                    <th class="p-2">Product</th>
                    <th class="p-2">Harga</th>
                    <th class="p-2">Qty</th>
                    <th class="p-2">Subtotal</th>
                    <th class="p-2">Aksi</th>
                </tr>
            </thead>
            <tbody id="po-body"></tbody>
        </table>

        <!-- Button tambah -->
        <button type="button" onclick="addRow()" 
            class="bg-green-500 text-white px-3 py-1 rounded mb-4">
            + Tambah Item
        </button>

        <h3 class="text-right font-bold mb-4">
            Total: Rp <span id="total">0</span>
        </h3>

        <button class="bg-green-500 text-white px-4 py-2 rounded">
            Simpan PO
        </button>
        <a href="{{ route('po.index') }}" class="bg-blue-500 ml-2 text-white roundedd px-4 py-2 border">
            Batal
        </a>

    </form>

</div>

<script>
let products = [];

// =========================
// LOAD PRODUK BY SUPPLIER
// =========================
document.getElementById('supplier').addEventListener('change', function() {
    let supplierId = this.value;

    if (!supplierId) return;

    fetch(`/get-products/${supplierId}`)
        .then(res => res.json())
        .then(data => {
            products = data;

            // reset table
            document.getElementById('po-body').innerHTML = '';
            document.getElementById('total').innerText = 0;
        });
});

// =========================
// TAMBAH ROW
// =========================
function addRow() {

    if (products.length === 0) {
        alert('Pilih supplier dulu!');
        return;
    }

    let tbody = document.getElementById('po-body');
    let index = tbody.children.length;

    let options = `<option value="">Pilih Produk</option>`;

    products.forEach(p => {
        options += `<option value="${p.id}" data-price="${p.pivot.price}">
                        ${p.name}
                    </option>`;
    });

    let row = `
        <tr>
            <td class="p-2">
                <select name="products[${index}][product_id]" 
                        class="product border p-1 w-full" required>
                    ${options}
                </select>
            </td>

            <td class="p-2 price text-center">0</td>

            <td class="p-2 text-center">
                <input type="number" name="products[${index}][qty]" 
                       class="qty border p-1 w-20" min="1" required>
            </td>

            <td class="p-2 subtotal text-center">0</td>

            <td class="p-2 text-center">
                <button type="button" onclick="removeRow(this)" 
                    class="bg-red-500 text-white px-2 py-1 rounded">
                    X
                </button>
            </td>
        </tr>
    `;

    tbody.insertAdjacentHTML('beforeend', row);

    attachEvents();
}

// =========================
// EVENT HANDLER
// =========================
function attachEvents() {

    document.querySelectorAll('.product').forEach(select => {
        select.onchange = function() {

            let price = this.options[this.selectedIndex].dataset.price || 0;

            let row = this.closest('tr');

            row.querySelector('.price').innerText = formatRupiah(price);
            row.querySelector('.qty').dataset.price = price;

            calculateRow(row);
        };
    });

    document.querySelectorAll('.qty').forEach(input => {
        input.oninput = function() {
            calculateRow(this.closest('tr'));
        };
    });
}

// =========================
// HITUNG SUBTOTAL
// =========================
function calculateRow(row) {

    let qty = row.querySelector('.qty').value || 0;
    let price = row.querySelector('.qty').dataset.price || 0;

    let subtotal = qty * price;

    row.querySelector('.subtotal').innerText = formatRupiah(subtotal);

    calculateTotal();
}

// =========================
// HITUNG TOTAL
// =========================
function calculateTotal() {
    let total = 0;

    document.querySelectorAll('.subtotal').forEach(el => {
        let value = el.innerText.replace(/\D/g, '');
        total += parseInt(value) || 0;
    });

    document.getElementById('total').innerText = formatRupiah(total);
}

// =========================
// HAPUS ROW
// =========================
function removeRow(button) {
    button.closest('tr').remove();
    calculateTotal();
}

// =========================
// FORMAT RUPIAH
// =========================
function formatRupiah(angka) {
    return new Intl.NumberFormat('id-ID').format(angka);
}
</script>

@endsection