<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Manual Modul - biMBA AIUEO Logistik</title>
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
        <h1 class="text-lg font-semibold text-gray-800">Create Manual Modul</h1>
        <a href="{{ route('order-manual-modul.manual') }}" class="text-sm bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">← Kembali</a>
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

    <form id="formManualModul" action="{{ route('order-manual-modul.manual.store') }}" method="POST">
        @csrf

        <div class="bg-white border border-gray-200 rounded shadow-sm p-5 mb-5">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- GENERAL --}}
                <div>
                    <div class="section-title">General</div>
                    <div class="space-y-3">
                        <div>
                            <label class="field-label">ID Manual</label>
                            <input type="text" class="field-input bg-gray-100 text-gray-500"
                                value="Otomatis (contoh: MM-{{ date('Ymd') }}-0001)" disabled>
                            <p class="text-xs text-gray-400 mt-1">Di-generate otomatis saat disimpan</p>
                        </div>

                        <div>
                            <label class="field-label">ID Pesanan (Bimba Shop)</label>
                            <input type="text" name="order_id" value="{{ old('order_id') }}" class="field-input"
                                placeholder="Kosongkan jika belum ada">
                        </div>

                        <div>
                            <label class="field-label">Date created</label>
                            <div class="flex items-center gap-1">
                                <input type="date" name="order_date_date" value="{{ old('order_date_date', date('Y-m-d')) }}" class="field-input" style="flex:1">
                                <span class="text-gray-400 text-sm px-1">@</span>
                                <input type="time" name="order_date_time" value="{{ old('order_date_time', date('H:i')) }}" class="field-input" style="width:90px">
                            </div>
                        </div>

                        <div>
                            <label class="field-label">Customer</label>
                            <select name="customer_id" id="customerSelect" style="width:100%">
                                <option value="">— Pilih Customer —</option>
                                @foreach($customers ?? [] as $c)
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
                                        @selected(old('customer_id') == $c->ID)
                                    >
                                        {{ $c->display_name }}
                                        @if($c->user_email) — {{ $c->user_email }} @endif
                                        (#{{ $c->ID }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="customer_name" id="customer_name" value="{{ old('customer_name') }}">
                        </div>

                        <div>
                            <label class="field-label">Status Kirim</label>
                            <select name="status_kirim" id="statusKirim" class="field-input">
                                <option value="Dikirim" @selected(old('status_kirim','Dikirim')=='Dikirim')>Dikirim</option>
                                <option value="Diambil" @selected(old('status_kirim')=='Diambil')>Diambil</option>
                            </select>
                        </div>

                        <div>
                            <label class="field-label">Ekspedisi</label>
                            <select name="ekspedisi" id="ekspedisi" class="field-input">
                                <option value="Lion Parcel">Lion Parcel</option>
                                <option value="JNE">JNE</option>
                            </select>
                        </div>

                        <div>
                            <label class="field-label">Service</label>
                            <select name="service_pengiriman" id="servicePengiriman" class="field-input"></select>
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
                                <input type="text" name="billing_first_name" id="billing_first_name" value="{{ old('billing_first_name') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Last name</label>
                                <input type="text" name="billing_last_name" id="billing_last_name" value="{{ old('billing_last_name') }}" class="field-input" placeholder="No Cabang">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Company</label>
                            <input type="text" name="billing_company" id="billing_company" value="{{ old('billing_company') }}" class="field-input">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Address line 1</label>
                                <input type="text" name="billing_address_1" id="billing_address_1" value="{{ old('billing_address_1') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Address line 2</label>
                                <input type="text" name="billing_address_2" id="billing_address_2" value="{{ old('billing_address_2') }}" class="field-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">City</label>
                                <input type="text" name="billing_city" id="billing_city" value="{{ old('billing_city') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Postcode / ZIP</label>
                                <input type="text" name="billing_postcode" id="billing_postcode" value="{{ old('billing_postcode') }}" class="field-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Country / Region</label>
                                <select name="billing_country" class="field-input">
                                    <option value="Indonesia" selected>Indonesia</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">State / County</label>
                                <input type="text" name="billing_state" id="billing_state" value="{{ old('billing_state') }}" class="field-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Email address</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Phone</label>
                                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="field-input">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Payment method</label>
                            <select name="payment_method" class="field-input">
                                <option value="manual" @selected(old('payment_method','manual')=='manual')>Manual</option>
                                <option value="transfer">Transfer</option>
                                <option value="cash">Cash</option>
                                <option value="N/A">N/A</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Sub District (Kelurahan)</label>
                                <input type="text" name="billing_kelurahan" id="billing_kelurahan" value="{{ old('billing_kelurahan') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">District (Kecamatan)</label>
                                <input type="text" name="billing_kecamatan" id="billing_kecamatan" value="{{ old('billing_kecamatan') }}" class="field-input">
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
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">First name</label>
                                <input type="text" name="shipping_first_name" id="shipping_first_name" value="{{ old('shipping_first_name') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Last name</label>
                                <input type="text" name="shipping_last_name" id="shipping_last_name" value="{{ old('shipping_last_name') }}" class="field-input">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Company</label>
                            <input type="text" name="shipping_company" id="shipping_company" value="{{ old('shipping_company') }}" class="field-input">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Address line 1</label>
                                <input type="text" name="shipping_address_1" id="shipping_address_1" value="{{ old('shipping_address_1') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Address line 2</label>
                                <input type="text" name="shipping_address_2" id="shipping_address_2" value="{{ old('shipping_address_2') }}" class="field-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">City</label>
                                <input type="text" name="shipping_city" id="shipping_city" value="{{ old('shipping_city') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">Postcode / ZIP</label>
                                <input type="text" name="shipping_postcode" id="shipping_postcode" value="{{ old('shipping_postcode') }}" class="field-input">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Country / Region</label>
                                <select name="shipping_country" class="field-input">
                                    <option value="Indonesia" selected>Indonesia</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">State / County</label>
                                <input type="text" name="shipping_state" id="shipping_state" value="{{ old('shipping_state') }}" class="field-input">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Phone</label>
                            <input type="text" name="shipping_phone" id="shipping_phone" value="{{ old('shipping_phone') }}" class="field-input">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="field-label">Sub District (Kelurahan)</label>
                                <input type="text" name="shipping_kelurahan" id="shipping_kelurahan" value="{{ old('shipping_kelurahan') }}" class="field-input">
                            </div>
                            <div>
                                <label class="field-label">District (Kecamatan)</label>
                                <input type="text" name="shipping_kecamatan" id="shipping_kecamatan" value="{{ old('shipping_kecamatan') }}" class="field-input">
                            </div>
                        </div>
                        <div>
                            <label class="field-label">Customer provided note</label>
                            <textarea name="catatan" rows="5" class="field-input bg-gray-100">{{ old('catatan') }}</textarea>
                        </div>
                        <div>
                            <label class="field-label">Weight</label>
                            <div class="flex items-center gap-2">
                                <input type="number"
                                    name="order_weight"
                                    value="{{ old('order_weight') }}"
                                    step="1"
                                    min="0"
                                    class="field-input"
                                    style="width:120px">
                                <span class="text-sm text-gray-500">g</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ITEMS: SKU dari products.label --}}
