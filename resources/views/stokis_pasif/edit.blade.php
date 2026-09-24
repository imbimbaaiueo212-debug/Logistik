@extends('layouts.panel')

@section('title', 'Edit Stokis Pasif')

@section('content')
    <div class="min-w-0">
        <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Edit Stokis Pasif</h1>
        <p class="mt-1 text-sm text-navy-950/55">{{ $stokis->no_cab }}</p>
    </div>

    @include('stokis_pasif._form')
@endsection