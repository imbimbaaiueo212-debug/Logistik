@extends('layouts.panel')

@section('title', 'Detail Pasif Manual')

@section('content')
@php
    $kolom = [
        ['key' => 'no',        'label' => 'No'],
        ['key' => 'id_pesan',  'label' => 'Id Pesan', 'class' => 'font-medium text-[#28447F]'],
        ['key' => 'tgl_pesan', 'label' => 'Tgl Pesan',
            'value' => function ($i) { return $i->tgl_pesan ? \Carbon\Carbon::parse($i->tgl_pesan)->format('d/m/Y') : null; }],
        ['key' => 'nama_unit', 'label' => 'Nama Unit', 'class' => 'font-semibold text-[#162749]'],
        ['key' => 'label',     'label' => 'Label', 'type' => 'pill'],
        ['key' => 'jumlah',    'label' => 'Jumlah', 'align' => 'center', 'type' => 'number', 'class' => 'font-semibold'],
        ['key' => 'ekspedisi', 'label' => 'Ekspedisi'],
        ['key' => 'service_pengiriman', 'label' => 'Service'],
        ['key' => 'note',       'label' => 'Note',       'clip' => true],
        ['key' => 'keterangan', 'label' => 'Keterangan', 'clip' => true],
    ];
@endphp

@include('partials.page-header', [
    'title'    => 'Detail Pasif Manual',
    'subtitle' => 'Edisi ' . $periode->edisi . ($periode->judul ? ' - ' . $periode->judul : ''),
    'back'     => route('import.pasif.manual.index'),
    'primary'  => ['url' => route('import.pasif.manual.edit', $periode->id), 'label' => 'Edit', 'icon' => 'pencil'],
])
@include('partials.flash')
@include('partials.stat-cards', ['stats' => [
    ['Edisi', $periode->edisi],
    ['Judul', $periode->judul],
    ['Periode', $periode->periode],
    ['Bulan / Tahun', trim(($periode->bulan ?? '') . ' ' . ($periode->tahun ?? ''))],
    ['No PS', $periode->no_ps],
    ['Total Qty', number_format($total), true],
]])

<h3 class="text-base font-semibold text-[#162749] mb-3">Daftar Unit ({{ $periode->transaksis->count() }})</h3>
@include('partials.pasif-table', [
    'kolom'  => $kolom,
    'rows'   => $periode->transaksis,
    'kosong' => 'Belum ada unit pada periode ini.',
])
@endsection
