<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail DLC - {{ $periode->edisi }} | biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
@include('partials.top-nav')

<div class="flex h-screen">
    <div class="flex-1 p-8 overflow-auto">

        <div class="flex justify-between items-center mb-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">{{ $periode->judul ?? $periode->edisi }}</h2>
                <p class="text-gray-500 mt-1">Periode: {{ $periode->periode }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('import.dlc.index') }}" 
                   class="px-5 py-2.5 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50 transition">
                    ← Kembali
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-xl mb-6">
                {{ session('success') }}
            </div>
        @endif

        <!-- Info Header -->
        <div class="bg-white rounded-3xl shadow p-6 mb-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Edisi</p>
                    <p class="font-semibold text-lg">{{ $periode->edisi }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Periode</p>
                    <p class="font-semibold text-lg">{{ $periode->periode }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Jumlah Unit</p>
                    <p class="font-semibold text-lg" id="jumlah-unit">{{ $periode->pesanan->count() }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Qty</p>
                    <p class="font-semibold text-lg text-blue-600" id="total-qty">{{ number_format($total) }}</p>
                </div>
            </div>
        </div>

        <!-- Tabel Unit -->
        <div class="bg-white rounded-3xl shadow overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600 w-16">No</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-600">Nama Unit</th>
                        <th class="px-6 py-4 text-right text-sm font-semibold text-gray-600">Qty</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-600 w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="tabel-pesanan">
                    @foreach($periode->pesanan as $index => $item)
                        <tr class="hover:bg-gray-50" id="row-{{ $item->id }}">
                            <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $item->nama_unit }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-gray-800">
                                <span id="qty-{{ $item->id }}">{{ number_format($item->qty) }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <button onclick="openEditModal({{ $item->id }}, '{{ $item->nama_unit }}', {{ $item->qty }})"
                                        class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50">
                    <tr>
                        <td colspan="2" class="px-6 py-4 text-right font-bold text-gray-700">Jumlah</td>
                        <td class="px-6 py-4 text-right font-bold text-blue-600 text-lg" id="footer-total">
                            {{ number_format($total) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>

<!-- ===== MODAL EDIT QTY ===== -->
<div id="editModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6">
        <h3 class="text-xl font-bold text-gray-800 mb-1">Edit Qty</h3>
        <p class="text-gray-500 text-sm mb-5" id="modal-unit-name">—</p>

        <div class="mb-5">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Qty</label>
            <input type="number" id="modal-qty" min="0"
                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-3 justify-end">
            <button onclick="closeEditModal()"
                    class="px-5 py-2.5 border border-gray-300 rounded-xl font-medium text-gray-700 hover:bg-gray-50">
                Batal
            </button>
            <button onclick="saveQty()"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-medium">
                Simpan
            </button>
        </div>
    </div>
</div>

<script>
    let currentId = null;

    function openEditModal(id, namaUnit, qty) {
        currentId = id;
        document.getElementById('modal-unit-name').textContent = namaUnit;
        document.getElementById('modal-qty').value = qty;
        document.getElementById('editModal').classList.remove('hidden');
        document.getElementById('editModal').classList.add('flex');
        
        const input = document.getElementById('modal-qty');
        input.focus();
        input.select(); // biar langsung terblok semua angka
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
        document.getElementById('editModal').classList.remove('flex');
        currentId = null;
    }

    function saveQty() {
        const qty = document.getElementById('modal-qty').value;

        if (qty === '' || qty < 0) {
            alert('Qty tidak valid');
            return;
        }

        fetch(`/import/dlc/pesanan/${currentId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ qty: parseInt(qty) })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal mengupdate');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan');
        });
    }

    // Tekan Enter di input → langsung simpan
    document.getElementById('modal-qty').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            saveQty();
        }
    });

    // Tutup modal jika klik di luar
    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });
</script>

</body>
</html>