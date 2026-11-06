@extends('layouts.panel')

@section('title', 'Spare Pasif 3%')

@section('content')
@php
    $subtitle = $periode
        ? $periode->edisi . ' | ' . ($periode->periode ?: trim($periode->bulan . ' ' . $periode->tahun))
          . ($periode->no_ps ? ' | No PS: ' . $periode->no_ps : '')
        : 'Belum ada periode Unit Pasif';
    $n = fn ($v) => number_format((int) $v, 0, '.', ',');
@endphp

@include('partials.page-header', [
    'title'     => 'Spare Pasif 3%',
    'subtitle'  => $subtitle,
    'back'      => route('kalender.pasif.index'),
    'backLabel' => 'Kembali ke Menu Pasif',
])
@include('partials.flash')

@if($periodes->count() > 1)
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <span class="text-sm font-medium text-gray-500 mr-1">Periode</span>
        @foreach($periodes as $p)
            <a href="{{ route('kalender.pasif.spare', ['periode' => $p->id]) }}"
               class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition
                      {{ $periode && $periode->id === $p->id ? 'bg-[#162749] text-white border-[#162749]' : 'bg-white text-gray-700 border-gray-300 hover:border-[#E85D2A] hover:text-[#E85D2A]' }}">
                {{ $p->edisi }}{{ $p->no_ps ? ' · ' . $p->no_ps : '' }}
            </a>
        @endforeach
    </div>
@endif

@include('partials.stat-cards', ['stats' => [
    ['Total Unit', $n($sum['unit'])],
    ['Murid', $n($sum['murid'])],
    ['Relawan @2 exp', $n($sum['relawanx2'])],
    ['Humas + Pajangan', $n($sum['humas'])],
    ['Total Kalender', $n($sum['total']), true],
    ['Spare 3% (' . $n($sum['lembar']) . ' lembar)', $n($sum['spare'])],
]])

@include('kalender::pasif._tabel-unit', ['rows' => $rows])

<ul class="mt-4 text-sm text-gray-500 space-y-1 list-disc list-inside">
    <li>RELAWAN @2 exp = RELAWAN × 2 (tiap relawan mendapat 2 kalender).</li>
    <li>TOTAL = MURID + RELAWAN @2 exp + HUMAS (10 untuk Humas sekitar unit dan 1 dipasang di unit).</li>
    <li>Spare 3% = TOTAL × 3%, dibulatkan (half-up); 1 lembar print = 200 eksemplar.</li>
</ul>
@endsection
