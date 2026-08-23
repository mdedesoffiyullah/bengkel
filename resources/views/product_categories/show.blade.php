@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Kategori Produk</h1>
            <p class="mt-1 text-sm text-gray-500">Informasi kategori dan produk di dalamnya.</p>
        </div>

        <div class="flex gap-2">
            <a href="{{ route('product-categories.edit', $productCategory) }}"
               class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
                Edit
            </a>

            <a href="{{ route('product-categories.index') }}"
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
                        {{ $productCategory->name }}
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ $productCategory->code }}
                    </p>
                </div>

                @if ($productCategory->is_active)
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
                <p class="text-xs font-medium text-gray-500 uppercase">Kode</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $productCategory->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">Nama</p>
                <p class="mt-1 text-sm text-gray-900">
                    {{ $productCategory->name }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs font-medium text-gray-500 uppercase">Deskripsi</p>
                <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                    {{ $productCategory->description ?? '-' }}
                </p>
            </div>

        </div>

    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                Produk dalam Kategori
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Total {{ $productCategory->products->count() }} produk
            </p>
        </div>

        @if ($productCategory->products->count())

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">

                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Kode
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Nama Produk
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Merk
                            </th>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">

                        @foreach ($productCategory->products as $product)

                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $product->code }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $product->name }}
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $product->brand ?? '-' }}
                                </td>

                                <td class="px-6 py-4">

                                    @if ($product->is_active)
                                        <span class="px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                                            Aktif
                                        </span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                                            Tidak Aktif
                                        </span>
                                    @endif

                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>

        @else

            <div class="p-6 text-sm text-gray-500">
                Belum ada produk dalam kategori ini.
            </div>

        @endif

    </div>

</div>
@endsection
