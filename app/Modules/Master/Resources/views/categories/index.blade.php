@extends('layouts.app')

@section('content')

<div class="max-w-3xl mx-auto bg-white p-6 rounded-xl shadow">
    <div class="flex justify-between mb-4">
        <h2 class="text-xl font-bold">Data Kategori</h2>
        <a href="{{ route('categories.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
            + Tambah
        </a>
    </div>

    <table class="w-full border">
        <thead>
            <tr class="bg-gray-100">
                <th class="p-2">No</th>
                <th class="p-2">Nama</th>
                <th class="p-2">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($categories as $i => $cat)
            <tr>
                <td class="p-2">{{ $i+1 }}</td>
                <td class="p-2">{{ $cat->nama }}</td>
                <td class="p-2 flex gap-2">
                    <a href="{{ route('categories.edit', $cat->id) }}" class="text-yellow-500">Edit</a>

                    <form action="{{ route('categories.destroy', $cat->id) }}" method="POST">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('Hapus?')" class="text-red-500">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

</div>

@endsection