{{--
    <x-metric-stat> — satu angka statistik di dock metrik hero.

    Props:
      value     : angka target (dihitung naik dari 0 oleh script .js-counter)
      label     : label di bawah angka
      icon-bg   : class Tailwind background badge ikon
      position  : 'first' | 'middle' | 'last' — mengatur padding di sisi pembatas (divide-x)

    Slot:
      icon      : elemen <svg> ikon
--}}
@props([
    'value',
    'label',
    'iconBg'   => 'bg-navy-900/5',
    'position' => 'middle',
])

@php
    $paddingClass = match ($position) {
        'first' => 'pr-3 sm:pr-5',
        'last'  => 'pl-3 sm:pl-5',
        default => 'px-3 sm:px-5',
    };
@endphp

<div class="flex items-center gap-3 {{ $paddingClass }} min-w-0">
    <div class="hidden xs:flex w-9 h-9 shrink-0 items-center justify-center rounded-lg {{ $iconBg }}">
        {{ $icon }}
    </div>
    <div class="min-w-0">
        <p class="js-counter text-lg sm:text-xl font-bold text-navy-950 truncate" data-target="{{ $value }}">0</p>
        <p class="text-[11px] sm:text-xs text-navy-950/45 leading-tight truncate">{{ $label }}</p>
    </div>
</div>