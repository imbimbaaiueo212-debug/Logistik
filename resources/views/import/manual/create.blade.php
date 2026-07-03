<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Manual Pemesanan</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body{
            font-family:Poppins,sans-serif;
        }
    </style>
</head>
<body class="bg-gray-100">

@include('partials.top-nav')

<div class="max-w-5xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">

        <div>
            <h1 class="text-3xl font-bold">
                Tambah Manual Pemesanan
            </h1>

            <p class="text-gray-500">
                Input data order manual
            </p>
        </div>

        <a href="{{ route('import.manual') }}"
            class="bg-gray-600 text-white px-5 py-3 rounded-xl hover:bg-gray-700">
            ← Kembali
        </a>

    </div>

    <div class="bg-white rounded-3xl shadow p-8">

        <form action="{{ route('import.manual.store') }}" method="POST">

            @csrf

            <div class="grid grid-cols-2 gap-6">

                <div>
                    <label>Tanggal Order</label>

                    <input
                        type="date"
                        name="order_date"
                        class="w-full border rounded-xl p-3"
                        value="{{ old('order_date') }}">
                </div>

                <div>
                    <label>Nama Customer</label>

                    <input
                        type="text"
                        name="customer_name"
                        class="w-full border rounded-xl p-3"
                        value="{{ old('customer_name') }}">
                </div>

                <div>
                    <label>SKU</label>

                    <input
                        type="text"
                        name="product_sku"
                        class="w-full border rounded-xl p-3"
                        value="{{ old('product_sku') }}">
                </div>

                <div>
                    <label>Produk</label>

                    <input
                        type="text"
                        name="product_name"
                        class="w-full border rounded-xl p-3"
                        value="{{ old('product_name') }}">
                </div>

                <div>
                    <label>Qty</label>

                    <input
                        type="number"
                        name="qty"
                        class="w-full border rounded-xl p-3"
                        value="{{ old('qty',1) }}">
                </div>

                <div>
                    <label>Harga</label>

                    <input
                        type="number"
                        name="price"
                        class="w-full border rounded-xl p-3"
                        value="{{ old('price') }}">
                </div>

                <div class="col-span-2">
                    <label>Status</label>

                    <select
                        name="status"
                        class="w-full border rounded-xl p-3">

                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>

                    </select>
                </div>

            </div>

            <div class="mt-8 flex justify-end">

                <button
                    class="bg-blue-600 text-white px-8 py-3 rounded-xl hover:bg-blue-700">

                    Simpan Data

                </button>

            </div>

        </form>

    </div>

</div>

</body>
</html>