@extends('layouts.app')

@section('title', 'Stok Opname')

@section('content')
<div class="p-6 space-y-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Stock Opname</h1>

        <a href="{{ route('stock-opname.create') }}"
            class="bg-blue-600 text-white px-4 py-2 rounded">
            + Buat Opname
        </a>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search"
            placeholder="Cari kode..."
            value="{{ request('search') }}"
            class="border px-3 py-2 rounded w-64">

        <select name="status" class="border px-3 py-2 rounded">
            <option value="">Semua Status</option>
            <option value="draft">Draft</option>
            <option value="submitted">Submitted</option>
            <option value="approved">Approved</option>
        </select>

        <button class="bg-gray-800 text-white px-4 py-2 rounded">
            Filter
        </button>
    </form>

    {{-- LIST CARD --}}
    <div class="grid gap-4">

        @forelse($data as $so)
        <a href="{{ route('stock-opname.show', $so->id) }}"
            class="block bg-white p-4 rounded-xl shadow hover:shadow-md transition">

            <div class="flex justify-between items-center">

                {{-- LEFT --}}
                <div>
                    <h2 class="font-semibold text-lg">{{ $so->code }}</h2>
                    <p class="text-sm text-gray-500">
                        Gudang: {{ $so->warehouse->name }}
                    </p>
                </div>

                {{-- RIGHT --}}
                <div class="text-right">

                    {{-- STATUS BADGE --}}
                    @if($so->status == 'draft')
                        <span class="bg-gray-200 text-gray-800 px-3 py-1 rounded text-xs">
                            Draft
                        </span>
                    @elseif($so->status == 'submitted')
                        <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded text-xs">
                            Submitted
                        </span>
                    @elseif($so->status == 'approved')
                        <span class="bg-green-200 text-green-800 px-3 py-1 rounded text-xs">
                            Approved
                        </span>
                    @endif

                    <p class="text-xs text-gray-400 mt-1">
                        {{ $so->created_at->format('d M Y H:i') }}
                    </p>
                </div>

            </div>

        </a>
        @empty
        <div class="text-center text-gray-500">
            Tidak ada data
        </div>
        @endforelse

    </div>

    {{-- PAGINATION --}}
    <div>
        {{ $data->links() }}
    </div>

</div>
@endsection