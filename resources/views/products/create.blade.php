@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Tambah Produk
        </h1>

        <p class="text-sm text-gray-500 mt-1">
            Tambahkan sparepart atau produk ke master inventory.
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('products.store') }}"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
    >

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kode Produk
                </label>

                <input
                    type="text"
                    name="code"
                    value="{{ old('code') }}"
                    required
                    maxlength="20"
                    placeholder="Contoh: BRG-001"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kategori
                </label>

                <input
                    type="text"
                    name="category_name"
                    value="{{ old('category_name') }}"
                    maxlength="255"
                    placeholder="Contoh: Oli Mesin"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >

                <p class="text-xs text-gray-500 mt-1">
                    Ketik kategori secara manual. Jika belum ada,
                    kategori otomatis dibuat di Master Kategori.
                </p>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Produk
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    placeholder="Contoh: Oli Mesin 10W-40"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Brand
                </label>

                <input
                    type="text"
                    name="brand"
                    value="{{ old('brand') }}"
                    placeholder="Opsional"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Barcode
                </label>

                <input
                    type="text"
                    name="barcode"
                    value="{{ old('barcode') }}"
                    placeholder="Opsional"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Satuan
                </label>

                <input
                    type="text"
                    name="unit"
                    value="{{ old('unit', 'PCS') }}"
                    required
                    placeholder="PCS"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tipe Stok
                </label>

                <select
                    name="stock_type"
                    required
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
                    <option
                        value="STOCK"
                        @selected(old('stock_type', 'STOCK') === 'STOCK')
                    >
                        STOCK
                    </option>

                    <option
                        value="NON_STOCK"
                        @selected(old('stock_type') === 'NON_STOCK')
                    >
                        NON STOCK
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga Beli
                </label>

                <input
                    type="number"
                    name="default_purchase_price"
                    value="{{ old('default_purchase_price', 0) }}"
                    required
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga Jual
                </label>

                <input
                    type="number"
                    name="default_selling_price"
                    value="{{ old('default_selling_price', 0) }}"
                    required
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Minimum Stock
                </label>

                <input
                    type="number"
                    name="minimum_stock"
                    value="{{ old('minimum_stock', 0) }}"
                    required
                    min="0"
                    step="0.001"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan
                </label>

                <textarea
                    name="notes"
                    rows="3"
                    placeholder="Opsional"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >{{ old('notes') }}</textarea>
            </div>

            <div class="md:col-span-2">

                <label class="inline-flex items-center gap-2">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', true))
                        class="rounded border-gray-300"
                    >

                    <span class="text-sm text-gray-700">
                        Produk aktif
                    </span>

                </label>

            </div>

        </div>

        <div class="flex justify-end gap-3 mt-8">

            <a
                href="{{ route('products.index') }}"
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm"
            >
                Batal
            </a>

            <button
                type="submit"
                class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700"
            >
                Simpan Produk
            </button>

        </div>

    </form>

</div>

@endsection