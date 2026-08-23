@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Customers
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola data customer dan motor.
            </p>
        </div>

        <a
            href="{{ route('customers.create') }}"
            class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800"
        >
            + Tambah Customer
        </a>

    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="p-6 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="font-semibold text-gray-900">
                        Daftar Customer
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Total {{ $customers->total() }} customer
                    </p>
                </div>

                <form
                    method="GET"
                    action="{{ route('customers.index') }}"
                >

                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama / plat / motor..."
                        class="w-72 px-4 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500"
                    >

                </form>

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
                            Customer
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Telepon
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Plat Nomor
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Motor
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

                    @forelse ($customers as $customer)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $customer->code }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $customer->name }}
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $customer->phone ?? '-' }}
                            </td>

                            <td class="px-6 py-4 font-medium text-gray-900">
                                {{ $customer->plate_number ?? '-' }}
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $customer->brand ?? '-' }}
                                {{ $customer->type ? ' - ' . $customer->type : '' }}
                            </td>

                            <td class="px-6 py-4 text-center">

                                @if ($customer->is_active)

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

                                    <a
                                        href="{{ route('customers.show', $customer) }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50"
                                    >
                                        Detail
                                    </a>

                                    <a
                                        href="{{ route('customers.edit', $customer) }}"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800"
                                    >
                                        Edit
                                    </a>

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="px-6 py-12 text-center"
                            >

                                <p class="text-gray-500">
                                    Belum ada data customer.
                                </p>

                                <a
                                    href="{{ route('customers.create') }}"
                                    class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800"
                                >
                                    Tambah Customer Pertama
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($customers->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $customers->links() }}
            </div>

        @endif

    </div>

</div>

@endsection