@extends('layouts.panel')

@section('title', 'Edit Pasif Manual')

@section('content')
@include('partials.page-header', [
    'title'    => 'Edit Pasif Manual',
    'subtitle' => 'Ubah data periode & unit pasif manual',
])
@include('partials.flash')
@include('partials.pasif-manual-form', [
    'periode' => $periode,
    'action'  => route('import.pasif.manual.update', $periode->id),
    'cancel'  => route('import.pasif.manual.show', $periode->id),
    'submit'  => 'Simpan Perubahan',
])
@endsection
