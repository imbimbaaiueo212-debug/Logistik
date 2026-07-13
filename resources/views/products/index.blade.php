@extends('layouts.app')

@section('title', 'Master Produk')

@section('content')

<div class="bg-white rounded-2xl shadow p-6">

@if(session('success'))
    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
        <ul>
            @foreach ($errors->all() as $error)
                <li>- {{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif


<div class="flex justify-between mb-6 items-center">
    <h3 class="text-xl font-semibold">Master Product</h3>

    <div class="flex gap-2">

        <a href="{{ route('products.export') }}"
           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">
            Export
        </a>

        <form action="{{ route('products.import') }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
            @csrf
            <input type="file" name="file" class="border p-1 rounded">
            <button class="bg-blue-500 text-white px-3 rounded">
                Import
            </button>
        </form>

        <a href="{{ route('products.create') }}"
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
            + Tambah
        </a>

    </div>
</div>

<form method="GET" class="flex flex-wrap gap-3 mb-5 items-end">

    <div>
        <label class="block text-sm font-medium mb-1">
            Jenis
        </label>

        <select name="jenis"
                class="border rounded px-3 py-2">

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
        <label class="block text-sm font-medium mb-1">
            Kategori
        </label>

        <select name="kategori"
                class="border rounded px-3 py-2">

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
        <label class="block text-sm font-medium mb-1">
            Sub Kategori
        </label>

        <select name="sub_kategori"
                class="border rounded px-3 py-2">

            <option value="">Semua Sub Kategori</option>

            @foreach($su as $kategori)
                <option value="{{ $kategori }}"
                    {{ request('kategori') == $kategori ? 'selected' : '' }}>
                    {{ $kategori }}
                </option>
            @endforeach

        </select>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">
            Tampilkan
        </label>

        <select name="per_page"
                class="border rounded px-3 py-2">

            @foreach([10,20,50,100,250,500,1000,5000] as $n)

                <option value="{{ $n }}"
                    {{ $perPage == $n ? 'selected' : '' }}>
                    {{ $n }}
                </option>

            @endforeach

        </select>
    </div>

    <button class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
        Filter
    </button>

    <a href="{{ route('products.index') }}"
       class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
        Reset
    </a>

</form>

<div class="flex justify-between items-center mb-4">

    <div class="text-gray-600">
    Total Produk :
    <strong>{{ $products->total() }}</strong>
</div>

    <form method="GET">

        <label class="mr-2 font-medium">
            Tampilkan
        </label>

        <select name="per_page"
                onchange="this.form.submit()"
                class="border rounded px-3 py-2">

            @foreach([10,20,50,100,250,500,1000,5000] as $n)
                <option value="{{ $n }}"
                    {{ $perPage == $n ? 'selected' : '' }}>
                    {{ number_format($n) }}
                </option>
            @endforeach

        </select>

        <span>Data</span>

    </form>

</div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 text-sm">
    <thead class="bg-gray-100">
        <tr>
            <th class="p-3 text-center">No</th>

            <th class="p-3 text-left">Jenis</th>
            <th class="p-3 text-left">Kategori</th>
            <th class="p-3 text-left">Sub Kategori</th>
            <th class="p-3 text-center">Label</th>
            <th class="p-3 text-left">Nama Produk</th>
            <th class="p-3 text-center">Satuan</th>

            <th class="p-3 text-right">Berat</th>
            <th class="p-3 text-right">Berat Paket</th>

            <th class="p-3 text-right">Harga Beli</th>
            <th class="p-3 text-right">Harga Jual</th>
            <th class="p-3 text-right">Harga Jual Penyesuaian</th>

            <th class="p-3 text-center">Status</th>
            <th class="p-3 text-center">Isi</th>
            <th class="p-3 text-center">Role</th>
            <th class="p-3 text-center">Tgl Rilis</th>

            <th class="p-3 text-center">Aksi</th>
        </tr>
    </thead>

    <tbody>

    @forelse($products as $p)

    <tr class="border-t hover:bg-gray-50">

        <td class="text-center">
            {{ $products->firstItem() + $loop->index }}
        </td>

        <td>{{ $p->jenis }}</td>

        <td>{{ $p->kategori }}</td>

        <td>{{ $p->sub_kategori }}</td>

        <td class="text-center">
            {{ $p->label }}
        </td>

        <td>
            {{ $p->name }}
        </td>

        <td class="text-center">
            {{ $p->satuan }}
        </td>

        <td class="text-right">
            {{ $p->berat_satuan !== null ? number_format($p->berat_satuan,3,',','.') . ' Kg' : '-' }}
        </td>

        <td class="text-right">
            {{ $p->berat_paket !== null ? number_format($p->berat_paket,3,',','.') . ' Kg' : '-' }}
        </td>

        <td class="text-right">
            Rp {{ $p->harga_beli ? number_format($p->harga_beli,0,',','.') : '-' }}
        </td>

        <td class="text-right">
            Rp {{ $p->harga_jual ? number_format($p->harga_jual,0,',','.') : '-' }}
        </td>

        <td class="text-right">
            Rp {{ $p->harga_jual_penyesuaian ? number_format($p->harga_jual_penyesuaian,0,',','.') : '-' }}
        </td>

        <td class="text-center">
            {{ $p->status }}
        </td>

        <td class="text-center">
            {{ $p->isi }}
        </td>

        <td class="text-center">

            <span class="px-3 py-1 rounded-full text-xs font-medium
                {{ $p->role == 'jual'
                    ? 'bg-green-100 text-green-700'
                    : ($p->role == 'tidak_dijual'
                        ? 'bg-red-100 text-red-700'
                        : 'bg-blue-100 text-blue-700') }}">

                {{ ucfirst($p->role) }}

            </span>

        </td>

        <td class="text-center">
            {{ $p->tanggal_rilis
                ? \Carbon\Carbon::parse($p->tanggal_rilis)->format('d/m/Y')
                : '-' }}
        </td>

        <td class="p-3 flex gap-2 justify-center">

            <a href="{{ route('products.edit',$p->id) }}"
               class="bg-yellow-400 hover:bg-yellow-500 px-4 py-1 rounded text-white text-sm">
                Edit
            </a>

            <form action="{{ route('products.destroy',$p->id) }}"
                  method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus?')">

                @csrf
                @method('DELETE')

                <button class="bg-red-500 hover:bg-red-600 px-4 py-1 rounded text-white text-sm">
                    Hapus
                </button>

            </form>

        </td>

    </tr>

    @empty

    <tr>

        <td colspan="17"
            class="text-center p-8 text-gray-500">

            Belum ada data produk

        </td>

    </tr>

    @endforelse

    </tbody>

</table>
    </div>
<div class="mt-5 flex justify-between items-center">

    <div class="text-sm text-gray-600">

        Menampilkan

        <b>{{ $products->firstItem() }}</b>

        -

        <b>{{ $products->lastItem() }}</b>

        dari

        <b>{{ number_format($products->total()) }}</b>

        data

    </div>

    {{ $products->links() }}

</div>
</div>

@endsection