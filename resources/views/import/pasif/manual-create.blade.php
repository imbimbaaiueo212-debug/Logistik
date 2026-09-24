@extends('layouts.panel')

@section('title', 'Create Manual Pasif')

@section('content')
@include('partials.page-header', [
    'title'    => 'Create Manual - Pesanan Majalah Pasif',
    'subtitle' => 'Input manual data unit pasif (tanpa import Excel)',
])
@include('partials.flash')
@include('partials.pasif-manual-form', [
    'action' => route('import.pasif.manual.store'),
    'cancel' => route('import.pasif.manual.index'),
    'submit' => 'Simpan Data Manual',
])
@endsection
