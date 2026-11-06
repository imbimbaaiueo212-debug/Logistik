@extends('layouts.panel')

@section('title', 'Rekap Total')

@section('content')
@php
    $n = fn ($v) => number_format((int) $v, 0, '.', ',');
    $relawanx2 = fn ($p) => (int) $p->pesanan_sum_bacaan_unit * 2;
    $total     = fn ($p) => (int) $p->pesanan_sum_qty + (int) $p->pesanan_sum_bacaan_unit * 2 + (int) $p->pesanan_sum_humas;

    $kolom = [
        ['key' => 'edisi',   'label' => 'Edisi',   'class' => 'font-semibold text-[#162749]'],
        ['key' => 'periode', 'label' => 'Periode', 'value' => function ($p) { return $p->periode ?? trim($p->bulan . ' ' . $p->tahun); }],
        ['key' => 'no_ps',   'label' => 'No PS',   'type' => 'input'],
        ['key' => 'pesanan_count',           'label' => 'Jumlah Unit',     'align' => 'right', 'type' => 'number'],
        ['key' => 'pesanan_sum_qty',         'label' => 'MURID',           'align' => 'right', 'type' => 'number'],
        ['key' => 'pesanan_sum_bacaan_unit', 'label' => 'RELAWAN',         'align' => 'right', 'type' => 'number'],
        ['key' => 'relawan_exp',             'label' => 'RELAWAN @2 exp',  'align' => 'right', 'type' => 'number', 'value' => $relawanx2, 'class' => 'text-gray-600'],
        ['key' => 'pesanan_sum_humas',       'label' => 'HUMAS 10 + Pajangan Unit', 'align' => 'right', 'type' => 'number'],
        ['key' => 'total',                   'label' => 'TOTAL',           'align' => 'right', 'type' => 'number', 'value' => $total, 'class' => 'font-bold text-[#E85D2A]'],
        ['key' => 'status',  'label' => 'Status',  'type' => 'badge'],
    ];

    $footer = [
        'edisi'                    => 'GRAND TOTAL',
        'periode'                  => $n($grand['periode']) . ' periode',
        'pesanan_count'            => $n($grand['unit']),
        'pesanan_sum_qty'          => $n($grand['murid']),
        'pesanan_sum_bacaan_unit'  => $n($grand['relawan']),
        'relawan_exp'              => $n($grand['relawanx2']),
        'pesanan_sum_humas'        => $n($grand['humas']),
        'total'                    => $n($grand['total']),
    ];
@endphp

@include('partials.page-header', [
    'title'     => 'Rekap Total',
    'subtitle'  => 'Hasil import lengkap: MURID + RELAWAN @2 exp + HUMAS = TOTAL kalender',
    'back'      => route('kalender.pasif.index'),
    'backLabel' => 'Kembali ke Menu Unit Pasif',
    'primary'   => ['url' => route('kalender.pasif.create'), 'label' => 'Import Data'],
])
@include('partials.flash')
@include('partials.stat-cards', ['stats' => [
    ['Total Unit', $n($grand['unit'])],
    ['Total Murid', $n($grand['murid'])],
    ['Relawan @2 exp', $n($grand['relawanx2'])],
    ['Humas + Pajangan', $n($grand['humas'])],
    ['Grand Total Kalender', $n($grand['total']), true],
]])
@include('partials.pasif-table', [
    'noPsUrl'     => url('/kalender/pasif'),
    'kolom'       => $kolom,
    'rows'        => $periodes,
    'footer'      => $footer,
    'detailRoute' => 'kalender.pasif.rekap.show',
    'kosong'      => 'Belum ada data. Silakan import terlebih dahulu.',
])
@endsection
