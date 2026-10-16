@extends('layouts.panel')

@section('title', 'Bacaan Unit')

@section('content')
@php
    $kolom = [
        ['key' => 'edisi',   'label' => 'Edisi',   'class' => 'font-semibold text-[#162749]'],
        ['key' => 'periode', 'label' => 'Periode', 'value' => function ($p) { return $p->periode ?? trim($p->bulan . ' ' . $p->tahun); }],
        ['key' => 'no_ps',   'label' => 'No PS',   'type' => 'input'],
        ['key' => 'pesanan_count',           'label' => 'Jumlah Unit',  'align' => 'center', 'type' => 'number'],
        ['key' => 'pesanan_sum_bacaan_unit', 'label' => 'Total Bacaan', 'align' => 'center', 'type' => 'number', 'class' => 'font-semibold text-[#E85D2A]'],
        ['key' => 'status',  'label' => 'Status',  'type' => 'badge'],
    ];
@endphp

@include('partials.page-header', [
    'title'    => 'Bacaan Unit',
    'subtitle' => 'Data Bacaan Unit dari pemesanan majalah Pasif',
    'back'     => route('import.pasif.index'),
])
@include('partials.flash')
@include('partials.pasif-table', [
    'kolom'       => $kolom,
    'rows'        => $periodes,
    'detailRoute' => 'import.pasif.bacaan.show',
    'kosong'      => 'Belum ada data Bacaan Unit. Silakan import data Unit Pasif terlebih dahulu.',
])
@endsection
