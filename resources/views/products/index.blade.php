@extends('layouts.app')

@section('title', 'Master Produk')

@section('content')

<div class="bg-white rounded-2xl shadow p-6">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between mb-6">
        <h3 class="text-xl font-semibold">Master Product</h3>
        <a href="{{ route('products.create') }}" 
           class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
            + Tambah Product
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 text-sm">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-left">Label / SKU</th>
                    <th class="p-3 text-left">Nama Produk</th>
                    <th class="p-3 text-center">Jenis</th>
                    <th class="p-3 text-center">Satuan</th>
                    <th class="p-3 text-center">Hal</th>
                    <th class="p-3 text-center">Lembar</th>
                    <th class="p-3 text-center">Kertas</th>
                    <th class="p-3 text-right">Berat Satuan</th>
                    <th class="p-3 text-right">Isi</th>
                    <th class="p-3 text-right">Berat Paket</th>
                    <th class="p-3 text-right">Harga Beli</th>
                    <th class="p-3 text-right">Harga Jual</th>
                    <th class="p-3 text-center">Role</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $p)
                <tr class="border-t hover:bg-gray-50">
                    <td class="p-3 font-medium">{{ $p->label ?? $p->sku ?? '-' }}</td>
                    <td class="p-3">{{ $p->name }}</td>
                    <td class="p-3 text-center">{{ $p->jenis ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $p->satuan ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $p->hal ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $p->lembar ?? '-' }}</td>
                    <td class="p-3 text-center">{{ $p->kertas ?? '-' }}</td>
                    <td class="p-3 text-right">
                        {{ $p->berat_satuan ? number_format($p->berat_satuan, 3) . ' kg' : '-' }}
                    </td>
                    <td class="p-3 text-center">{{ $p->isi ?? '-' }}</td>
                    <td class="p-3 text-right font-medium">
                        {{ $p->berat_paket ? number_format($p->berat_paket, 3) . ' kg' : '-' }}
                    </td>
                    <td class="p-3 text-right">
                        Rp {{ $p->harga_beli ? number_format($p->harga_beli, 0, ',', '.') : '-' }}
                    </td>
                    <td class="p-3 text-right">
                        Rp {{ $p->harga_jual ? number_format($p->harga_jual, 0, ',', '.') : '-' }}
                    </td>
                    <td class="p-3 text-center">
                        <span class="px-3 py-1 rounded-full text-xs font-medium
                            {{ $p->role == 'jual' ? 'bg-green-100 text-green-700' : 
                               ($p->role == 'tidak_dijual' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }}">
                            {{ ucfirst($p->role ?? '-') }}
                        </span>
                    </td>
                    <td class="p-3 flex gap-2 justify-center">
                        <a href="{{ route('products.edit', $p->id) }}" 
                           class="bg-yellow-400 hover:bg-yellow-500 px-4 py-1 rounded text-white text-sm">
                            Edit
                        </a>
                        <form action="{{ route('products.destroy', $p->id) }}" method="POST" 
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
                    <td colspan="14" class="p-8 text-center text-gray-500">Belum ada data produk</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection