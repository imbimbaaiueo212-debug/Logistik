<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Manual Sertifikat - biMBA AIUEO Logistik</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .field-label { font-size: 12px; color: #6b7280; margin-bottom: 4px; display: block; }
        .field-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            padding: 7px 10px;
            font-size: 13px;
            background: #fff;
        }
        .field-input:focus {
            outline: none;
            border-color: #2271b1;
            box-shadow: 0 0 0 1px #2271b1;
        }
        .section-title {
            font-size: 14px;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 12px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .link-blue { color: #2271b1; font-size: 12px; text-decoration: none; }
        .link-blue:hover { text-decoration: underline; }
        .select2-container .select2-selection--single {
            height: 36px !important;
            border: 1px solid #d1d5db !important;
            border-radius: 4px !important;
            padding-top: 3px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px !important;
            font-size: 13px;
            padding-left: 10px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 34px !important;
        }
    </style>
</head>
<body class="bg-gray-100">
@include('partials.top-nav')

<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-lg font-semibold text-gray-800">Edit Manual Sertifikat</h1>
            <p class="text-sm text-gray-500">
                ID Manual: <strong>MS-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</strong>
                @if($order->order_id)
                    · Order ID: <span class="text-indigo-600 font-medium">{{ $order->order_id }}</span>
                @endif
            </p>
        </div>
        <a href="{{ route('order-manual-sertifikat.manual') }}" class="text-sm bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">← Kembali</a>
    </div>

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            {!! session('error') !!}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form id="formManualSertifikat" action="{{ route('order-manual-sertifikat.manual.update', $order->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white border border-gray-200 rounded shadow-sm p-5 mb-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- GENERAL --}}
                <div>
                    <div class="section-title">General</div>
                    <div class="space-y-3">
                        <div>
                            <label class="field-label">ID Manual</label>
                            <input type="text" class="field-input bg-gray-100 text-gray-500"
                                value="MS-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}" disabled>
                        </div>

                        <div>
                            <label class="field-label">ID Pesanan (Bimba Shop)</label>
                            <input type="text" name="order_id" value="{{ old('order_id', $order->order_id) }}" class="field-input"
                                placeholder="Kosongkan jika belum ada">
                        </div>

                        <div>
                            <label class="field-label">Date created</label>
                            <div class="flex items-center gap-1">
                                @php
                                    $od = $order->order_date ? \Carbon\Carbon::parse($order->order_date) : null;
                                @endphp
                                <input type="date" name="order_date_date"
                                    value="{{ old('order_date_date', $od ? $od->format('Y-m-d') : date('Y-m-d')) }}"
                                    class="field-input" style="flex:1">
                                <span class="text-gray-400 text-sm px-1">@</span>
                                <input type="time" name="order_date_time"
                                    value="{{ old('order_date_time', $od ? $od->format('H:i') : date('H:i')) }}"
                                    class="field-input" style="width:90px">
                            </div>
                        </div>

                        <div>
                            <label class="field-label">Customer</label>
                            <select name="customer_id" id="customerSelect" style="width:100%">
                                <option value="">— Pilih Customer —</option>
                                @foreach($customers ?? [] as $c)
                                    @php
                                        $displayName = $c->display_name ?? '';
                                        $company     = $c->billing_company ?? '';
                                        $matchName   = trim(old('customer_name', $order->customer_name ?? ''));
                                        $isSelected  = false;
                                        if ($matchName !== '') {
                                            $isSelected =
                                                strcasecmp(trim($displayName), $matchName) === 0
                                                || strcasecmp(trim($company), $matchName) === 0
                                                || str_contains(strtolower($displayName), strtolower($matchName))
                                                || str_contains(strtolower($company), strtolower($matchName));
                                        }
                                    @endphp
                                    <option
                                        value="{{ $c->ID }}"
                                        data-display-name="{{ $c->display_name }}"
                                        data-first-name="{{ $c->first_name ?? $c->billing_first_name }}"
                                        data-last-name="{{ $c->last_name ?? $c->billing_last_name }}"
                                        data-email="{{ $c->user_email ?? $c->billing_email }}"
                                        data-phone="{{ $c->billing_phone ?? '' }}"
                                        data-company="{{ $c->billing_company ?? '' }}"
                                        data-address-1="{{ $c->billing_address_1 ?? '' }}"
                                        data-address-2="{{ $c->billing_address_2 ?? '' }}"
                                        data-city="{{ $c->billing_city ?? '' }}"
                                        data-postcode="{{ $c->billing_postcode ?? '' }}"
                                        data-state="{{ $c->billing_state ?? '' }}"
                                        data-billing-first="{{ $c->billing_first_name ?? '' }}"
                                        data-billing-last="{{ $c->billing_last_name ?? '' }}"
                                        @selected($isSelected)
                                    >
                                        {{ $c->display_name }}
                                        @if($c->user_email) — {{ $c->user_email }} @endif
                                        (#{{ $c->ID }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="customer_name" id="customer_name" value="{{ old('customer_name', $order->customer_name) }}">
                        </div>

                        <div>
                            <label class="field-label">Status Kirim</label>
                            <select name="status_kirim" id="statusKirim" class="field-input">
                                <option value="Dikirim" @selected(old('status_kirim', $order->status_kirim ?? 'Dikirim') == 'Dikirim')>Dikirim</option>
                                <option value="Diambil" @selected(old('status_kirim', $order->status_kirim) == 'Diambil')>Diambil</option>
                            </select>
                        </div>

                        <div>
                            <label class="field-label">Ekspedisi</label>
                            <select name="ekspedisi" id="ekspedisi" class="field-input">
                                <option value="Lion Parcel" @selected(old('ekspedisi', $order->ekspedisi) == 'Lion Parcel')>Lion Parcel</option>
                                <option value="JNE" @selected(old('ekspedisi', $order->ekspedisi) == 'JNE')>JNE</option>
                                <option value="Diambil Sendiri" @selected(old('ekspedisi', $order->ekspedisi) == 'Diambil Sendiri')>Diambil Sendiri</option>
                            </select>
                        </div>

                        <div>
                            <label class="field-label">Service</label>
                            <select name="service_pengiriman" id="servicePengiriman" class="field-input"></select>
                        </div>

                        <div>
                            <label class="field-label">Status</label>
                            <select name="status" class="field-input">
                                <option value="pending" @selected(old('status', $order->status) == 'pending')>Pending</option>
                                <option value="processing" @selected(old('status', $order->status) == 'processing')>Processing</option>
                                <option value="completed" @selected(old('status', $order->status) == 'completed')>Completed</option>
                            </select>
                        </div>

                        <div>
                            <label class="field-label">Payment method</label>
                            <select name="payment_method" class="field-input">
                                <option value="manual" @selected(old('payment_method', $order->payment_method ?? 'manual') == 'manual')>Manual</option>
                                <option value="transfer" @selected(old('payment_method', $order->payment_method) == 'transfer')>Transfer</option>
                                <option value="cash" @selected(old('payment_method', $order->payment_method) == 'cash')>Cash</option>
                                <option value="N/A" @selected(old('payment_method', $order->payment_method) == 'N/A')>N/A</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- BILLING --}}
                <div>
                    <div class="section-title">Billing</div>
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">First name</label>
                                <input type="text" name="billing_first_name" id="billing_first_name"
                                    value="{{ old('billing_first_name', $order->billing_first_name) }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Last name (No Cabang)</label>
                                <input type="text" name="billing_last_name" id="billing_last_name"
                                    value="{{ old('billing_last_name', $order->billing_last_name) }}" class="field-input" placeholder="No Cabang">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Company / Nama Unit</label>
                            <input type="text" name="billing_company" id="billing_company"
                                value="{{ old('billing_company', $order->customer_name) }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Address line 1</label>
                            <input type="text" name="billing_address_1" id="billing_address_1"
                                value="{{ old('billing_address_1', $order->shipping_address_1) }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Address line 2</label>
                            <input type="text" name="billing_address_2" id="billing_address_2"
                                value="{{ old('billing_address_2', $order->shipping_address_2) }}" class="field-input">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">City</label>
                                <input type="text" name="billing_city" id="billing_city"
                                    value="{{ old('billing_city', $order->shipping_city) }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Phone</label>
                                <input type="text" name="phone" id="phone"
                                    value="{{ old('phone', $order->phone) }}" class="field-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Sub District (Kelurahan)</label>
                                <input type="text" name="billing_kelurahan" id="billing_kelurahan"
                                    value="{{ old('billing_kelurahan', $order->billing_kelurahan ?? '') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">District (Kecamatan)</label>
                                <input type="text" name="billing_kecamatan" id="billing_kecamatan"
                                    value="{{ old('billing_kecamatan', $order->billing_kecamatan ?? '') }}" class="field-input">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SHIPPING --}}
                <div>
                    <div class="flex justify-between items-center mb-3">
                        <div class="section-title mb-0 border-0 pb-0">Shipping</div>
                        <a href="javascript:void(0)" onclick="copyBillingToShipping()" class="link-blue">Copy billing address</a>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="field-label">Address line 1</label>
                            <input type="text" name="shipping_address_1" id="shipping_address_1"
                                value="{{ old('shipping_address_1', $order->shipping_address_1) }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Address line 2</label>
                            <input type="text" name="shipping_address_2" id="shipping_address_2"
                                value="{{ old('shipping_address_2', $order->shipping_address_2) }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">City</label>
                            <input type="text" name="shipping_city" id="shipping_city"
                                value="{{ old('shipping_city', $order->shipping_city) }}" class="field-input">
                        </div>
                        <div>
                            <label class="field-label">Phone</label>
                            <input type="text" name="shipping_phone" id="shipping_phone"
                                value="{{ old('shipping_phone', $order->phone) }}" class="field-input">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Sub District (Kelurahan)</label>
                                <input type="text" name="shipping_kelurahan" id="shipping_kelurahan"
                                    value="{{ old('shipping_kelurahan', $order->shipping_kelurahan ?? '') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">District (Kecamatan)</label>
                                <input type="text" name="shipping_kecamatan" id="shipping_kecamatan"
                                    value="{{ old('shipping_kecamatan', $order->shipping_kecamatan ?? '') }}" class="field-input">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Customer provided note</label>
                            <textarea name="catatan" rows="4" class="field-input">{{ old('catatan', $order->catatan) }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Weight</label>
                            <div class="flex items-center gap-2">
                                <input type="number" name="order_weight"
                                    value="{{ old('order_weight', $order->order_weight) }}"
                                    step="1" min="0" class="field-input" style="width:120px">
                                <span class="text-sm text-gray-500">g</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ITEM (single) --}}
        <div class="bg-white border border-gray-200 rounded shadow-sm p-5 mb-5">
            <div class="section-title mb-3">Item / SKU</div>
            <p class="text-xs text-gray-400 mb-3">SKU diambil dari <strong>label</strong> master produk</p>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-gray-500 border-b">
                            <th class="pb-2 pr-2 font-medium" style="min-width:220px">SKU <span class="text-red-500">*</span></th>
                            <th class="pb-2 pr-2 font-medium" style="min-width:180px">Nama Produk / Sertifikat <span class="text-red-500">*</span></th>
                            <th class="pb-2 pr-2 font-medium" style="min-width:110px">Harga Jual</th>
                            <th class="pb-2 pr-2 font-medium w-24">Qty <span class="text-red-500">*</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row border-b border-gray-100">
                            <td class="py-2 pr-2">
                                <select class="sku-select" name="product_sku" style="width:100%">
                                    @if($order->product_sku)
                                        <option value="{{ $order->product_sku }}" selected>
                                            {{ $order->product_sku }}@if($order->product_name) — {{ $order->product_name }}@endif
                                        </option>
                                    @endif
                                </select>
                                <input type="hidden" name="product_id" class="product-id" value="{{ $order->product_id ?? '' }}">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="text" name="product_name" required
                                    class="field-input product-name"
                                    placeholder="Nama produk"
                                    value="{{ old('product_name', $order->product_name) }}">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="number" name="harga_jual" step="0.01" min="0"
                                    class="field-input product-harga"
                                    placeholder="0"
                                    value="{{ old('harga_jual', $order->price ?? '') }}">
                            </td>
                            <td class="py-2 pr-2">
                                <input type="number" name="qty" min="1" required class="field-input"
                                    value="{{ old('qty', $order->qty ?? 1) }}">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded text-sm font-semibold hover:bg-indigo-700">
                💾 Simpan Perubahan
            </button>
            <a href="{{ route('order-manual-sertifikat.manual') }}" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm">Batal</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
