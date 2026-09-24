@extends('layouts.panel')

@section('title', 'Pasif Manual')

@section('content')
@php
    $kolom = [
        ['key' => 'no',      'label' => 'No'],
        ['key' => 'edisi',   'label' => 'Edisi',   'class' => 'font-semibold text-[#162749]'],
        ['key' => 'judul',   'label' => 'Judul',   'clip' => true],
        ['key' => 'periode', 'label' => 'Periode'],
        ['key' => 'bulan_tahun', 'label' => 'Bulan / Tahun',
            'value' => function ($p) { return trim(($p->bulan ?? '') . ' ' . ($p->tahun ?? '')); }],
        ['key' => 'transaksis_count', 'label' => 'Total Unit', 'align' => 'center', 'type' => 'pill', 'suffix' => ' unit',
            'value' => function ($p) { return $p->transaksis_count ?? 0; }],
        ['key' => 'transaksis_sum_jumlah', 'label' => 'Total Qty', 'align' => 'center', 'type' => 'number', 'class' => 'font-semibold'],
        ['key' => 'no_ps',   'label' => 'No PS'],
        ['key' => 'status',  'label' => 'Status', 'type' => 'badge'],
    ];
@endphp

@include('partials.page-header', [
    'title'    => 'Pasif Manual',
    'subtitle' => 'Daftar periode pesanan majalah pasif (input manual)',
    'back'     => route('import.pasif.index'),
    'primary'  => ['url' => route('import.pasif.manual.create'), 'label' => 'Create Manual'],
])
@include('partials.flash')
@include('partials.pasif-table', [
    'kolom'        => $kolom,
    'rows'         => $periodes,
    'detailRoute'  => 'import.pasif.manual.show',
    'editRoute'    => 'import.pasif.manual.edit',
    'destroyRoute' => 'import.pasif.manual.destroy',
    'kosong'       => 'Belum ada data Pasif Manual.',
    'kosongLink'   => ['url' => route('import.pasif.manual.create'), 'label' => 'Buat sekarang'],
])
@endsection