@php
    $oldItems = old('items', [[
        'product_id'   => '',
        'product_sku'  => '',
        'product_name' => '',
        'jenis'        => '',
        'kategori'     => '',
        'harga_jual'   => '',
        'qty'          => 1,
    ]]);
@endphp

<div class="bg-white border border-gray-200 rounded shadow-sm p-5 mb-5">
    <div class="flex justify-between items-center mb-3">
        <div>
            <div class="section-title mb-0 border-0 pb-0">Items / SKU</div>
            <p class="text-xs text-gray-400 mt-1">SKU diambil dari <strong>label</strong> master produk (fallback: sku → kode)</p>
        </div>
        <button type="button" onclick="addItemRow()" class="bg-emerald-600 text-white text-xs px-3 py-1.5 rounded hover:bg-emerald-700">
            + Tambah SKU
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-xs text-gray-500 border-b">
                    <th class="pb-2 pr-2 font-medium" style="min-width:220px">SKU (dari Label Produk) <span class="text-red-500">*</span></th>
                    <th class="pb-2 pr-2 font-medium" style="min-width:180px">Nama Produk / Modul <span class="text-red-500">*</span></th>
                    <th class="pb-2 pr-2 font-medium" style="min-width:100px">Jenis</th>
                    <th class="pb-2 pr-2 font-medium" style="min-width:120px">Kategori</th>
                    <th class="pb-2 pr-2 font-medium" style="min-width:110px">Harga Jual</th>
                    <th class="pb-2 pr-2 font-medium w-20">Qty <span class="text-red-500">*</span></th>
                    <th class="pb-2 w-8"></th>
                </tr>
            </thead>
            <tbody id="itemsBody">
                @foreach($oldItems as $i => $item)
                <tr class="item-row border-b border-gray-100">
                    <td class="py-2 pr-2">
                        <select class="sku-select" name="items[{{ $i }}][product_sku]" style="width:100%">
                            @if(!empty($item['product_sku']))
                                <option value="{{ $item['product_sku'] }}" selected>
                                    {{ $item['product_sku'] }}@if(!empty($item['product_name'])) — {{ $item['product_name'] }}@endif
                                </option>
                            @endif
                        </select>
                        <input type="hidden" name="items[{{ $i }}][product_id]" class="product-id" value="{{ $item['product_id'] ?? '' }}">
                    </td>
                    <td class="py-2 pr-2">
                        <input type="text" name="items[{{ $i }}][product_name]" required
                            class="field-input product-name"
                            placeholder="Nama produk"
                            value="{{ $item['product_name'] ?? '' }}">
                    </td>
                    <td class="py-2 pr-2">
                        <input type="text" name="items[{{ $i }}][jenis]"
                            class="field-input product-jenis"
                            placeholder="Jenis"
                            value="{{ $item['jenis'] ?? '' }}">
                    </td>
                    <td class="py-2 pr-2">
                        <input type="text" name="items[{{ $i }}][kategori]"
                            class="field-input product-kategori"
                            placeholder="Kategori"
                            value="{{ $item['kategori'] ?? '' }}">
                    </td>
                   <td class="py-2 pr-2">
    <input type="number"
           name="items[{{ $i }}][harga_jual]"
           step="0.01"
           min="0"
           class="field-input product-harga"
           placeholder="0"
           value="{{ $item['harga_jual'] ?? '' }}">