const searchProductUrl = @json(route('order-manual-sertifikat.search-products'));
const currentService = @json(old('service_pengiriman', $order->service_pengiriman));

function initSkuSelect(el) {
    const $el = $(el);

    $el.select2({
        placeholder: 'Cari SKU / Label / Nama...',
        allowClear: true,
        width: '100%',
        minimumInputLength: 0,
        ajax: {
            url: searchProductUrl,
            dataType: 'json',
            delay: 250,
            cache: true,
            data: function (params) {
                return { q: params.term || '' };
            },
            processResults: function (data) {
                if (Array.isArray(data)) return { results: data };
                return { results: data.results || [] };
            }
        }
    });

    $el.on('select2:select', function (e) {
        const d = e.params.data;
        const $row = $(this).closest('.item-row');

        $row.find('.product-id').val(d.product_id || '');
        $row.find('.product-name').val(d.name || '');

        const hargaSatuan = parseFloat(d.harga_jual || d.harga) || 0;
        $el.data('harga-satuan', hargaSatuan);
        $el.data('berat', parseFloat(d.berat) || 0);

        $row.find('.product-harga').val(hargaSatuan);
        updateHargaJualRow($row);
        calculateTotalWeight();
    });

    $el.on('select2:clear', function () {
        const $row = $(this).closest('.item-row');
        $row.find('.product-id').val('');
        $row.find('.product-name').val('');
        $row.find('.product-harga').val('');
        $el.data('harga-satuan', 0);
        $el.data('berat', 0);
        calculateTotalWeight();
    });
}

