@extends('layouts.panel')

@section('title', 'Spare Pasif 3%')

@push('styles')
<style>
    .spare-wrap { overflow: auto; max-height: calc(100vh - 330px); }
    .spare-table { width: 100%; border-collapse: separate; border-spacing: 0; font-size: 13px; }
    .spare-table thead th {
        position: sticky; top: 0; z-index: 20; background: #162749; color: #fff;
        padding: 12px 16px; font-weight: 600; white-space: nowrap; line-height: 1.25;
    }
    .spare-table tbody td { padding: 10px 16px; border-bottom: 1px solid #eef0f5; background: #fff; white-space: nowrap; }
    .spare-table tbody tr:hover td { background: #f7f8fb; }
    .spare-table tfoot td { position: sticky; bottom: 0; z-index: 15; padding: 12px 16px; background: #eef1f6; font-weight: 700; color: #162749; border-top: 2px solid #162749; white-space: nowrap; }
    .spare-table th:first-child, .spare-table td:first-child { position: sticky; left: 0; z-index: 10; }
    .spare-table thead th:first-child { z-index: 30; }
    .spare-table tfoot td:first-child { z-index: 25; }
    .spare-clip { display: block; max-width: 320px; overflow: hidden; text-overflow: ellipsis; }
</style>
@endpush

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

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="spare-wrap">
        <table class="spare-table">
            <thead>
                <tr>
                    <th style="text-align:left">NO</th>
                    <th style="text-align:left">CABANG</th>
                    <th style="text-align:left">biMBA-AIUEO UNIT</th>
                    <th style="text-align:right">MURID</th>
                    <th style="text-align:right">RELAWAN</th>
                    <th style="text-align:right">RELAWAN @2 exp</th>
                    <th style="text-align:right">HUMAS 10 + Pajangan Unit</th>
                    <th style="text-align:right">TOTAL</th>
                    <th style="text-align:left">NO TELP</th>
                    <th style="text-align:left">ALAMAT</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="font-medium">{{ $r->no_cab ?: '-' }}</td>
                        <td class="font-semibold text-[#162749]">{{ $r->nama_unit }}</td>
                        <td style="text-align:right">{{ $n($r->qty) }}</td>
                        <td style="text-align:right">{{ $n($r->bacaan_unit) }}</td>
                        <td style="text-align:right" class="text-gray-600">{{ $n($r->relawan_exp) }}</td>
                        <td style="text-align:right">{{ $n($r->humas) }}</td>
                        <td style="text-align:right" class="font-bold text-[#E85D2A]">{{ $n($r->total_kalender) }}</td>
                        <td>{{ filled($r->telepon) ? $r->telepon : '-' }}</td>
                        <td><span class="spare-clip" title="{{ $r->alamat }}">{{ filled($r->alamat) ? $r->alamat : '-' }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align:center" class="py-14 text-gray-400">
                            Belum ada data Unit Pasif.
                            <a href="{{ route('kalender.pasif.create') }}" class="text-[#E85D2A] hover:underline ml-1">Import Excel</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($rows->isNotEmpty())
                <tfoot>
                    <tr>
                        <td></td>
                        <td></td>
                        <td>TOTAL</td>
                        <td style="text-align:right">{{ $n($sum['murid']) }}</td>
                        <td style="text-align:right">{{ $n($sum['relawan']) }}</td>
                        <td style="text-align:right">{{ $n($sum['relawanx2']) }}</td>
                        <td style="text-align:right">{{ $n($sum['humas']) }}</td>
                        <td style="text-align:right" class="text-[#E85D2A]">{{ $n($sum['total']) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<ul class="mt-4 text-sm text-gray-500 space-y-1 list-disc list-inside">
    <li>RELAWAN @2 exp = RELAWAN × 2 (tiap relawan mendapat 2 kalender).</li>
    <li>TOTAL = MURID + RELAWAN @2 exp + HUMAS (10 untuk Humas sekitar unit dan 1 dipasang di unit).</li>
    <li>Spare 3% = TOTAL × 3%, dibulatkan (half-up); 1 lembar print = 200 eksemplar.</li>
</ul>
@endsection