</td>
                    <td class="py-2 pr-2">
                        <input type="number" name="items[{{ $i }}][qty]" min="1" required class="field-input"
                            value="{{ $item['qty'] ?? 1 }}">
                    </td>
                    <td class="py-2 text-center">
                        <button type="button" onclick="removeItemRow(this)" class="text-red-500 text-lg font-bold">×</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

        <div class="flex gap-2">
            <button type="submit" class="bg-indigo-600 text-white px-5 py-2 rounded text-sm font-semibold hover:bg-indigo-700">
                Simpan Order
            </button>
            <a href="{{ route('order-manual-modul.manual') }}" class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-sm">Batal</a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let itemIndex = {{ count($oldItems) }};
const searchProductUrl = @json(route('order-manual-modul.search-products'));

/**
 * Inisialisasi Select2 untuk SKU
 */
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
                if (Array.isArray(data)) {
                    return { results: data };
                }
                return { results: data.results || [] };
            }
        }
    });

    // Saat produk dipilih
    $el.on('select2:select', function (e) {
        const d = e.params.data;
        const $row = $(this).closest('.item-row');

        $row.find('.product-id').val(d.product_id || '');
        $row.find('.product-name').val(d.name || '');
        $row.find('.product-jenis').val(d.jenis || '');
        $row.find('.product-kategori').val(d.kategori || '');

        // Simpan harga SATUAN
        const hargaSatuan = parseFloat(d.harga_jual || d.harga) || 0;
        $row.find('.sku-select').data('harga-satuan', hargaSatuan);

        // Simpan berat SATUAN
        $row.find('.sku-select').data('berat', parseFloat(d.berat) || 0);

        // Set harga awal (qty masih 1)
        $row.find('.product-harga').val(hargaSatuan);

        // Hitung ulang
        updateHargaJualRow($row);
        calculateTotalWeight();
    });

    // Saat SKU di-clear
    $el.on('select2:clear', function () {
        const $row = $(this).closest('.item-row');
        $row.find('.product-id').val('');
        $row.find('.product-name').val('');
        $row.find('.product-jenis').val('');
        $row.find('.product-kategori').val('');
        $row.find('.product-harga').val('');
        $row.find('.sku-select').data('harga-satuan', 0);
        $row.find('.sku-select').data('berat', 0);

        calculateTotalWeight();
    });
}

