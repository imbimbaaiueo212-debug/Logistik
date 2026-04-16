@extends('layouts.app')

@section('title', 'Buat Quotation')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <h2 class="text-xl font-bold mb-4">Buat Quotation</h2>

    <form method="POST" action="{{ route('quotation.store') }}">
        @csrf

        <!-- SUPPLIER -->
        <div class="mb-4">
            <label>Supplier</label>
            <select id="supplier" name="supplier_id"
                class="w-full border p-2 rounded" required>
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- TANGGAL -->
        <div class="mb-4">
            <label>Tanggal</label>
            <input type="date" name="date"
                   class="w-full border p-2 rounded" required>
        </div>

        <!-- ITEMS -->
        <h3 class="font-bold mt-4 mb-2">Items</h3>

        <table class="w-full border" id="items-table">
            <thead>
                <tr class="bg-gray-100">
                    <th>Produk</th>
                    <th width="120">Qty</th>
                    <th width="150">Harga</th>
                    <th width="150">Subtotal</th>
                    <th width="80">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>

        <button type="button" onclick="addRow()"
            class="mt-3 bg-blue-500 text-white px-3 py-1 rounded">
            + Tambah Item
        </button>

        <!-- TOTAL -->
        <div class="mt-6 text-right">
            <h3 class="text-lg font-bold">
                Total: Rp <span id="grand-total">0</span>
            </h3>
        </div>

        <!-- hidden total -->
        <input type="hidden" name="total" id="total-input">
        

        <div class="mt-6">
            <button class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
                Simpan
            </button>
            <a href="{{ route('quotation.index') }}"
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
        ← Back
    </a>    
        </div>

    </form>

</div>

<script>
let products = [];

// =========================
// LOAD PRODUK BY SUPPLIER
// =========================
document.getElementById('supplier').addEventListener('change', function () {

    let supplierId = this.value;

    if (!supplierId) {
        products = [];
        document.querySelector('#items-table tbody').innerHTML = '';
        updateGrandTotal();
        return;
    }

    fetch('/get-products/' + supplierId)
        .then(res => res.json())
        .then(data => {

            // FIX: ambil dari pivot
            products = data.map(p => ({
                id: p.id,
                name: p.name,
                price: p.price ?? (p.pivot ? p.pivot.price : 0)
            }));

            document.querySelector('#items-table tbody').innerHTML = '';
            updateGrandTotal();
        })
        .catch(err => console.error(err));
});


// =========================
// TAMBAH ROW
// =========================
function addRow() {

    if (products.length === 0) {
        alert('Pilih supplier dulu!');
        return;
    }

    let index = Date.now();

    let options = products.map(p => `
        <option value="${p.id}" data-price="${p.price}">
            ${p.name}
        </option>
    `).join('');

    let row = `
    <tr>
        <td>
            <select name="items[${index}][product_id]" class="product border p-2 w-full">
                ${options}
            </select>
        </td>
        <td>
            <input type="number" name="items[${index}][qty]" class="qty border p-2 w-full" value="1">
        </td>
        <td>
            <input type="number" name="items[${index}][price]" class="price border p-2 w-full" readonly>
        </td>
        <td>
            <input type="number" class="subtotal border p-2 w-full" readonly>
        </td>
        <td>
            <button type="button" onclick="removeRow(this)">❌</button>
        </td>
    </tr>
    `;

    document.querySelector('#items-table tbody').insertAdjacentHTML('beforeend', row);

    let lastRow = document.querySelector('#items-table tbody tr:last-child');

    setPrice(lastRow);
    calculateRow(lastRow);
}


// =========================
// HAPUS ROW
// =========================
function removeRow(btn) {
    btn.closest('tr').remove();
    updateGrandTotal();
}


// =========================
// EVENT LISTENER
// =========================
document.addEventListener('change', function(e) {

    let row = e.target.closest('tr');
    if (!row) return;

    if (e.target.classList.contains('product')) {
        setPrice(row);
        calculateRow(row);
    }

    if (e.target.classList.contains('qty')) {
        calculateRow(row);
    }
});


// =========================
// SET PRICE
// =========================
function setPrice(row) {

    let select = row.querySelector('.product');
    let selectedOption = select.options[select.selectedIndex];

    let price = selectedOption.dataset.price;

    if (!price || price === 'undefined') {
        price = 0;
    }

    row.querySelector('.price').value = price;
}


// =========================
// HITUNG SUBTOTAL
// =========================
function calculateRow(row) {

    let qty = parseFloat(row.querySelector('.qty').value) || 0;
    let price = parseFloat(row.querySelector('.price').value) || 0;

    let subtotal = qty * price;

    row.querySelector('.subtotal').value = subtotal;

    updateGrandTotal();
}


// =========================
// GRAND TOTAL
// =========================
function updateGrandTotal() {

    let total = 0;

    document.querySelectorAll('.subtotal').forEach(el => {
        total += parseFloat(el.value) || 0;
    });

    document.getElementById('grand-total').innerText = total.toLocaleString();

    // kirim ke backend
    document.getElementById('total-input').value = total;
}
</script>

@endsection