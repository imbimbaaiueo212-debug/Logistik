@extends('layouts.panel')

@section('title', 'Edit Manual ' . ucfirst($jenis))

@push('styles')
<style>
    .field-label { font-size: 12px; font-weight: 500; color: #0F1B3399; margin-bottom: 4px; display: block; }
    .field-input {
        width: 100%; border: 1px solid #0F1B331A; border-radius: 0.75rem; padding: 9px 12px;
        font-size: 13.5px; background: #fff; color: #0F1B33;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    .field-input:focus { outline: none; border-color: #28447F; box-shadow: 0 0 0 3px rgba(40,68,127,0.12); }
    .field-input:disabled, .field-input[readonly] { background: #F8F9FB; color: #0F1B3366; }
    .section-title { font-size: 14px; font-weight: 600; color: #0F1B33; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid #0F1B331A; }
</style>
@endpush

@section('content')
    @php
        $tgl = optional($order->order_date);
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-2xl sm:text-[28px] leading-tight font-bold text-navy-950">Edit Manual {{ ucfirst($jenis) }}</h1>
            <p class="mt-1 text-sm text-navy-950/55">{{ $manualId }} &middot; {{ $order->customer_name }}</p>
        </div>
        <a href="{{ route("order-manual-{$jenis}.manual") }}"
           class="inline-flex items-center gap-2 bg-white border border-navy-950/10 hover:border-navy-700 text-navy-950/70 hover:text-navy-700 text-sm font-medium px-5 py-2.5 rounded-xl transition-colors shrink-0">
            Kembali
        </a>
    </div>

    @if(session('error'))
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">{!! session('error') !!}</div>
    @endif
    @if($errors->any())
        <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl text-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route("order-manual-{$jenis}.manual.update", $manualId) }}" class="mt-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl shadow-card p-6">
            <h2 class="section-title">Data Pesanan</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">ID Manual</label>
                    <input type="text" value="{{ $manualId }}" class="field-input" readonly>
                </div>
                <div>
                    <label class="field-label">Customer / Unit</label>
                    <input type="text" value="{{ $order->customer_name }}" class="field-input" readonly>
                </div>

                <div>
                    <label class="field-label">Tanggal</label>
                    <div style="display:flex; gap:8px">
                        <input type="date" name="order_date_date" value="{{ old('order_date_date', $tgl ? $tgl->format('Y-m-d') : date('Y-m-d')) }}" class="field-input" style="flex:1" required>
                        <input type="time" name="order_date_time" value="{{ old('order_date_time', $tgl ? $tgl->format('H:i') : '00:00') }}" class="field-input" style="width:110px">
                    </div>
                </div>

                <div>
                    <label class="field-label">Status Unit <span class="text-rust-600">*</span></label>
                    @php $su = old('status_unit', $order->status_unit); @endphp
                    <select name="status_unit" required class="field-input">
                        <option value="aktif" @selected($su === 'aktif')>Aktif</option>
                        <option value="pasif" @selected($su === 'pasif')>Pasif</option>
                        <option value="souv"  @selected($su === 'souv')>SOUV (PUW)</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">ID biMBA Shop (opsional)</label>
                    <input type="text" name="order_id" value="{{ old('order_id', $order->order_id) }}" class="field-input"
                           placeholder="mis. 878029">
                    <p class="text-xs text-navy-950/40 mt-1">
                        Diisi bila ingin menautkan ke order Shop tertentu. Kosongkan agar dicocokkan otomatis.
                        @if($order->bimba_order_id) Saat ini terhubung ke <strong>{{ $order->bimba_order_id }}</strong>. @endif
                    </p>
                </div>

                <div>
                    <label class="field-label">Status Kirim</label>
                    @php $sk = old('status_kirim', $order->status_kirim ?: 'Dikirim'); @endphp
                    <select name="status_kirim" class="field-input">
                        <option value="Dikirim" @selected($sk === 'Dikirim')>Dikirim</option>
                        <option value="Diambil" @selected($sk === 'Diambil')>Diambil</option>
                    </select>
                </div>

                <div>
                    <label class="field-label">Ekspedisi</label>
                    @php $ek = old('ekspedisi', $order->ekspedisi); @endphp
                    <select name="ekspedisi" class="field-input">
                        <option value="">-</option>
                        @foreach(['Lion Parcel', 'JNE', 'Diambil Sendiri'] as $opt)
                            <option value="{{ $opt }}" @selected($ek === $opt)>{{ $opt }}</option>
                        @endforeach
                        @if($ek && ! in_array($ek, ['Lion Parcel', 'JNE', 'Diambil Sendiri'], true))
                            <option value="{{ $ek }}" selected>{{ $ek }}</option>
                        @endif
                    </select>
                </div>

                <div>
                    <label class="field-label">Service</label>
                    <input type="text" name="service_pengiriman" value="{{ old('service_pengiriman', $order->service_pengiriman) }}" class="field-input"
                           placeholder="mis. REGPACK / REG">
                </div>

                <div class="md:col-span-2">
                    <label class="field-label">Catatan</label>
                    <textarea name="catatan" rows="3" class="field-input">{{ old('catatan', $order->catatan) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-card p-6">
            <h2 class="section-title">Items / SKU</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-navy-950/50">
                            <th class="py-2 pr-4">SKU</th>
                            <th class="py-2 pr-4">Produk</th>
                            <th class="py-2" style="width:140px">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($items as $it)
                        <tr class="border-t border-navy-950/5">
                            <td class="py-2.5 pr-4">{{ $it->product_sku }}</td>
                            <td class="py-2.5 pr-4">{{ $it->product_name }}</td>
                            <td class="py-2.5">
                                <input type="number" min="1" name="items[{{ $it->id }}][qty]"
                                       value="{{ old("items.{$it->id}.qty", $it->qty) }}" class="field-input" required>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <p class="text-xs text-navy-950/40 mt-3">
                Mengubah qty, Status Unit, atau ID biMBA Shop akan mencocokkan ulang pesanan ini ke biMBA Shop.
            </p>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="bg-rust-600 hover:bg-rust-500 text-white px-6 py-2.5 rounded-xl text-sm font-semibold transition-colors">
                Simpan Perubahan
            </button>
            <a href="{{ route("order-manual-{$jenis}.manual") }}" class="px-6 py-2.5 rounded-xl bg-white border border-navy-950/10 hover:border-navy-700 text-navy-950/70 hover:text-navy-700 text-sm font-medium transition-colors">
                Batal
            </a>
        </div>
    </form>
@endsection
