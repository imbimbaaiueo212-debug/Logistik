@extends('layouts.panel')

@section('title', 'Spare Pasif 3%')

@section('content')
@php
    $kolom = [
        ['key' => 'edisi',       'label' => 'Edisi', 'class' => 'font-semibold text-[#162749]'],
        ['key' => 'dlc_total',   'label' => 'DLC',    'align' => 'right', 'type' => 'number'],
        ['key' => 'pasif_total', 'label' => 'Pasif',  'align' => 'right', 'type' => 'number'],
        ['key' => 'bacaan_total','label' => 'Bacaan', 'align' => 'right', 'type' => 'number'],
        ['key' => 'grand_total', 'label' => 'Total',  'align' => 'right', 'type' => 'number', 'class' => 'font-medium'],
        ['key' => 'spare_raw',   'label' => 'Spare Raw', 'align' => 'right', 'type' => 'number', 'decimals' => 3, 'class' => 'text-gray-500'],
        ['key' => 'spare',       'label' => 'Spare (dibulatkan)', 'align' => 'right', 'type' => 'number', 'class' => 'font-bold text-[#E85D2A]'],
        [
            'key' => 'lembar', 'label' => 'Lembar Print', 'align' => 'right', 'type' => 'pill',
            'suffix' => ' lembar', 'pillClass' => 'bg-[#E85D2A]/10 text-[#D14E1F]',
            'value' => function ($r) { return number_format($r->lembar, 0, '.', ','); },
        ],
        ['key' => 'grup',  'label' => 'Grup',  'align' => 'center', 'type' => 'pill'],
        ['key' => 'no_ps', 'label' => 'No PS', 'type' => 'input'],
    ];
@endphp

@include('partials.page-header', [
    'title'     => 'Spare Pasif 3%',
    'subtitle'  => '(DLC + Unit Pasif + Bacaan Unit) × 3%, dibulatkan (half-up), lalu dihitung lembar print (200/lembar)',
    'back'      => route('import.pasif.index'),
    'backLabel' => 'Kembali ke Menu Pasif',
])
@include('partials.flash')
@include('partials.pasif-table', [
    'kolom'  => $kolom,
    'rows'   => $data,
    'noPsUrl'=> url('/import/pasif/spare'),
    'kosong' => 'Belum ada data Spare Pasif. Data dihitung otomatis dari DLC + Pasif + Bacaan yang aktif.',
])

<ul class="mt-4 text-sm text-gray-500 space-y-1 list-disc list-inside">
    <li>Data disimpan di tabel <code class="bg-gray-100 px-1 rounded">spare_pasifs</code> dengan grup = A.</li>
    <li>Pembulatan memakai <code class="bg-gray-100 px-1 rounded">round()</code> PHP (half-up).</li>
    <li>1 lembar print = 200 eksemplar: <code class="bg-gray-100 px-1 rounded">ceil(spare / 200)</code>.</li>
</ul>
@endsection
