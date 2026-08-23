@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Produk</h1>
            <p class="mt-1 text-sm text-gray-500">
                Informasi lengkap sparepart atau barang bengkel.
            </p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('products.edit', $product) }}"
               class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
                Edit
            </a>

            <a href="{{ route('products.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50">
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $product->name }}
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ $product->code }}
                    </p>
                </div>

                @if ($product->is_active)
                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                        Aktif
                    </span>
                @else
                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                        Tidak Aktif
                    </span>
                @endif

            </div>
        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Kode Produk</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $product->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Kategori</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $product->category->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Barcode</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $product->barcode ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Nama Produk</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $product->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Merk</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $product->brand ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Satuan</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $product->unit }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Tipe Stok</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $product->stock_type === 'STOCK' ? 'Stock' : 'Non Stock' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Minimum Stock</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ number_format($product->minimum_stock, 0, ',', '.') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Harga Beli Default</p>
                <p class="mt-1 text-sm text-gray-900">
                    Rp {{ number_format($product->default_purchase_price, 2, ',', '.') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Harga Jual Default</p>
                <p class="mt-1 text-sm text-gray-900">
                    Rp {{ number_format($product->default_selling_price, 2, ',', '.') }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs font-medium text-gray-500 uppercase">Catatan</p>
                <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                    {{ $product->notes ?? '-' }}
                </p>
            </div>

        </div>

    </div>

</div>
@endsection