/**
 * Update Harga Jual = Harga Satuan × Qty
 */
function updateHargaJualRow($row) {
    const hargaSatuan = parseFloat($row.find('.sku-select').data('harga-satuan')) || 0;
    const qty = parseFloat($row.find('input[name*="[qty]"]').val()) || 1;

    $row.find('.product-harga').val(Math.round(hargaSatuan * qty));
}

/**
 * Hitung total berat (gram)
 */
function calculateTotalWeight() {
    let totalKg = 0;

    $('#itemsBody .item-row').each(function () {
        const beratSatuanKg = parseFloat($(this).find('.sku-select').data('berat')) || 0;
        const qty = parseFloat($(this).find('input[name*="[qty]"]').val()) || 0;
        totalKg += beratSatuanKg * qty;
    });

    const totalGram = totalKg * 1000;
    $('input[name="order_weight"]').val(totalGram > 0 ? Math.round(totalGram) : '');
}

/**
 * Event: saat Qty berubah → update harga jual + berat
 */
$(document).on('input change', 'input[name*="[qty]"]', function () {
    const $row = $(this).closest('.item-row');
    updateHargaJualRow($row);
    calculateTotalWeight();
});

/**
 * Event: jika user edit manual Harga Jual → anggap sebagai harga satuan baru
 */
$(document).on('change', '.product-harga', function () {
    const $row = $(this).closest('.item-row');
    const qty = parseFloat($row.find('input[name*="[qty]"]').val()) || 1;
    const hargaSekarang = parseFloat($(this).val()) || 0;

    // Simpan ulang sebagai harga satuan
    $row.find('.sku-select').data('harga-satuan', qty > 0 ? (hargaSekarang / qty) : hargaSekarang);
});

/**
 * Tambah baris item baru
 */
function addItemRow() {
    const index = document.querySelectorAll('#itemsBody .item-row').length;

    const html = `
    <tr class="item-row border-b border-gray-100">
        <td class="py-2 pr-2">
            <select class="sku-select" name="items[${index}][product_sku]" style="width:100%"></select>
            <input type="hidden" name="items[${index}][product_id]" class="product-id" value="">
        </td>
        <td class="py-2 pr-2">
            <input type="text" name="items[${index}][product_name]" required class="field-input product-name" placeholder="Nama produk">
        </td>
        <td class="py-2 pr-2">
            <input type="text" name="items[${index}][jenis]" class="field-input product-jenis" placeholder="Jenis">
        </td>
        <td class="py-2 pr-2">
            <input type="text" name="items[${index}][kategori]" class="field-input product-kategori" placeholder="Kategori">
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${index}][harga_jual]" step="0.01" min="0" class="field-input product-harga" placeholder="0">
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${index}][qty]" min="1" required class="field-input" value="1">
        </td>
        <td class="py-2 text-center">
            <button type="button" onclick="removeItemRow(this)" class="text-red-500 text-lg font-bold">×</button>
        </td>
    </tr>`;

    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', html);
    initSkuSelect(document.querySelector('#itemsBody .item-row:last-child .sku-select'));
}

/**
 * Hapus baris
 */
function removeItemRow(btn) {
    if (document.querySelectorAll('#itemsBody .item-row').length <= 1) {
        alert('Minimal 1 item');
        return;
    }
    const $row = $(btn).closest('tr');
    $row.find('.sku-select').select2('destroy');
    $row.remove();
    calculateTotalWeight();
}

