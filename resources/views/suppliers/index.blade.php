@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Suppliers
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data supplier bengkel.
            </p>
        </div>

        <a href="{{ route('suppliers.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            + Tambah Supplier
        </a>

    </div>


    {{-- Success Message --}}
    @if (session('success'))

        <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-700 text-sm">
            {{ session('success') }}
        </div>

    @endif


    {{-- Supplier Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Daftar Supplier
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Total {{ $suppliers->total() }} supplier
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Kode
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Nama Supplier
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Telepon
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Email
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Alamat
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

                    @forelse ($suppliers as $supplier)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $supplier->code }}
                            </td>


                            <td class="px-6 py-4">

                                <div class="font-medium text-gray-900">
                                    {{ $supplier->name }}
                                </div>

                            </td>


                            <td class="px-6 py-4 text-gray-600">
                                {{ $supplier->phone ?? '-' }}
                            </td>


                            <td class="px-6 py-4 text-gray-600">
                                {{ $supplier->email ?? '-' }}
                            </td>


                            <td class="px-6 py-4 text-gray-600">

                                <div class="max-w-xs truncate">
                                    {{ $supplier->address ?? '-' }}
                                </div>

                            </td>


                            <td class="px-6 py-4 text-center">

                                @if ($supplier->is_active)

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                        Aktif
                                    </span>

                                @else

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-600">
                                        Tidak Aktif
                                    </span>

                                @endif

                            </td>


                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                        Detail
                                    </a>

                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">
                                        Edit
                                    </a>

                                    <form action="{{ route('suppliers.destroy', $supplier) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus supplier ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition">
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7" class="px-6 py-12 text-center">

                                <p class="text-gray-500">
                                    Belum ada data supplier.
                                </p>

                                <a href="{{ route('suppliers.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800">
                                    Tambah Supplier
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($suppliers->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $suppliers->links() }}
            </div>

        @endif

    </div>

</div>

@endsection