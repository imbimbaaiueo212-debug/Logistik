<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buat Picking List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100">

    @include('partials.top-nav')

    <div class="max-w-5xl mx-auto p-8">
        <h2 class="text-3xl font-bold mb-6">Buat Picking List</h2>

        @if($order)
        <form action="{{ route('picking.store') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="bg-white rounded-3xl shadow p-8 mb-8">
                <div class="grid grid-cols-2 gap-6 mb-8">
                    <div>
                        <strong>Nama Unit:</strong> {{ $order->nama_unit ?? '-' }}<br>
                        <strong>CAB:</strong> {{ $order->billing_last_name ?? '-' }}<br>
                        <strong>NIM:</strong> {{ $order->billing_company ?? '-' }}
                    </div>
                    <div class="text-right">
                        <strong>ID Pesan:</strong> {{ $order->id_pesan ?? '-' }}<br>
                        <strong>Tanggal:</strong> {{ $order->tgl_pesan ? \Carbon\Carbon::parse($order->tgl_pesan)->format('d F Y') : '-' }}
                    </div>
                </div>

                <!-- Preview A5 -->
                <div class="border-2 border-dashed border-gray-300 p-4 rounded-2xl">
                    @include('picking.print', [
                        'item' => $order,
                        'no_pl' => 'PL-' . date('YmdHis'),
                        'tgl_order' => $order->tgl_pesan,
                        'data' => collect([ (object)[
                            'item_name' => $order->pesanan ?? 'Produk Utama',
                            'item_sku'  => '-',
                            'item_qty'  => 1
                        ]])
                    ])
                </div>
            </div>

            <div class="flex justify-center gap-4">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-10 py-4 rounded-2xl text-lg font-semibold">
                    💾 Simpan Picking List
                </button>
                <button onclick="window.print()" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-10 py-4 rounded-2xl text-lg font-semibold">
                    🖨 Cetak
                </button>
            </div>
        </form>
        @else
            <p class="text-red-500">Order tidak ditemukan.</p>
        @endif
    </div>
</body>
</html>