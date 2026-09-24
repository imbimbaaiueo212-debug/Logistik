{{--
    <x-menu-group> — satu baris menu yang bisa dibuka (dropdown) berisi beberapa submenu.

    Props:
      accent    : warna aksen garis kiri saat hover/terbuka (hex)
      icon-bg   : class Tailwind untuk background badge ikon
      title     : judul menu
      subtitle  : deskripsi singkat
      open      : true/false — apakah terbuka secara default (opsional)

    Slot:
      icon      : elemen <svg> ikon (wajib)
      submenu   : daftar <a class="submenu-link"> / label / badge "segera" di dalamnya

    Contoh:
      <x-menu-group accent="#28447F" icon-bg="bg-navy-700/[0.08]"
                    title="User" subtitle="biMBA Shop, Unit Kemitraan, Matching">
          <x-slot:icon><svg ...>...</svg></x-slot:icon>
          <x-slot:submenu>
              <a href="{{ route('user.export') }}" class="submenu-link">biMBA Shop</a>
          </x-slot:submenu>
      </x-menu-group>
--}}
@props([
    'accent'  => '#28447F',
    'iconBg'  => 'bg-navy-700/[0.08]',
    'title',
    'subtitle',
    'open'    => false,
])

<details class="menu-row group" style="--accent:{{ $accent }}" @if($open) open @endif>
    <summary class="flex items-center gap-3 px-4 sm:px-5 py-3.5 sm:py-4">
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
    </summary>

    <div class="submenu px-4 sm:px-5 pb-3.5 sm:pb-4 pl-[3.6rem] sm:pl-[4.1rem] space-y-1">
        {{ $submenu }}
    </div>
</details>