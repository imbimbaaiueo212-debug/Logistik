@extends('layouts.panel')

@section('title', 'Detail Unit Pasif ' . $periode->edisi)

@section('content')
@php
    $kolom = [
        ['key' => 'no',        'label' => 'No'],
        ['key' => 'no_cab',    'label' => 'Cabang',    'class' => 'font-medium'],
        ['key' => 'nama_unit', 'label' => 'Nama Unit', 'class' => 'font-semibold text-[#162749]'],
        ['key' => 'qty',       'label' => 'Qty',       'align' => 'center', 'type' => 'number', 'class' => 'font-medium text-[#28447F]'],
        ['key' => 'telepon',   'label' => 'Telepon'],
        ['key' => 'alamat',    'label' => 'Alamat',    'clip' => true],
    ];
    $subtitle = ($periode->periode ?? trim($periode->bulan . ' ' . $periode->tahun))
              . ($periode->no_ps ? ' | No PS: ' . $periode->no_ps : '');
@endphp

@include('partials.page-header', [
    'title'     => $periode->edisi,
    'subtitle'  => $subtitle,
    'back'      => route('import.pasif.list'),
    'backLabel' => 'Kembali ke Daftar',
])
@include('partials.flash')
@include('partials.stat-cards', ['stats' => [
    ['Total Unit', $periode->pesanan->count()],
    ['Total Qty', number_format($total), true],
]])
@include('partials.pasif-table', [
    'kolom'  => $kolom,
    'rows'   => $periode->pesanan,
    'kosong' => 'Belum ada unit pada periode ini.',
])
@endsection
