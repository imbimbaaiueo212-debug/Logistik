@extends('layouts.panel')

@section('title', 'Detail Unit Pasif ' . $periode->edisi)

@section('content')
@php
    $subtitle = ($periode->periode ?? trim($periode->bulan . ' ' . $periode->tahun))
              . ($periode->no_ps ? ' | No PS: ' . $periode->no_ps : '')
              . ($periode->cetak_ke ? ' | Cetak ke-' . $periode->cetak_ke : '');
    $totalKalender = $periode->pesanan->sum(fn ($r) => $r->total_kalender);
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
    ['Total Kalender', number_format($totalKalender)],
    ['Spare Pasif 3%', number_format($spareQty)],
    ['Grand Total', number_format($totalKalender + $spareQty), true],
]])
@include('kalender::pasif._tabel-unit', ['rows' => $periode->pesanan, 'spareQty' => $spareQty, 'cetakKe' => $periode->cetak_ke])
@endsection
