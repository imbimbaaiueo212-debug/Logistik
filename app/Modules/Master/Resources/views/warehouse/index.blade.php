@extends('layouts.app')

@section('title', 'Gudang')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Data Gudang</h2>

        <a href="{{ route('warehouses.create') }}" 
           class="bg-green-500 text-white px-4 py-2 rounded">
            + Tambah Gudang
        </a>
    </div>

    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">ID</th>
                <th class="p-2">Nama</th>
                <th class="p-2">Kode</th>
                <th class="p-2">Lokasi</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($warehouses as $w)
            <tr class="border-t">
                <td class="p-2 text-center">#{{ $w->id }}</td>
                <td class="p-2">{{ $w->name }}</td>
                <td class="p-2 text-center">{{ $w->code }}</td>
                <td class="p-2">{{ $w->location }}</td>
                <td class="p-2 text-center">
                    <div class="flex justify-center gap-2">

                        <a href="{{ route('warehouses.edit', $w->id) }}"
                           class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
                            Edit
                        </a>

                        <form action="{{ route('warehouses.destroy', $w->id) }}" method="POST"
                              onsubmit="return confirm('Yakin hapus?')">
                            @csrf
                            @method('DELETE')

                            <button class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded">
                                Hapus
                            </button>
                        </form>

                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center p-4 text-gray-500">
                    Belum ada data gudang
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>

@endsection