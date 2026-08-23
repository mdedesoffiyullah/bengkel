@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Product Categories
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola kategori produk bengkel.
            </p>
        </div>

        <a href="{{ route('product-categories.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">
            + Tambah Kategori
        </a>

    </div>


    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="p-6 border-b border-gray-200">

            <div>
                <h2 class="font-semibold text-gray-900">
                    Daftar Kategori
                </h2>

                <p class="text-sm text-gray-500 mt-1">
                    Total {{ $categories->total() }} kategori
                </p>
            </div>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Kode
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Nama
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Deskripsi
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

                    @forelse ($categories as $category)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $category->code }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $category->name }}
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $category->description ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-center">

                                @if ($category->is_active)

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

                                    <a href="{{ route('product-categories.show', $category) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50">
                                        Detail
                                    </a>

                                    <a href="{{ route('product-categories.edit', $category) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800">
                                        Edit
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="px-6 py-12 text-center">

                                <p class="text-gray-500">
                                    Belum ada kategori produk.
                                </p>

                                <a href="{{ route('product-categories.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800">
                                    Tambah Kategori Pertama
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($categories->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $categories->links() }}
            </div>

        @endif

    </div>

</div>

@endsection