@extends('layouts.panel')

@section('title', 'Create Manual Pasif')

@section('content')
@include('partials.page-header', [
    'title'    => 'Create Manual - Pesanan Kalender Pasif',
    'subtitle' => 'Input manual data unit pasif (tanpa import Excel)',
])
@include('partials.flash')
@include('partials.pasif-manual-form', [
    'action' => route('kalender.pasif.manual.store'),
    'cancel' => route('kalender.pasif.manual.index'),
    'submit' => 'Simpan Data Manual',
])
@endsection
