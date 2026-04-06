@extends('layouts.app')

@section('title', 'Data Supplier')

@section('content')

<div class="bg-white rounded-2xl shadow p-6">

    @if(session('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h3 class="text-xl font-semibold">Data Supplier</h3>
        <a href="{{ route('suppliers.create') }}" 
           class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
            + Tambah Supplier
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border border-gray-200 rounded-lg">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3 text-center">Nama</th>
                    <th calss="p-3 text-center">SKU</th>
                    <th class="p-3 text-center">Email</th>
                    <th class="p-3 text-center">Phone</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse($suppliers as $s)
                <tr class="border-t">
                    <td class="p-3 text-center">{{ $s->nama_supplier }}</td>
                    <td class="p-3 text-center">{{ $s->kode_supplier }}</td>
                    <td class="p-3 text-center">{{ $s->email }}</td>
                    <td class="p-3 text-center">{{ $s->phone }}</td>
                    <td class="p-3 text-center flex justify-center gap-2">

                        <a href="{{ route('suppliers.edit', $s->id) }}" 
                           class="bg-yellow-400 hover:bg-yellow-500 px-3 py-1 rounded text-white">
                            Edit
                        </a>

                        <form action="{{ route('suppliers.destroy', $s->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Yakin hapus?')" 
                                    class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-white">
                                Hapus
                            </button>
                        </form>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="p-4 text-center text-gray-500">
                        Belum ada data supplier
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection