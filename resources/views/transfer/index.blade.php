@extends('layouts.app')

@section('title', 'Transfer Gudang')

@section('content')

<div class="bg-white p-6 rounded-2xl shadow">

<div class="flex justify-between mb-4">
    <h2 class="text-xl font-bold">Transfer Gudang</h2>

    <a href="{{ route('transfer.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded">
        + Transfer
    </a>
</div>

<table class="w-full border">
<thead>
<tr>
<th>ID</th>
<th>Dari</th>
<th>Ke</th>
<th>Tanggal</th>
<th>Status</th>
<th>Aksi</th>
</tr>
</thead>

<tbody>
@foreach($transfers as $t)
<tr class="border-t text-center">
<td>#{{ $t->id }}</td>
<td>{{ $t->fromWarehouse->name }}</td>
<td>{{ $t->toWarehouse->name }}</td>
<td>{{ $t->date }}</td>

<td>
    @if($t->status == 'pending')
        <span class="bg-yellow-200 text-yellow-800 px-2 py-1 rounded">
            Pending
        </span>
    @elseif($t->status == 'approved')
        <span class="bg-green-200 text-green-800 px-2 py-1 rounded">
            Approved
        </span>
    @else
        <span class="bg-red-200 text-red-800 px-2 py-1 rounded">
            Rejected
        </span>
    @endif
</td>

<td>
    <a href="{{ route('transfer.show', $t->id) }}" class="bg-blue-500 text-white px-2 py-1 rounded">Detail</a>

    @if($t->status == 'pending')
        <form action="{{ route('transfer.approve', $t->id) }}" method="POST" class="inline">
            @csrf
            <button class="bg-green-600 text-white px-2 py-1 rounded">
                Approve
            </button>
        </form>
    @endif

    @if($t->status == 'pending')
    <a href="{{ route('transfer.edit', $t->id) }}" class="bg-yellow-500 text-white px-2 py-1 rounded">Edit</a>
@endif

    <form action="{{ route('transfer.destroy', $t->id) }}" method="POST" class="inline">
        @csrf
        @method('DELETE')
        <button onclick="return confirm('Hapus?')" class="bg-red-500 text-white px-2 py-1 rounded">
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