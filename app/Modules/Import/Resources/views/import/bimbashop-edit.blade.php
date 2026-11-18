@extends('layouts.panel')

@section('title', 'Edit Order biMBA Shop')

@section('content')
@php
    $inp = 'w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-[#162749] focus:border-[#E85D2A] focus:ring-2 focus:ring-[#E85D2A]/20 outline-none';
    $lbl = 'block text-xs font-semibold text-[#162749]/70 mb-1.5';
    $statusList = ['processing', 'completed', 'on-hold', 'pending', 'cancelled', 'refunded', 'failed'];
    $statusNow  = preg_replace('/^wc-/', '', strtolower((string) old('status', $order->status)));
    if ($statusNow !== '' && ! in_array($statusNow, $statusList, true)) {
        $statusList[] = $statusNow;
    }
@endphp

<div class="max-w-4xl mx-auto">
    @include('partials.page-header', [
        'title'     => 'Edit Order #' . $order->order_id,
        'subtitle'  => 'Ubah data order hasil import biMBA Shop.',
        'back'      => route('import.bimbashop'),
        'backLabel' => 'Kembali ke Data Order',
    ])

    @if ($errors->any())
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-700 text-sm px-4 py-3">
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('import.bimbashop.update', $order->id) }}" method="POST"
          class="bg-white rounded-2xl shadow-card p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="{{ $lbl }}" for="order_id">Order ID</label>
                <input id="order_id" type="text" name="order_id" value="{{ old('order_id', $order->order_id) }}" class="{{ $inp }}" required>
            </div>
            <div>
                <label class="{{ $lbl }}" for="order_date">Order Date</label>
                <input id="order_date" type="datetime-local" name="order_date"
                       value="{{ old('order_date', optional($order->order_date)->format('Y-m-d\TH:i')) }}" class="{{ $inp }}" required>
            </div>
            <div>
                <label class="{{ $lbl }}" for="item_sku">Item SKU</label>
                <input id="item_sku" type="text" name="item_sku" value="{{ old('item_sku', $order->item_sku) }}" class="{{ $inp }}" required>
            </div>
            <div>
                <label class="{{ $lbl }}" for="status">Status</label>
                <select id="status" name="status" class="{{ $inp }}" required>
                    @foreach ($statusList as $st)
                        <option value="{{ $st }}" {{ $statusNow === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="{{ $lbl }}" for="item_name">Item Name</label>
                <input id="item_name" type="text" name="item_name" value="{{ old('item_name', $order->item_name) }}" class="{{ $inp }}" required>
            </div>
            <div>
                <label class="{{ $lbl }}" for="item_price">Item Price</label>
                <input id="item_price" type="number" step="any" min="0" name="item_price" value="{{ old('item_price', $order->item_price) }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="item_qty">Qty</label>
                <input id="item_qty" type="number" min="0" name="item_qty" value="{{ old('item_qty', $order->item_qty) }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="order_total">Order Total</label>
                <input id="order_total" type="number" step="any" name="order_total" value="{{ old('order_total', $order->order_total) }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="payment_method">Payment Method</label>
                <input id="payment_method" type="text" name="payment_method" value="{{ old('payment_method', $order->payment_method) }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="billing_first_name">Billing First Name</label>
                <input id="billing_first_name" type="text" name="billing_first_name" value="{{ old('billing_first_name', $order->billing_first_name) }}" class="{{ $inp }}">
            </div>
            <div>
                <label class="{{ $lbl }}" for="billing_last_name">Billing Last Name</label>
                <input id="billing_last_name" type="text" name="billing_last_name" value="{{ old('billing_last_name', $order->billing_last_name) }}" class="{{ $inp }}">
            </div>
        </div>

        <div class="mt-6 flex items-center gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-[#1D3361] hover:bg-[#162749] transition-colors text-white text-sm font-semibold px-6 py-2.5 rounded-xl">
                Simpan perubahan
            </button>
            <a href="{{ route('import.bimbashop') }}" class="text-sm font-medium text-[#162749]/60 hover:text-[#D14E1F] px-3 py-2.5">Batal</a>
        </div>
    </form>
</div>
@endsection