/**
 * Copy Billing → Shipping
 */
function copyBillingToShipping() {
    const map = {
        shipping_first_name: 'billing_first_name',
        shipping_last_name:  'billing_last_name',
        shipping_company:    'billing_company',
        shipping_address_1:  'billing_address_1',
        shipping_address_2:  'billing_address_2',
        shipping_city:       'billing_city',
        shipping_postcode:   'billing_postcode',
        shipping_state:      'billing_state',
        shipping_phone:      'phone',
        shipping_kelurahan:  'billing_kelurahan',
        shipping_kecamatan:  'billing_kecamatan',
    };

    const company = document.getElementById('billing_company').value
                 || document.getElementById('customer_name').value;
    document.getElementById('shipping_company').value = company;

    for (const [ship, bill] of Object.entries(map)) {
        if (ship === 'shipping_company') continue;
        const el = document.getElementById(ship);
        const src = document.getElementById(bill) || document.querySelector(`[name="${bill}"]`);
        if (el && src) el.value = src.value;
    }
}

$(document).ready(function () {
    // Inisialisasi semua select SKU
    $('.sku-select').each(function () {
        initSkuSelect(this);
    });

    // Customer Select2
    $('#customerSelect').select2({
        placeholder: '— Pilih Unit / Customer —',
        allowClear: true,
        width: '100%'
    });

    $('#customerSelect').on('change', function () {
        const opt = $(this).find(':selected');

        if (!opt.val()) {
            $('#customer_name, #billing_first_name, #billing_last_name, #billing_company, #billing_address_1, #billing_address_2, #billing_city, #billing_postcode, #billing_state, #phone, #email').val('');
            return;
        }

        const displayName  = opt.data('display-name') || '';
        const firstName    = opt.data('first-name') || opt.data('billing-first') || '';
        const lastName     = opt.data('last-name') || opt.data('billing-last') || '';
        const email        = opt.data('email') || '';
        const phone        = opt.data('phone') || '';
        const company      = opt.data('company') || displayName;
        const address1     = opt.data('address-1') || '';
        const address2     = opt.data('address-2') || '';
        const city         = opt.data('city') || '';
        const postcode     = opt.data('postcode') || '';
        const state        = opt.data('state') || '';
        const billingFirst = opt.data('billing-first') || firstName;
        const billingLast  = opt.data('billing-last') || lastName;

        $('#customer_name').val(displayName || company);
        $('#billing_first_name').val(billingFirst);
        $('#billing_last_name').val(billingLast);
        $('#billing_company').val(company);
        $('#billing_address_1').val(address1);
        $('#billing_address_2').val(address2);
        $('#billing_city').val(city);
        $('#billing_postcode').val(postcode);
        $('#billing_state').val(state);
        $('#phone').val(phone);
        $('#email').val(email);

        $('#shipping_first_name').val(billingFirst);
        $('#shipping_last_name').val(billingLast);
        $('#shipping_company').val(company);
        $('#shipping_address_1').val(address1);
        $('#shipping_address_2').val(address2);
        $('#shipping_city').val(city);
        $('#shipping_postcode').val(postcode);
        $('#shipping_state').val(state);
        $('#shipping_phone').val(phone);
    });

    // Status Kirim & Ekspedisi
    const statusEl    = document.getElementById('statusKirim');
    const ekspedisiEl = document.getElementById('ekspedisi');
    const serviceEl   = document.getElementById('servicePengiriman');
    const form        = document.getElementById('formManualModul');

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
            ekspedisiEl.innerHTML = '<option value="Lion Parcel">Lion Parcel</option><option value="JNE">JNE</option>';
            fillService(ekspedisiEl.value === 'JNE' ? [{v:'REG',l:'REG'}] : lion, ekspedisiEl.value === 'JNE' ? 'REG' : 'REGPACK');
        }
    }

    statusEl.addEventListener('change', applyStatus);
    ekspedisiEl.addEventListener('change', function () {
        fillService(ekspedisiEl.value === 'JNE' ? [{v:'REG',l:'REG'}] : lion, ekspedisiEl.value === 'JNE' ? 'REG' : 'REGPACK');
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