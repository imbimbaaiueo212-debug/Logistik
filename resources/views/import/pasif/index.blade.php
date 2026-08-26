<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unit Pasif - biMBA AIUEO Logistik</title>
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
                <h2 class="text-3xl font-bold text-gray-800">Unit Pasif</h2>
                <p class="text-gray-500 mt-1">Data pemesanan majalah Unit Pasif</p>
            </div>
            
        </div>

        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-xl">
                {!! session('success') !!}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-100 text-gray-600 text-sm uppercase">
                    <tr>
                        <th class="px-6 py-4">Edisi</th>
                        <th class="px-6 py-4">Periode</th>
                        <th class="px-6 py-4">No PS</th>
                        <th class="px-6 py-4 text-center">Jumlah Unit</th>
                        <th class="px-6 py-4 text-center">Total Qty</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($periodes as $periode)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 font-semibold text-blue-700">
                                {{ $periode->edisi }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $periode->periode ?? ($periode->bulan . ' ' . $periode->tahun) }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <input type="text"
                                        value="{{ $periode->no_ps ?? '' }}"
                                        data-id="{{ $periode->id }}"
                                        placeholder="Isi No PS"
                                        class="no-ps-input w-28 border border-gray-300 rounded-lg px-2 py-1 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <button type="button"
                                            onclick="updateNoPs(this)"
                                            class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-700 px-2 py-1 rounded-lg font-medium transition">
                                        Simpan
                                    </button>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $periode->pesanan_count }}
                            </td>
                            <td class="px-6 py-4 text-center font-medium">
                                {{ number_format($periode->pesanan_sum_qty ?? 0) }}
                            </td>
                            <td class="px-6 py-4">
                                @if($periode->status === 'aktif')
                                    <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">Aktif</span>
                                @else
                                    <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-medium">Nonaktif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('import.pasif.show', $periode->id) }}"
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Detail
                                    </a>
                                    <form action="{{ route('import.pasif.destroy', $periode->id) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin hapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                Belum ada data Unit Pasif. Silakan import terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $periodes->links() }}
        </div>

        <div class="mt-8">
            <a href="{{ route('import.pasif.index') }}"
               class="inline-flex items-center gap-2 text-gray-600 hover:text-blue-600 font-medium">
                ← Kembali
            </a>
        </div>

    </div>
</div>

<script>
function updateNoPs(btn) {
    const wrapper = btn.closest('div');
    const input = wrapper.querySelector('.no-ps-input');
    const id = input.dataset.id;
    const noPs = input.value.trim();

    btn.disabled = true;
    btn.textContent = '...';

    fetch(`/import/pasif/${id}/no-ps`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ no_ps: noPs })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            btn.textContent = '✓';
            btn.classList.remove('bg-blue-100', 'text-blue-700');
            btn.classList.add('bg-green-100', 'text-green-700');
            setTimeout(() => {
                btn.textContent = 'Simpan';
                btn.classList.remove('bg-green-100', 'text-green-700');
                btn.classList.add('bg-blue-100', 'text-blue-700');
                btn.disabled = false;
            }, 1500);
        } else {
            alert('Gagal menyimpan');
            btn.textContent = 'Simpan';
            btn.disabled = false;
        }
    })
    .catch(err => {
        console.error(err);
        alert('Terjadi kesalahan');
        btn.textContent = 'Simpan';
        btn.disabled = false;
    });
}
</script>
</body>
</html>