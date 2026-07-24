@extends('layouts.app')

@section('title', 'Master Produk')

@section('content')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<div class="bg-white rounded-2xl shadow p-6">
    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <div class="flex justify-between mb-6 items-center">
        <h3 class="text-xl font-semibold">Master Product</h3>
        <div class="flex gap-2">
            <a href="{{ route('products.export') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Export</a>
            <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
                @csrf
                <input type="file" name="file" class="border p-1 rounded">
                <button class="bg-blue-500 text-white px-3 rounded">Import</button>
            </form>
            <a href="{{ route('products.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">+ Tambah</a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-5 items-end">

    <div>
        <label class="block text-sm font-medium mb-1">Jenis</label>
        <select name="jenis" id="filterJenis" class="border rounded px-3 py-2">
            <option value="">Semua Jenis</option>
            @foreach($jenisList as $jenis)
                <option value="{{ $jenis }}"
                    {{ request('jenis') == $jenis ? 'selected' : '' }}>
                    {{ $jenis }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Kategori</label>
        <select name="kategori" id="filterKategori" class="border rounded px-3 py-2">
            <option value="">Semua Kategori</option>
            @foreach($kategoriList as $kategori)
                <option value="{{ $kategori }}"
                    {{ request('kategori') == $kategori ? 'selected' : '' }}>
                    {{ $kategori }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Sub Kategori</label>
        <select name="sub_kategori" id="filterSubKategori" class="border rounded px-3 py-2">
            <option value="">Semua Sub Kategori</option>
            @foreach($subKategoriList as $sub)
                <option value="{{ $sub }}"
                    {{ request('sub_kategori') == $sub ? 'selected' : '' }}>
                    {{ $sub }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">Tampilkan</label>
        <select name="per_page" id="filterPerPage" class="border rounded px-3 py-2">
            @foreach([10,20,50,100,250,500,1000,5000] as $n)
                <option value="{{ $n }}"
                    {{ $perPage == $n ? 'selected' : '' }}>
                    {{ $n }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit"
            class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Filter
    </button>

    <a href="{{ route('products.index') }}"
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
        Reset
    </a>

    <button type="button"
            id="btnEditMulti"
            onclick="showBulkEditModal()"
            class="hidden bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded font-medium">
        ✏️ Edit Multi (<span id="selectedCount">0</span>)
    </button>

</form>

    <div class="overflow-x-auto overflow-y-auto max-h-[calc(100vh-280px)]">  <!-- sesuaikan angkanya -->
    <table class="w-full border border-gray-200 text-sm">
            <thead>
                <tr>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">
                        <input type="checkbox" id="checkAll" class="w-4 h-4 cursor-pointer">
                    </th>

                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">No</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-left">Jenis</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-left">Kategori</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-left">Sub Kategori</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">Label</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-left">Nama Produk</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">Satuan</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-right">Berat</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-right">Berat Paket</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-right">Harga Beli</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-right">Harga Jual</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">Status</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">Isi</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">Role</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">Tgl Rilis</th>
                    <th class="sticky top-0 z-30 bg-gray-100 p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-3 text-center">
                        <input type="checkbox" 
                               class="product-checkbox w-4 h-4 cursor-pointer"
                               value="{{ $p->id }}"
                               data-kode="{{ $p->kode ?? '' }}"
                               data-kategori="{{ $p->kategori ?? '' }}"
                               data-sub-kategori="{{ $p->sub_kategori ?? '' }}"
                               data-name="{{ $p->name ?? '' }}"
                               data-jenis="{{ $p->jenis ?? '' }}"
                               data-satuan="{{ $p->satuan ?? '' }}"
                               data-harga-beli="{{ $p->harga_beli ?? '' }}"
                               data-harga-jual="{{ $p->harga_jual ?? '' }}"
                               data-role="{{ $p->role ?? '' }}"
                               data-status="{{ $p->status ?? '' }}"
                               data-tanggal-rilis="{{ $p->tanggal_rilis ?? '' }}">
                    </td>
                    <td class="text-center">{{ $products->firstItem() + $loop->index }}</td>
                    <td>{{ $p->jenis }}</td>
                    <td>{{ $p->kategori }}</td>
                    <td>{{ $p->sub_kategori }}</td>
                    <td class="text-center">{{ $p->label ?? '-' }}</td>
                    <td>{{ $p->name }}</td>
                    <td class="text-center">{{ $p->satuan }}</td>
                    <td class="text-right">{{ $p->berat_satuan ? number_format($p->berat_satuan,3,',','.') . ' Kg' : '-' }}</td>
                    <td class="text-right">{{ $p->berat_paket ? number_format($p->berat_paket,3,',','.') . ' Kg' : '-' }}</td>
                    <td class="text-right">Rp {{ $p->harga_beli ? number_format($p->harga_beli) : '-' }}</td>
                    <td class="text-right">Rp {{ $p->harga_jual ? number_format($p->harga_jual) : '-' }}</td>
                    <td class="text-center">{{ $p->status }}</td>
                    <td class="text-center">{{ $p->isi }}</td>
                    <td class="text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-medium 
                            {{ $p->role == 'jual' ? 'bg-green-100 text-green-700' : 
                               ($p->role == 'tidak_dijual' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ ucfirst($p->role ?? '') }}
                        </span>
                    </td>
                    <td class="text-center">{{ $p->tanggal_rilis ? \Carbon\Carbon::parse($p->tanggal_rilis)->format('d/m/Y') : '-' }}</td>
                    <td class="p-3 flex gap-2 justify-center">
                        
                        <form action="{{ route('products.destroy', $p->id) }}" method="POST" onsubmit="return confirm('Yakin hapus?')">
                            @csrf @method('DELETE')
                            <button class="bg-red-500 hover:bg-red-600 px-4 py-1 rounded text-white text-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="18" class="text-center p-8 text-gray-500">Belum ada data</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 flex justify-between items-center">
        <div class="text-sm text-gray-600">
            Menampilkan <b>{{ $products->firstItem() }}</b> - <b>{{ $products->lastItem() }}</b> dari <b>{{ number_format($products->total()) }}</b> data
        </div>
        {{ $products->links() }}
    </div>
</div>

<!-- Modal Bulk Edit -->
<div id="bulkEditModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl mx-4 my-8">
        <div class="p-6 border-b">
            <h2 class="text-2xl font-semibold">Edit Multi Produk</h2>
            <p class="text-gray-600">Jumlah dipilih: <strong id="modalCount">0</strong></p>
        </div>

        <form action="{{ route('products.bulk-update') }}" method="POST" id="bulkForm">
            @csrf
            <div id="selectedInputs"></div>

            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium mb-1">Kode</label>
                    <input type="text" name="kode" id="input-kode" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Kategori</label>
                    <input type="text" name="kategori" id="input-kategori" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Sub Kategori</label>
                    <input type="text" name="sub_kategori" id="input-sub_kategori" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Nama Produk</label>
                    <input type="text" name="name" id="input-name" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Jenis</label>
                    <input type="text" name="jenis" id="input-jenis" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Satuan</label>
                    <input type="text" name="satuan" id="input-satuan" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Harga Beli (Rp)</label>
                    <input type="number" name="harga_beli" id="input-harga_beli" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Harga Jual (Rp)</label>
                    <input type="number" name="harga_jual" id="input-harga_jual" step="0.01" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Role</label>
                    <select name="role" id="input-role" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                        <option value="">-- Tidak Diubah --</option>
                        <option value="jual">Jual</option>
                        <option value="tidak_dijual">Tidak Dijual</option>
                        <option value="stock">Stock</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Status</label>
                    <input type="text" name="status" id="input-status" class="w-full border border-gray-300 rounded-lg px-4 py-3" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Tanggal Rilis</label>
                    <input type="date" name="tanggal_rilis" id="input-tanggal_rilis" class="w-full border border-gray-300 rounded-lg px-4 py-3">
                    <small class="text-gray-500">Kosongkan jika tidak diubah</small>
                </div>
            </div>

            <div class="p-6 border-t flex gap-4">
                <button type="button" onclick="closeBulkModal()" class="flex-1 bg-gray-500 text-white py-3 rounded-xl">Batal</button>
                <button type="submit" onclick="return confirm('Simpan perubahan untuk produk terpilih?')" class="flex-1 bg-yellow-600 text-white py-3 rounded-xl font-medium">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

 <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {

    // Select2 untuk filter
    $('#filterJenis').select2({
        placeholder: 'Cari Jenis...',
        allowClear: true,
        width: '200px'
    });

    $('#filterKategori').select2({
        placeholder: 'Cari Kategori...',
        allowClear: true,
        width: '200px'
    });

    $('#filterSubKategori').select2({
        placeholder: 'Cari Sub Kategori...',
        allowClear: true,
        width: '200px'
    });

    $('#filterPerPage').select2({
        minimumResultsForSearch: Infinity,
        width: '120px'
    });

});

//INI UNTUK AUTO FILTER
$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | AUTO FILTER
    |--------------------------------------------------------------------------
    */

    $('#filterJenis').on('change', function () {

        const jenis = $(this).val();

        // Reset kategori & sub kategori
        $('#filterKategori').val(null).trigger('change');
        $('#filterSubKategori').val(null).trigger('change');

        // Submit otomatis
        $(this).closest('form').submit();
    });


    $('#filterKategori').on('change', function () {

        const kategori = $(this).val();

        // Reset sub kategori
        $('#filterSubKategori').val(null).trigger('change');

        // Submit otomatis
        $(this).closest('form').submit();
    });


    $('#filterSubKategori').on('change', function () {

        $(this).closest('form').submit();

    });


    /*
    |--------------------------------------------------------------------------
    | PER PAGE AUTO
    |--------------------------------------------------------------------------
    */

    $('#filterPerPage').on('change', function () {

        $(this).closest('form').submit();

    });

});

document.addEventListener('DOMContentLoaded', function() {
    const checkAll = document.getElementById('checkAll');
    const checkboxes = document.querySelectorAll('.product-checkbox');
    const btnEditMulti = document.getElementById('btnEditMulti');

    function updateButton() {
        const selectedCount = document.querySelectorAll('.product-checkbox:checked').length;
        btnEditMulti.classList.toggle('hidden', selectedCount === 0);
        document.getElementById('selectedCount').textContent = selectedCount;
    }

    checkAll.addEventListener('change', () => {
        checkboxes.forEach(cb => cb.checked = checkAll.checked);
        updateButton();
    });

    checkboxes.forEach(cb => cb.addEventListener('change', updateButton));
});

function getSameValue(selected, attr) {
    const values = Array.from(selected).map(cb => cb.dataset[attr] || '');
    const first = values[0];
    return values.every(v => v === first) ? first : '';
}

function showBulkEditModal() {
    const selected = document.querySelectorAll('.product-checkbox:checked');
    if (selected.length === 0) return;

    document.getElementById('bulkForm').reset();

    const container = document.getElementById('selectedInputs');
    container.innerHTML = '';

    selected.forEach(cb => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'selected_products[]';
        input.value = cb.value;
        container.appendChild(input);
    });

    // Isi data lama
    document.getElementById('input-kode').value = getSameValue(selected, 'kode');
    document.getElementById('input-kategori').value = getSameValue(selected, 'kategori');
    document.getElementById('input-sub_kategori').value = getSameValue(selected, 'subKategori');
    document.getElementById('input-name').value = getSameValue(selected, 'name');
    document.getElementById('input-jenis').value = getSameValue(selected, 'jenis');
    document.getElementById('input-satuan').value = getSameValue(selected, 'satuan');
    document.getElementById('input-harga_beli').value = getSameValue(selected, 'hargaBeli');
    document.getElementById('input-harga_jual').value = getSameValue(selected, 'hargaJual');
    document.getElementById('input-status').value = getSameValue(selected, 'status');
    document.getElementById('input-tanggal_rilis').value = getSameValue(selected, 'tanggalRilis');

    const roleValue = getSameValue(selected, 'role');
    document.getElementById('input-role').value = roleValue || '';

    document.getElementById('modalCount').textContent = selected.length;
    document.getElementById('bulkEditModal').classList.remove('hidden');
}

function closeBulkModal() {
    document.getElementById('bulkEditModal').classList.add('hidden');
}
</script>