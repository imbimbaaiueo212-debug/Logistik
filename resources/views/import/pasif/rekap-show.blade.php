@extends('layouts.panel')

@section('title', 'Rekap Total ' . $periode->edisi)

@section('content')
@php
    $kolom = [
        ['key' => 'no',          'label' => 'No'],
        ['key' => 'no_cab',      'label' => 'Cabang',      'class' => 'font-medium'],
        ['key' => 'nama_unit',   'label' => 'Nama Unit',   'class' => 'font-semibold text-[#162749]'],
        ['key' => 'bacaan_unit', 'label' => 'Bacaan Unit', 'align' => 'center', 'type' => 'number', 'class' => 'font-medium text-[#28447F]'],
        ['key' => 'qty',         'label' => 'Qty Majalah', 'align' => 'center', 'type' => 'number', 'class' => 'font-medium text-[#E85D2A]'],
        ['key' => 'telepon',     'label' => 'Telepon'],
        ['key' => 'alamat',      'label' => 'Alamat',      'clip' => true],
    ];
    $subtitle = ($periode->periode ?? trim($periode->bulan . ' ' . $periode->tahun))
              . ($periode->no_ps ? ' | No PS: ' . $periode->no_ps : '');
@endphp

@include('partials.page-header', [
    'title'     => $periode->edisi . ' - Rekap Total',
    'subtitle'  => $subtitle,
    'back'      => route('import.pasif.rekap'),
    'backLabel' => 'Kembali ke Rekap Total',
])
@include('partials.stat-cards', ['stats' => [
    ['Total Unit', $periode->pesanan->count()],
    ['Total Bacaan', number_format($totalBacaan)],
    ['Total Qty Majalah', number_format($totalMajalah), true],
]])
@include('partials.pasif-table', [
    'kolom'  => $kolom,
    'rows'   => $periode->pesanan,
    'kosong' => 'Belum ada unit pada periode ini.',
])
@endsection
