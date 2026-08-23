@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Edit Work Order
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui customer, jasa, sparepart, dan pekerjaan Work Order.
            </p>
        </div>

        <a href="{{ route('work-orders.show', $workOrder) }}"
           class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-700">
            <div class="font-semibold mb-2">Periksa data berikut:</div>

            <ul class="list-disc ml-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('work-orders.update', $workOrder) }}"
          class="space-y-6">

        @csrf
        @method('PUT')

        <div class="bg-white rounded-xl border shadow-sm p-6">

            <h2 class="text-lg font-semibold">
                Informasi Work Order
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mt-5">

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Kode WO *
                    </label>

                    <input type="text"
                           name="code"
                           value="{{ old('code', $workOrder->code) }}"
                           required
                           class="w-full rounded-lg border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Status *
                    </label>

                    <select name="status"
                            class="w-full rounded-lg border-gray-300">

                        @foreach([
                            'OPEN',
                            'IN_PROGRESS',
                            'WAITING_PARTS',
                            'COMPLETED',
                            'CANCELLED'
                        ] as $status)

                            <option value="{{ $status }}"
                                {{ $workOrder->status === $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>

                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipe WO *
                    </label>

                    <select name="type"
                            class="w-full rounded-lg border-gray-300">

                        <option value="REGULAR"
                            {{ $workOrder->type === 'REGULAR' ? 'selected' : '' }}>
                            REGULAR
                        </option>

                        <option value="WARRANTY"
                            {{ $workOrder->type === 'WARRANTY' ? 'selected' : '' }}>
                            WARRANTY
                        </option>

                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Dibuka
                    </label>

                    <input type="datetime-local"
                           name="opened_at"
                           value="{{ optional($workOrder->opened_at)->format('Y-m-d\TH:i') }}"
                           class="w-full rounded-lg border-gray-300">
                </div>

            </div>

        </div>


        <div class="bg-white rounded-xl border shadow-sm p-6">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold">
                        Customer & Kendaraan
                    </h2>

                    <p class="text-sm text-gray-500">
                        Data motor berasal langsung dari customer.
                    </p>
                </div>

                <button type="button"
                        onclick="setCustomerMode('NEW')"
                        class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm">
                    + Customer Baru
                </button>

            </div>

            <div class="mt-5">

                <label class="block text-sm font-medium mb-1">
                    Customer *
                </label>

                <select id="customer_id"
                        name="customer_id"
                        onchange="setCustomerMode('EXISTING')"
                        class="w-full rounded-lg border-gray-300">

                    <option value="">
                        -- Pilih Customer --
                    </option>

                    @foreach($customers as $customer)

                        <option value="{{ $customer->id }}"
                            {{ $workOrder->customer_id == $customer->id ? 'selected' : '' }}>

                            {{ $customer->code }} - {{ $customer->name }}

                            @if($customer->plate_number)
                                | {{ $customer->plate_number }}
                            @endif

                        </option>

                    @endforeach

                </select>

                <input type="hidden"
                       id="customer_mode"
                       name="customer_mode"
                       value="{{ old('customer_mode', 'EXISTING') }}">

            </div>

            <div id="newCustomerBox"
                 class="hidden mt-6 border-t pt-6">

                <div class="flex items-center justify-between mb-4">

                    <h3 class="font-semibold">
                        Customer Baru
                    </h3>

                    <button type="button"
                            onclick="setCustomerMode('EXISTING')"
                            class="text-sm text-blue-600">
                        Pilih Customer Existing
                    </button>

                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Kode Customer
                        </label>

                        <input name="customer_code"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Nama Customer *
                        </label>

                        <input name="customer_name"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            No. Telepon
                        </label>

                        <input name="customer_phone"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Plat Nomor
                        </label>

                        <input name="customer_plate_number"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Brand Motor
                        </label>

                        <input name="customer_brand"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Tipe Motor
                        </label>

                        <input name="customer_type"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div class="md:col-span-2">

                        <label class="block text-sm font-medium mb-1">
                            Catatan
                        </label>

                        <textarea name="customer_notes"
                                  rows="2"
                                  class="w-full rounded-lg border-gray-300"></textarea>

                    </div>

                </div>

            </div>

        </div>


        <div class="bg-white rounded-xl border shadow-sm p-6">

            <h2 class="text-lg font-semibold">
                Keluhan & Diagnosa
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Keluhan
                    </label>

                    <textarea name="complaint"
                              rows="4"
                              class="w-full rounded-lg border-gray-300">{{ old('complaint', $workOrder->complaint) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Diagnosa
                    </label>

                    <textarea name="diagnosis"
                              rows="4"
                              class="w-full rounded-lg border-gray-300">{{ old('diagnosis', $workOrder->diagnosis) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Catatan
                    </label>

                    <textarea name="notes"
                              rows="4"
                              class="w-full rounded-lg border-gray-300">{{ old('notes', $workOrder->notes) }}</textarea>
                </div>

            </div>

        </div>


        <div class="bg-white rounded-xl border shadow-sm p-6">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold">
                        Item Pekerjaan
                    </h2>

                    <p class="text-sm text-gray-500">
                        Item langsung diedit dari Work Order.
                    </p>
                </div>

                <button type="button"
                        onclick="addItem()"
                        class="px-4 py-2 rounded-lg bg-slate-900 text-white">
                    + Tambah Item
                </button>

            </div>

            <div id="itemsContainer"
                 class="space-y-5 mt-6"></div>

            <div class="mt-8 flex justify-end">

                <div class="w-full md:w-96">

                    <label class="block text-sm font-medium mb-1">
                        Discount WO
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="discount"
                           value="{{ old('discount', $workOrder->discount) }}"
                           class="w-full rounded-lg border-gray-300 text-right">

                </div>

            </div>

        </div>


        <div class="flex justify-end gap-3">

            <a href="{{ route('work-orders.show', $workOrder) }}"
               class="px-5 py-3 rounded-lg border bg-white">
                Batal
            </a>

            <button type="submit"
                    class="px-6 py-3 rounded-lg bg-blue-600 text-white font-semibold">
                Simpan Perubahan
            </button>

        </div>


    {{-- =========================================================
         PAYMENT
    ========================================================== --}}

    @php
        $totalPaid = (float) $workOrder->payments()->sum('amount');

        $grandTotal = (float) $workOrder->grand_total;

        $remainingPayment = max(
            0,
            $grandTotal - $totalPaid
        );
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mt-6">

        <div class="mb-5">

            <h2 class="text-lg font-semibold text-gray-900">
                Pembayaran
            </h2>

            <p class="text-sm text-gray-500 mt-1">
                Tambahkan pembayaran langsung dari Work Order.
            </p>

        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

            {{-- Sudah Dibayar --}}
            <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">

                <p class="text-xs text-gray-500">
                    Sudah Dibayar
                </p>

                <p class="mt-1 text-lg font-bold text-gray-900">
                    Rp
                    {{ number_format(
                        $totalPaid,
                        0,
                        ',',
                        '.'
                    ) }}
                </p>

            </div>


            {{-- Sisa Tagihan --}}
            <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">

                <p class="text-xs text-yellow-700">
                    Sisa Tagihan
                </p>

                <p
                    id="payment_remaining"
                    class="mt-1 text-lg font-bold text-yellow-800"
                >
                    Rp
                    {{ number_format(
                        $remainingPayment,
                        0,
                        ',',
                        '.'
                    ) }}
                </p>

            </div>


            {{-- Status --}}
            <div class="rounded-lg bg-green-50 border border-green-200 p-4">

                <p class="text-xs text-green-700">
                    Status Pembayaran
                </p>

                <p
                    id="payment_status"
                    class="mt-1 text-lg font-bold {{ $remainingPayment <= 0 ? 'text-green-800' : 'text-yellow-800' }}"
                >
                    {{ $remainingPayment <= 0 ? 'LUNAS' : 'BELUM LUNAS' }}
                </p>

            </div>

        </div>


        @if($remainingPayment > 0)

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">

                {{-- Nominal --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nominal Pembayaran
                    </label>

                    <input
                        type="number"
                        name="payment_amount"
                        id="payment_amount"
                        step="0.01"
                        min="0"
                        max="{{ $remainingPayment }}"
                        value="{{ old('payment_amount') }}"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                        placeholder="Maks. {{ number_format($remainingPayment, 0, ',', '.') }}"
                    >

                    @error('payment_amount')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Metode --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Metode Pembayaran
                    </label>

                    <select
                        name="payment_method"
                        id="payment_method"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >

                        <option value="">
                            -- Pilih Metode --
                        </option>

                        <option
                            value="CASH"
                            @selected(old('payment_method') === 'CASH')
                        >
                            Cash
                        </option>

                        <option
                            value="BANK_TRANSFER"
                            @selected(old('payment_method') === 'BANK_TRANSFER')
                        >
                            Bank Transfer
                        </option>

                        <option
                            value="DEBIT_CARD"
                            @selected(old('payment_method') === 'DEBIT_CARD')
                        >
                            Debit Card
                        </option>

                        <option
                            value="CREDIT_CARD"
                            @selected(old('payment_method') === 'CREDIT_CARD')
                        >
                            Credit Card
                        </option>

                        <option
                            value="QRIS"
                            @selected(old('payment_method') === 'QRIS')
                        >
                            QRIS
                        </option>

                        <option
                            value="OTHER"
                            @selected(old('payment_method') === 'OTHER')
                        >
                            Other
                        </option>

                    </select>

                    @error('payment_method')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>


                {{-- Tanggal --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Pembayaran
                    </label>

                    <input
                        type="datetime-local"
                        name="payment_paid_at"
                        id="payment_paid_at"
                        value="{{ old(
                            'payment_paid_at',
                            now()->format('Y-m-d\TH:i')
                        ) }}"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >

                    @error('payment_paid_at')
                        <p class="mt-1 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </div>

            </div>


            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">

                {{-- Reference --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        No. Referensi
                    </label>

                    <input
                        type="text"
                        name="payment_reference_number"
                        value="{{ old('payment_reference_number') }}"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                        placeholder="Opsional"
                    >

                </div>


                {{-- Notes --}}
                <div>

                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Catatan Pembayaran
                    </label>

                    <input
                        type="text"
                        name="payment_notes"
                        value="{{ old('payment_notes') }}"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                        placeholder="Opsional"
                    >

                </div>

            </div>


            {{-- Preview --}}
            <div class="mt-5 rounded-lg bg-blue-50 border border-blue-200 p-4">

                <p class="text-sm text-blue-800">

                    Setelah pembayaran baru disimpan,
                    sisa tagihan akan otomatis dihitung kembali.

                </p>

            </div>

        @else

            <div class="mt-5 rounded-lg bg-green-50 border border-green-200 p-4">

                <div class="font-semibold text-green-800">
                    Work Order ini sudah LUNAS.
                </div>

                <div class="text-sm text-green-700 mt-1">
                    Tidak ada pembayaran tambahan yang diperlukan.
                </div>

            </div>

        @endif

    </div>

    </form>

</div>


<script>

let itemIndex = 0;

const services = @json($services);
const products = @json($products);
const existingItems = @json($workOrder->items);

function setCustomerMode(mode)
{
    const hidden = document.getElementById('customer_mode');
    const box = document.getElementById('newCustomerBox');
    const select = document.getElementById('customer_id');

    hidden.value = mode;

    if (mode === 'NEW') {
        box.classList.remove('hidden');
        select.value = '';
    } else {
        box.classList.add('hidden');
    }
}

function serviceOptions(selected = '')
{
    let html = '<option value="">-- Pilih Jasa --</option>';

    services.forEach(service => {

        html += `
            <option value="${service.id}"
                ${String(selected) === String(service.id) ? 'selected' : ''}>
                ${service.code} - ${service.name}
            </option>
        `;

    });

    return html;
}

function productOptions(selected = '')
{
    let html = '<option value="">-- Pilih Sparepart --</option>';

    products.forEach(product => {

        html += `
            <option value="${product.id}"
                ${String(selected) === String(product.id) ? 'selected' : ''}>
                ${product.code} - ${product.name}
            </option>
        `;

    });

    return html;
}

function addItem(item = null)
{
    const index = itemIndex++;

    const type = item?.item_type ?? 'SERVICE';

    const mode = item
        ? (
            type === 'SERVICE'
                ? (item.service_id ? 'EXISTING' : 'NEW')
                : (item.product_id ? 'EXISTING' : 'NEW')
        )
        : 'EXISTING';

    const html = `
        <div class="border rounded-xl p-5 item-row"
             data-index="${index}">

            <div class="flex items-center justify-between mb-5">

                <div class="font-semibold">
                    Item #${index + 1}
                </div>

                <button type="button"
                        onclick="this.closest('.item-row').remove()"
                        class="text-red-600 text-sm">
                    Hapus
                </button>

            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Tipe
                    </label>

                    <select name="items[${index}][item_type]"
                            onchange="changeItemType(${index}, this.value)"
                            class="w-full rounded-lg border-gray-300">

                        <option value="SERVICE"
                            ${type === 'SERVICE' ? 'selected' : ''}>
                            Jasa
                        </option>

                        <option value="PRODUCT"
                            ${type === 'PRODUCT' ? 'selected' : ''}>
                            Sparepart
                        </option>

                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Sumber
                    </label>

                    <select name="items[${index}][mode]"
                            onchange="changeItemMode(${index}, this.value)"
                            class="w-full rounded-lg border-gray-300">

                        <option value="EXISTING"
                            ${mode === 'EXISTING' ? 'selected' : ''}>
                            Master
                        </option>

                        <option value="NEW"
                            ${mode === 'NEW' ? 'selected' : ''}>
                            Tambah Baru
                        </option>

                    </select>
                </div>

                <div class="md:col-span-2"
                     id="existing-${index}">

                    <label class="block text-sm font-medium mb-1">
                        Item
                    </label>

                    <select name="items[${index}][service_id]"
                            id="service-select-${index}"
                            class="${type === 'PRODUCT' ? 'hidden' : ''} w-full rounded-lg border-gray-300">

                        ${serviceOptions(item?.service_id ?? '')}

                    </select>

                    <select name="items[${index}][product_id]"
                            id="product-select-${index}"
                            class="${type === 'SERVICE' ? 'hidden' : ''} w-full rounded-lg border-gray-300">

                        ${productOptions(item?.product_id ?? '')}

                    </select>

                </div>

            </div>


            <div id="new-service-${index}"
                 class="${mode === 'NEW' && type === 'SERVICE' ? '' : 'hidden'} mt-5 border-t pt-5">

                <div class="font-semibold mb-4">
                    Tambahkan Jasa Servis
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Kode Jasa
                        </label>

                        <input name="items[${index}][service_code]"
                               value="${item?.service?.code ?? ''}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Nama Jasa
                        </label>

                        <input name="items[${index}][service_name]"
                               value="${item?.service?.name ?? item?.item_name ?? ''}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Harga Default
                        </label>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="items[${index}][service_default_price]"
                               value="${item?.service?.default_price ?? item?.unit_price ?? 0}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Estimasi Durasi
                        </label>

                        <input type="number"
                               min="0"
                               name="items[${index}][service_estimated_duration]"
                               value="${item?.service?.estimated_duration ?? 0}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div class="md:col-span-4">
                        <label class="block text-sm font-medium mb-1">
                            Deskripsi
                        </label>

                        <textarea name="items[${index}][service_description]"
                                  rows="2"
                                  class="w-full rounded-lg border-gray-300">${item?.service?.description ?? ''}</textarea>
                    </div>

                </div>

            </div>


            <div id="new-product-${index}"
                 class="${mode === 'NEW' && type === 'PRODUCT' ? '' : 'hidden'} mt-5 border-t pt-5">

                <div class="font-semibold mb-4">
                    Tambahkan Sparepart
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Kode Produk
                        </label>

                        <input name="items[${index}][product_code]"
                               value="${item?.product?.code ?? ''}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Kategori
                        </label>

                        <input name="items[${index}][product_category_name]"
                               value="${item?.product?.category?.name ?? ''}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Barcode
                        </label>

                        <input name="items[${index}][product_barcode]"
                               value="${item?.product?.barcode ?? ''}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Nama Produk
                        </label>

                        <input name="items[${index}][product_name]"
                               value="${item?.product?.name ?? item?.item_name ?? ''}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Brand
                        </label>

                        <input name="items[${index}][product_brand]"
                               value="${item?.product?.brand ?? ''}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Satuan
                        </label>

                        <input name="items[${index}][product_unit]"
                               value="${item?.product?.unit ?? 'PCS'}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Tipe Stock
                        </label>

                        <select name="items[${index}][product_stock_type]"
                                class="w-full rounded-lg border-gray-300">

                            <option value="STOCK"
                                ${item?.product?.stock_type === 'STOCK' ? 'selected' : ''}>
                                STOCK
                            </option>

                            <option value="NON_STOCK"
                                ${item?.product?.stock_type === 'NON_STOCK' ? 'selected' : ''}>
                                NON STOCK
                            </option>

                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Harga Beli / Modal
                        </label>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="items[${index}][product_purchase_price]"
                               value="${item?.product?.default_purchase_price ?? item?.unit_cost ?? 0}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Harga Jual
                        </label>

                        <input type="number"
                               step="0.01"
                               min="0"
                               name="items[${index}][product_selling_price]"
                               value="${item?.product?.default_selling_price ?? item?.unit_price ?? 0}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Minimum Stock
                        </label>

                        <input type="number"
                               step="0.001"
                               min="0"
                               name="items[${index}][product_minimum_stock]"
                               value="${item?.product?.minimum_stock ?? 0}"
                               class="w-full rounded-lg border-gray-300">
                    </div>

                </div>

            </div>


            <div class="mt-5 grid grid-cols-1 md:grid-cols-4 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Qty
                    </label>

                    <input type="number"
                           step="0.001"
                           min="0.001"
                           name="items[${index}][quantity]"
                           value="${item?.quantity ?? 1}"
                           class="w-full rounded-lg border-gray-300">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Discount
                    </label>

                    <input type="number"
                           step="0.01"
                           min="0"
                           name="items[${index}][discount_amount]"
                           value="${item?.discount_amount ?? 0}"
                           class="w-full rounded-lg border-gray-300">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        Catatan Item
                    </label>

                    <input name="items[${index}][notes]"
                           value="${item?.notes ?? ''}"
                           class="w-full rounded-lg border-gray-300">
                </div>

            </div>

        </div>
    `;

    document
        .getElementById('itemsContainer')
        .insertAdjacentHTML('beforeend', html);

    changeItemType(index, type);
    changeItemMode(index, mode);
}

function changeItemType(index, type)
{
    const serviceSelect =
        document.getElementById(`service-select-${index}`);

    const productSelect =
        document.getElementById(`product-select-${index}`);

    if (type === 'SERVICE') {

        serviceSelect.classList.remove('hidden');
        productSelect.classList.add('hidden');

    } else {

        serviceSelect.classList.add('hidden');
        productSelect.classList.remove('hidden');
    }

    const row =
        document.querySelector(
            `[data-index="${index}"]`
        );

    const mode =
        row.querySelector(
            `select[name="items[${index}][mode]"]`
        ).value;

    changeItemMode(index, mode);
}

function changeItemMode(index, mode)
{
    const row =
        document.querySelector(
            `[data-index="${index}"]`
        );

    const type =
        row.querySelector(
            `select[name="items[${index}][item_type]"]`
        ).value;

    const existing =
        document.getElementById(`existing-${index}`);

    const serviceBox =
        document.getElementById(`new-service-${index}`);

    const productBox =
        document.getElementById(`new-product-${index}`);

    if (mode === 'NEW') {

        existing.classList.add('hidden');

        if (type === 'SERVICE') {
            serviceBox.classList.remove('hidden');
            productBox.classList.add('hidden');
        } else {
            serviceBox.classList.add('hidden');
            productBox.classList.remove('hidden');
        }

    } else {

        existing.classList.remove('hidden');
        serviceBox.classList.add('hidden');
        productBox.classList.add('hidden');
    }
}

setCustomerMode('EXISTING');

existingItems.forEach(item => {
    addItem(item);
});

if (existingItems.length === 0) {
    addItem();
}

</script>

@endsection
