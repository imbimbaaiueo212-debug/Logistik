@extends('layouts.panel')

@section('title', 'Rekap Total')

@section('content')
@php
    $kolom = [
        ['key' => 'edisi',   'label' => 'Edisi',   'class' => 'font-semibold text-[#162749]'],
        ['key' => 'periode', 'label' => 'Periode', 'value' => function ($p) { return $p->periode ?? trim($p->bulan . ' ' . $p->tahun); }],
        ['key' => 'no_ps',   'label' => 'No PS',   'type' => 'input'],
        ['key' => 'pesanan_count',           'label' => 'Jumlah Unit',  'align' => 'center', 'type' => 'number'],
        ['key' => 'pesanan_sum_bacaan_unit', 'label' => 'Total Bacaan', 'align' => 'center', 'type' => 'number', 'class' => 'font-medium text-[#28447F]'],
        ['key' => 'pesanan_sum_qty',         'label' => 'Total Qty',    'align' => 'center', 'type' => 'number', 'class' => 'font-semibold text-[#E85D2A]'],
        ['key' => 'status',  'label' => 'Status',  'type' => 'badge'],
    ];
@endphp

@include('partials.page-header', [
    'title'     => 'Rekap Total',
    'subtitle'  => 'Hasil import lengkap: Bacaan Unit + Qty Majalah',
    'back'      => route('import.pasif.index'),
    'backLabel' => 'Kembali ke Menu Unit Pasif',
    'primary'   => ['url' => route('import.pasif.create'), 'label' => 'Import Data'],
])
@include('partials.flash')
@include('partials.pasif-table', [
    'kolom'       => $kolom,
    'rows'        => $periodes,
    'detailRoute' => 'import.pasif.rekap.show',
    'kosong'      => 'Belum ada data. Silakan import terlebih dahulu.',
])
@endsection
