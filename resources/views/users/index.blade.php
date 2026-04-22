@extends('layouts.app')

@section('content')

<a href="{{ route('users.create') }}" class="bg-blue-500 text-white px-3 py-2 rounded">
    + User
</a>

<table class="w-full mt-4 border">
    <tr>
        <th>Name</th>
        <th>Email</th>
        <th>Aksi</th>
    </tr>

    @foreach($users as $user)
    <tr class="{{ $user->id == Auth::id() ? 'bg-yellow-100' : '' }}">
        <td class="text-center">{{ $user->name }}</td>
        <td class="text-center">{{ $user->email }}</td>
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