function updateHargaJualRow($row) {
    const hargaSatuan = parseFloat($row.find('.sku-select').data('harga-satuan')) || 0;
    const qty = parseFloat($row.find('input[name="qty"]').val()) || 1;
    if (hargaSatuan > 0) {
        $row.find('.product-harga').val(Math.round(hargaSatuan * qty));
    }
}

function calculateTotalWeight() {
    const beratSatuanKg = parseFloat($('.sku-select').data('berat')) || 0;
    const qty = parseFloat($('input[name="qty"]').val()) || 0;
    const totalGram = beratSatuanKg * qty * 1000;
    if (totalGram > 0) {
        $('input[name="order_weight"]').val(Math.round(totalGram));
    }
}

$(document).on('input change', 'input[name="qty"]', function () {
    updateHargaJualRow($(this).closest('.item-row'));
    calculateTotalWeight();
});

$(document).on('change', '.product-harga', function () {
    const $row = $(this).closest('.item-row');
    const qty = parseFloat($row.find('input[name="qty"]').val()) || 1;
    const hargaSekarang = parseFloat($(this).val()) || 0;
    $row.find('.sku-select').data('harga-satuan', qty > 0 ? (hargaSekarang / qty) : hargaSekarang);
});

function copyBillingToShipping() {
    document.getElementById('shipping_address_1').value = document.getElementById('billing_address_1').value || '';
    document.getElementById('shipping_address_2').value = document.getElementById('billing_address_2')?.value || '';
    document.getElementById('shipping_city').value = document.getElementById('billing_city').value || '';
    document.getElementById('shipping_phone').value = document.getElementById('phone').value || '';
    document.getElementById('shipping_kelurahan').value = document.getElementById('billing_kelurahan')?.value || '';
    document.getElementById('shipping_kecamatan').value = document.getElementById('billing_kecamatan')?.value || '';
}

