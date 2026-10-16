@php
    // [nama field, label, tipe]. Tipe: text, email, date, textarea, select
    $fields = [
        ['no_cab', 'No Cab', 'text'],
        ['no_induk_mitra', 'No Induk Mitra', 'text'],
        ['nama_stokis_db_kemitraan', 'Nama Stokis Kemitraan', 'text'],
        ['nama_stokis_db_bimbashop', 'Nama Stokis biMBA Shop', 'text'],
        ['nama_mitra', 'Nama Mitra', 'text'],
        ['email', 'Email', 'email'],
        ['no_hp', 'No HP', 'text'],
        ['ops_stokist', 'Ops Stokist', 'text'],
        ['db_kemitraan_db_bimbashop', 'DB Kemitraan & Shop', 'text'],
        ['status', 'Status', 'select'],
        ['tanggal_pasif', 'Tanggal Pasif', 'date'],
        ['related_form_pembukaan_unit_aktif', 'Form Pembukaan Unit', 'textarea'],
        ['related_formulir_kerjasama_english', 'Kerjasama English', 'textarea'],
        ['related_unit_bimba_aiueo', 'Unit biMBA', 'textarea'],
        ['related_formulir_kerjasama_mk_mm', 'Kerjasama MK/MM', 'textarea'],
        ['related_pengajuan_perubahan', 'Pengajuan Perubahan', 'textarea'],
        ['item_sku', 'Item SKU', 'textarea'],
    ];

    $sudahAda = $stokis->exists;
    $inputCls = 'w-full bg-white border border-navy-950/10 rounded-xl px-4 py-2.5 text-sm placeholder:text-navy-950/35 focus:outline-none focus:border-navy-700';
@endphp

<form action="{{ $sudahAda ? route('stokis-pasif.update', $stokis->id) : route('stokis-pasif.store') }}" method="POST"
      class="mt-6 bg-white rounded-2xl shadow-card p-5 sm:p-7">
    @csrf
    @if ($sudahAda)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-5 gap-y-4">
        @foreach ($fields as [$nama, $label, $tipe])
            @php
                $val = old($nama, $stokis->{$nama} ?? null);
                if (is_array($val)) { $val = implode(', ', $val); }
                if ($tipe === 'date') {
                    $val = filled($val) ? \Illuminate\Support\Carbon::parse($val)->format('Y-m-d') : '';
                }
            @endphp

            <div class="{{ $tipe === 'textarea' ? 'md:col-span-2' : '' }}">
                <label for="{{ $nama }}" class="block text-sm text-navy-950/60 mb-1.5">
                    {{ $label }}@if ($nama === 'no_cab') <span class="text-rust-600">*</span>@endif
                </label>

                @if ($tipe === 'textarea')
                    <textarea id="{{ $nama }}" name="{{ $nama }}" rows="2" class="{{ $inputCls }}">{{ $val }}</textarea>

                @elseif ($tipe === 'select' && $nama === 'status')
                    <select id="{{ $nama }}" name="{{ $nama }}" class="{{ $inputCls }}">
                        <option value="aktif" {{ $val === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="pasif" {{ ($val === 'pasif' || blank($val)) ? 'selected' : '' }}>Pasif</option>
                    </select>

                @else
                    <input id="{{ $nama }}" type="{{ $tipe }}" name="{{ $nama }}" value="{{ $val }}"
                           @if ($nama === 'no_cab') required @endif
                           class="{{ $inputCls }}">
                @endif

                @error($nama)
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach
    </div>

    <div class="mt-6 flex items-center gap-2">
        <button type="submit"
                class="bg-rust-500 hover:bg-rust-600 transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
            {{ $sudahAda ? 'Simpan perubahan' : 'Simpan' }}
        </button>
        <a href="{{ route('stokis-pasif.index') }}"
           class="text-sm font-medium text-navy-950/60 hover:text-navy-950 px-4 py-2.5">
            Batal
        </a>
    </div>
</form>