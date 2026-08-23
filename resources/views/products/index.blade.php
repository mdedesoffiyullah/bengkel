@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Products
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data produk dan spare part bengkel.
            </p>
        </div>

        <a href="{{ route('products.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">
            + Tambah Produk
        </a>

    </div>


    {{-- Table Card --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="p-6 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="font-semibold text-gray-900">
                        Daftar Produk
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Total {{ $products->total() }} produk
                    </p>
                </div>

                <form method="GET" action="{{ route('products.index') }}">

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari produk..."
                        class="w-64 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                    >

                </form>

            </div>

        </div>


        {{-- Table --}}
        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Kode
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Nama Produk
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Kategori
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Brand
                        </th>

                        <th class="px-6 py-4 text-center font-semibold text-gray-600">
                            Satuan
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Harga Jual
                        </th>

                        <th class="px-6 py-4 text-center font-semibold text-gray-600">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200">

                    @forelse ($products as $product)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $product->code }}
                            </td>

                            <td class="px-6 py-4">

                                <div>
                                    <p class="font-medium text-gray-900">
                                        {{ $product->name }}
                                    </p>

                                    @if ($product->barcode)
                                        <p class="text-xs text-gray-500">
                                            {{ $product->barcode }}
                                        </p>
                                    @endif
                                </div>

                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $product->category->name ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $product->brand ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                {{ $product->unit }}
                            </td>

                            <td class="px-6 py-4 text-right font-medium">
                                Rp {{ number_format($product->default_selling_price, 0, ',', '.') }}
                            </td>

                            <td class="px-6 py-4 text-center">

                                @if ($product->is_active)

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                        Aktif
                                    </span>

                                @else

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                        Tidak Aktif
                                    </span>

                                @endif

                            </td>

                            <td class="px-6 py-4 text-right">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('products.show', $product) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
                                        Detail
                                    </a>

                                    <a href="{{ route('products.edit', $product) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800">
                                        Edit
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8" class="px-6 py-12 text-center">

                                <p class="text-gray-500">
                                    Belum ada data produk.
                                </p>

                                <a href="{{ route('products.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800">
                                    Tambah Produk Pertama
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($products->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $products->links() }}
            </div>

        @endif

    </div>

</div>

@endsection