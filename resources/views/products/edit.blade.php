@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">
            Edit Produk
        </h1>

        <p class="text-sm text-gray-500 mt-1">
            Perbarui data master produk.
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
        action="{{ route('products.update', $product) }}"
        class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
    >

        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            {{-- KODE --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kode Produk
                </label>

                <input
                    type="text"
                    name="code"
                    value="{{ old('code', $product->code) }}"
                    required
                    maxlength="20"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- KATEGORI MANUAL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Kategori
                </label>

                <input
                    type="text"
                    name="category_name"
                    value="{{ old('category_name', optional($product->category)->name) }}"
                    required
                    maxlength="255"
                    placeholder="Contoh: Oli Mesin"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >

                <p class="text-xs text-gray-500 mt-1">
                    Ketik kategori. Jika belum ada, sistem akan membuat
                    kategori baru secara otomatis.
                </p>
            </div>

            {{-- NAMA --}}
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Nama Produk
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', $product->name) }}"
                    required
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- BRAND --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Brand
                </label>

                <input
                    type="text"
                    name="brand"
                    value="{{ old('brand', $product->brand) }}"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- BARCODE --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Barcode
                </label>

                <input
                    type="text"
                    name="barcode"
                    value="{{ old('barcode', $product->barcode) }}"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- UNIT --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Satuan
                </label>

                <input
                    type="text"
                    name="unit"
                    value="{{ old('unit', $product->unit) }}"
                    required
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- STOCK TYPE --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Tipe Stok
                </label>

                <select
                    name="stock_type"
                    required
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
                    <option value="STOCK"
                        @selected(old('stock_type', $product->stock_type) === 'STOCK')>
                        STOCK
                    </option>

                    <option value="NON_STOCK"
                        @selected(old('stock_type', $product->stock_type) === 'NON_STOCK')>
                        NON STOCK
                    </option>
                </select>
            </div>

            {{-- HARGA BELI --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga Beli
                </label>

                <input
                    type="number"
                    name="default_purchase_price"
                    value="{{ old('default_purchase_price', $product->default_purchase_price) }}"
                    required
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- HARGA JUAL --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Harga Jual
                </label>

                <input
                    type="number"
                    name="default_selling_price"
                    value="{{ old('default_selling_price', $product->default_selling_price) }}"
                    required
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- MINIMUM STOCK --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Minimum Stock
                </label>

                <input
                    type="number"
                    name="minimum_stock"
                    value="{{ old('minimum_stock', $product->minimum_stock) }}"
                    required
                    min="0"
                    step="0.001"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >
            </div>

            {{-- NOTES --}}
            <div class="md:col-span-2">

                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Catatan
                </label>

                <textarea
                    name="notes"
                    rows="3"
                    class="w-full rounded-lg border-gray-300 text-sm"
                >{{ old('notes', $product->notes) }}</textarea>

            </div>

            {{-- ACTIVE --}}
            <div class="md:col-span-2">

                <label class="inline-flex items-center gap-2">

                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked(old('is_active', $product->is_active))
                        class="rounded border-gray-300"
                    >

                    <span class="text-sm text-gray-700">
                        Produk aktif
                    </span>

                </label>

            </div>

        </div>

        <div class="flex justify-between items-center mt-8">

            <a
                href="{{ route('products.show', $product) }}"
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm"
            >
                Batal
            </a>

            <button
                type="submit"
                class="px-5 py-2 rounded-lg bg-blue-600 text-white text-sm font-medium hover:bg-blue-700"
            >
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>

@endsection