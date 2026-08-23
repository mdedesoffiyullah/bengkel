@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Inventory
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola saldo stok produk dan ketersediaan inventory bengkel.
            </p>
        </div>

    </div>


    {{-- Success Message --}}
    @if (session('success'))

        <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-700 text-sm">
            {{ session('success') }}
        </div>

    @endif


    {{-- Error Message --}}
    @if (session('error'))

        <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-200 text-red-700 text-sm">
            {{ session('error') }}
        </div>

    @endif


    {{-- Inventory Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Stock Inventory
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Saldo stok produk berdasarkan inventory balance.
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
                            Produk
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Satuan
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Stok
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Reserved
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Available
                        </th>

                        <th class="px-6 py-4 text-center font-semibold text-gray-600">
                            Status
                        </th>

                        <th class="px-6 py-4 text-center font-semibold text-gray-600">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200">

                    @forelse ($balances as $balance)

                        @php

                            $quantity = (float) $balance->quantity;

                            $reserved = (float) $balance->reserved_quantity;

                            $available = (float) $balance->available_quantity;

                            $minimum = (float) (
                                $balance->product->minimum_stock ?? 0
                            );

                        @endphp


                        <tr class="hover:bg-gray-50">

                            {{-- Product Code --}}
                            <td class="px-6 py-4 font-medium text-gray-900">

                                {{ $balance->product?->code ?? '-' }}

                            </td>


                            {{-- Product --}}
                            <td class="px-6 py-4">

                                <div class="font-medium text-gray-900">
                                    {{ $balance->product?->name ?? '-' }}
                                </div>

                                @if ($balance->product?->brand)

                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $balance->product->brand }}
                                    </div>

                                @endif

                            </td>


                            {{-- Unit --}}
                            <td class="px-6 py-4 text-gray-600">

                                {{ $balance->product?->unit ?? 'PCS' }}

                            </td>


                            {{-- Quantity --}}
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">

                                {{ number_format(
                                    $quantity,
                                    3,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                            {{-- Reserved --}}
                            <td class="px-6 py-4 text-right text-gray-600">

                                {{ number_format(
                                    $reserved,
                                    3,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                            {{-- Available --}}
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">

                                {{ number_format(
                                    $available,
                                    3,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4 text-center">

                                @if ($available <= 0)

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                        Habis
                                    </span>

                                @elseif ($available <= $minimum)

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700">
                                        Stok Minimum
                                    </span>

                                @else

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                        Tersedia
                                    </span>

                                @endif

                            </td>


                            {{-- Action --}}
                            <td class="px-6 py-4 text-center">

                                <a
                                    href="{{ route(
                                        'inventory-balances.show',
                                        $balance
                                    ) }}"
                                    class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-medium"
                                >
                                    Detail
                                </a>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="px-6 py-12 text-center"
                            >

                                <div class="text-gray-400 text-4xl mb-3">
                                    ??
                                </div>

                                <p class="text-gray-500">
                                    Belum ada data inventory.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($balances->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $balances->links() }}
            </div>

        @endif

    </div>

</div>

@endsection