$(document).ready(function () {
    initSkuSelect('.sku-select');

    // Pre-set harga satuan dari data existing
    const existingPrice = parseFloat($('.product-harga').val()) || 0;
    const existingQty = parseFloat($('input[name="qty"]').val()) || 1;
    if (existingPrice > 0 && existingQty > 0) {
        $('.sku-select').data('harga-satuan', existingPrice / existingQty);
    }

    $('#customerSelect').select2({
        placeholder: '— Pilih Unit / Customer —',
        allowClear: true,
        width: '100%'
    });

    // Pastikan nilai yang sudah selected ikut tampil di Select2
    const selectedVal = $('#customerSelect').val();
    if (selectedVal) {
        $('#customerSelect').val(selectedVal).trigger('change.select2');
    }

    $('#customerSelect').on('change', function () {
        const opt = $(this).find(':selected');
        if (!opt.val()) return;

        const displayName  = opt.data('display-name') || '';
        const company      = opt.data('company') || displayName;
        const billingFirst = opt.data('billing-first') || opt.data('first-name') || '';
        const billingLast  = opt.data('billing-last') || opt.data('last-name') || '';
        const phone        = opt.data('phone') || '';
        const address1     = opt.data('address-1') || '';
        const address2     = opt.data('address-2') || '';
        const city         = opt.data('city') || '';

        $('#customer_name').val(displayName || company);
        $('#billing_first_name').val(billingFirst);
        $('#billing_last_name').val(billingLast);
        $('#billing_company').val(company);
        $('#billing_address_1').val(address1);
        $('#billing_address_2').val(address2);
        $('#billing_city').val(city);
        $('#phone').val(phone);

        // Shipping ikut terisi
        $('#shipping_address_1').val(address1);
        $('#shipping_address_2').val(address2);
        $('#shipping_city').val(city);
        $('#shipping_phone').val(phone);
    });

    // Status Kirim & Ekspedisi
    const statusEl    = document.getElementById('statusKirim');
    const ekspedisiEl = document.getElementById('ekspedisi');
    const serviceEl   = document.getElementById('servicePengiriman');
    const form        = document.getElementById('formManualSertifikat');

    const lion = [
        {v:'REGPACK',l:'REGPACK'},{v:'BOSPACK',l:'BOSPACK'},
        {v:'JAGOPACK',l:'JAGOPACK'},{v:'BIGPACK',l:'BIGPACK'}
    ];

    function fillService(opts, sel) {
        serviceEl.innerHTML = '';
        opts.forEach(o => serviceEl.add(new Option(o.l, o.v, false, o.v === sel)));
    }

    function applyStatus() {
        if (statusEl.value === 'Diambil') {
            ekspedisiEl.innerHTML = '<option value="Diambil Sendiri">Diambil Sendiri</option>';
            ekspedisiEl.disabled = true;
            serviceEl.innerHTML = '<option value="">—</option>';
            serviceEl.disabled = true;
        } else {
            ekspedisiEl.disabled = false;
            serviceEl.disabled = false;
            const currentEkspedisi = ekspedisiEl.value || 'Lion Parcel';
            ekspedisiEl.innerHTML = '<option value="Lion Parcel">Lion Parcel</option><option value="JNE">JNE</option>';
            ekspedisiEl.value = currentEkspedisi === 'Diambil Sendiri' ? 'Lion Parcel' : currentEkspedisi;
            fillService(
                ekspedisiEl.value === 'JNE' ? [{v:'REG',l:'REG'}] : lion,
                currentService || (ekspedisiEl.value === 'JNE' ? 'REG' : 'REGPACK')
            );
        }
    }

    statusEl.addEventListener('change', applyStatus);
    ekspedisiEl.addEventListener('change', function () {
        fillService(
            ekspedisiEl.value === 'JNE' ? [{v:'REG',l:'REG'}] : lion,
            ekspedisiEl.value === 'JNE' ? 'REG' : 'REGPACK'
        );
    });

    form.addEventListener('submit', function () {
        if (statusEl.value === 'Diambil') {
            ekspedisiEl.disabled = false;
            serviceEl.disabled = false;
        }
        const d = form.querySelector('[name="order_date_date"]').value;
        const t = form.querySelector('[name="order_date_time"]').value || '00:00';
        if (d) {
            let h = form.querySelector('[name="order_date"]');
            if (!h) {
                h = document.createElement('input');
                h.type = 'hidden';
                h.name = 'order_date';
                form.appendChild(h);
            }
            h.value = d + ' ' + t + ':00';
        }
    });

    applyStatus();
});
</script>
</body>
</html>