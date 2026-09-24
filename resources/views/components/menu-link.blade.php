{{--
    <x-menu-link> — satu baris menu berupa link langsung (tanpa submenu).

    Props:
      href      : URL tujuan
      accent    : warna aksen garis kiri saat hover (hex), default rust
      icon-bg   : class Tailwind untuk background badge ikon
      title     : judul menu
      subtitle  : deskripsi singkat

    Slot:
      icon      : elemen <svg> ikon (wajib diisi oleh pemanggil)

    Contoh:
      <x-menu-link href="{{ route('products.index') }}"
                   accent="#E85D2A" icon-bg="bg-rust-500/10"
                   title="Produk" subtitle="Master produk">
          <x-slot:icon>
              <svg ...>...</svg>
          </x-slot:icon>
      </x-menu-link>
--}}
@props([
    'href',
    'accent'  => '#E85D2A',
    'iconBg'  => 'bg-rust-500/10',
    'title',
    'subtitle',
])

<a href="{{ $href }}"
   class="menu-row group flex items-center gap-3 px-4 sm:px-5 py-3.5 sm:py-4"
   style="--accent:{{ $accent }}">

    <div class="inline-flex items-center justify-center w-10 h-10 shrink-0 rounded-xl {{ $iconBg }}">
        {{ $icon }}
    </div>

    <div class="min-w-0 flex-1">
        <h3 class="font-semibold text-[15px] text-navy-950">{{ $title }}</h3>
        <p class="text-xs text-navy-950/45 truncate">{{ $subtitle }}</p>
    </div>

    <svg class="menu-chevron w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m9 6 6 6-6 6"/>
    </svg>
</a>