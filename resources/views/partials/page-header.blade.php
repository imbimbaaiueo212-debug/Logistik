{{--
    Header halaman.
    Param: $title, [$subtitle], [$back (url)], [$backLabel], [$primary = ['url'=>, 'label'=>, 'icon'=>'plus'|'pencil']]
--}}
@php
    $subtitle  = $subtitle ?? null;
    $back      = $back ?? null;
    $backLabel = $backLabel ?? 'Kembali';
    $primary   = $primary ?? null;
@endphp
<div class="flex flex-wrap items-start justify-between gap-3 mb-5">
    <div>
        <h2 class="text-2xl font-bold text-[#162749]">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-sm text-gray-500 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    <div class="flex items-center gap-2">
        @if($back)
            <a href="{{ $back }}"
               class="inline-flex items-center gap-2 border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2.5 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>
                {{ $backLabel }}
            </a>
        @endif
        @if($primary)
            <a href="{{ $primary['url'] }}"
               class="inline-flex items-center gap-2 bg-[#E85D2A] hover:bg-[#D14E1F] text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    @if(($primary['icon'] ?? 'plus') === 'pencil')
                        <path d="M4 20h4L19 9l-4-4L4 16v4Z"/><path d="m13.5 6.5 4 4"/>
                    @else
                        <path d="M12 5v14M5 12h14"/>
                    @endif
                </svg>
                {{ $primary['label'] }}
            </a>
        @endif
    </div>
</div>
