@extends('layouts.panel')

@section('title', 'Detail Unit Pasif ' . $periode->edisi)

@section('content')
@php
    $subtitle = ($periode->periode ?? trim($periode->bulan . ' ' . $periode->tahun))
              . ($periode->no_ps ? ' | No PS: ' . $periode->no_ps : '');
@endphp

@include('partials.page-header', [
    'title'     => $periode->edisi,
    'subtitle'  => $subtitle,
    'back'      => route('kalender.pasif.list'),
    'backLabel' => 'Kembali ke Daftar',
])
@include('partials.flash')
@include('partials.stat-cards', ['stats' => [
    ['Total Unit', $periode->pesanan->count()],
    ['Total Kalender', number_format($periode->pesanan->sum(fn ($r) => $r->total_kalender)), true],
]])
@include('kalender::pasif._tabel-unit', ['rows' => $periode->pesanan])
@endsection
