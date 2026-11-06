@extends('layouts.panel')

@section('title', 'Rekap Total ' . $periode->edisi)

@section('content')
@php
    $subtitle = ($periode->periode ?? trim($periode->bulan . ' ' . $periode->tahun))
              . ($periode->no_ps ? ' | No PS: ' . $periode->no_ps : '');
@endphp

@include('partials.page-header', [
    'title'     => $periode->edisi . ' - Rekap Total',
    'subtitle'  => $subtitle,
    'back'      => route('kalender.pasif.rekap'),
    'backLabel' => 'Kembali ke Rekap Total',
])
@include('partials.stat-cards', ['stats' => [
    ['Total Unit', $periode->pesanan->count()],
    ['Total Murid', number_format($periode->pesanan->sum('qty'))],
    ['Total Relawan', number_format($totalBacaan)],
    ['Total Kalender', number_format($periode->pesanan->sum(fn ($r) => $r->total_kalender)), true],
]])
@include('kalender::pasif._tabel-unit', ['rows' => $periode->pesanan])
@endsection
