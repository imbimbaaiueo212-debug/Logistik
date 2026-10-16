@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded shadow max-w-xl">

    <h2 class="text-xl font-bold mb-4">Buat Stock Opname</h2>

    <form action="{{ route('stock-opname.store') }}" method="POST">
        @csrf

        {{-- PILIH GUDANG --}}
        <div class="mb-4">
            <label class="block mb-1">Pilih Gudang</label>
            <select name="warehouse_id" class="w-full border px-3 py-2 rounded">
                <option value="">-- Pilih Gudang --</option>
                @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}">
                        {{ $wh->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- SUBMIT --}}
        <button class="bg-blue-600 text-white px-4 py-2 rounded">
            Buat Opname
        </button>

    </form>

</div>
@endsection