@extends('layouts.panel')

@section('title', 'Tambah Stokis Pasif')

@section('content')
    <div class="min-w-0">
        <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Tambah Stokis Pasif</h1>
        <p class="mt-1 text-sm text-navy-950/55">Data akan langsung masuk ke daftar Stokis Pasif</p>
    </div>

    @include('stokis_pasif._form')
@endsection