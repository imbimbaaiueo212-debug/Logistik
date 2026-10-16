@extends('layouts.app')

@section('content')

@if (session('success'))
    <div class="bg-green-100 text-green-700 text-sm p-3 rounded-lg mb-4">
        {{ session('success') }}
    </div>
@endif

<a href="{{ route('register') }}" class="bg-blue-500 text-white px-3 py-2 rounded">
    + Tambah User
</a>

<table class="w-full mt-4 border">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Role</th>
        <th>Aksi</th>
    </tr>

    @foreach($users as $user)
    <tr class="{{ $user->id == Auth::id() ? 'bg-yellow-100' : '' }}">
        <td class="text-center">{{ $user->name }}</td>
        <td class="text-center">{{ $user->email }}</td>
        <td class="text-center">
            <span class="px-2 py-1 rounded-full text-xs
                {{ $user->role == 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
            </span>
        </td>
        <td class="text-center space-x-2">

    <!-- EDIT -->
    <a href="{{ route('users.edit', $user->id) }}" 
       class="inline-block bg-yellow-500 hover:bg-yellow-600 text-white text-xs px-3 py-1 rounded-lg">
        ✏️ Edit
    </a>

    <!-- RESET PASSWORD -->
    <a href="{{ route('users.reset.form', $user->id) }}" 
       class="inline-block bg-blue-500 hover:bg-blue-600 text-white text-xs px-3 py-1 rounded-lg">
        🔑 Reset
    </a>

    <!-- DELETE -->
    <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button 
            onclick="return confirm('Yakin hapus user ini?')" 
            class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1 rounded-lg">
            🗑️ Hapus
        </button>
    </form>

</td>
    </tr>
    @endforeach
</table>

@endsection