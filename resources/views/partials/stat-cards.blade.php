{{-- Ringkasan angka. Param: $stats = [[label, nilai, (true = aksen rust)], ...] --}}
@php $jml = min(count($stats), 6); @endphp
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-4">
    <div class="grid grid-cols-2 md:grid-cols-{{ min($jml, 3) }} lg:grid-cols-{{ $jml }} gap-4">
        @foreach($stats as $s)
            <div class="min-w-0">
                <p class="text-xs text-gray-500">{{ $s[0] }}</p>
                <p class="font-semibold text-base truncate {{ !empty($s[2]) ? 'text-[#E85D2A]' : 'text-[#162749]' }}"
                   title="{{ $s[1] }}">{{ filled($s[1]) ? $s[1] : '-' }}</p>
            </div>
        @endforeach
    </div>
</div>
