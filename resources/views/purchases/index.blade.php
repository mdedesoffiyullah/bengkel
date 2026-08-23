@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Purchases
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola transaksi pembelian barang dan supplier.
            </p>
        </div>

        <a href="{{ route('purchases.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            + Buat Purchase
        </a>

    </div>


    {{-- Success --}}
    @if (session('success'))

        <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-700 text-sm">
            {{ session('success') }}
        </div>

    @endif


    {{-- Error --}}
    @if (session('error'))

        <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-200 text-red-700 text-sm">
            {{ session('error') }}
        </div>

    @endif


    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Daftar Purchase
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Total {{ $purchases->total() }} transaksi
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            No. Purchase
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Supplier
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Total
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200">

                    @forelse ($purchases as $purchase)

                        <tr class="hover:bg-gray-50">

                            {{-- Number --}}
                            <td class="px-6 py-4">

                                <div class="font-semibold text-gray-900">
                                    {{ $purchase->purchase_number
                                        ?? $purchase->number
                                        ?? $purchase->code
                                        ?? '-' }}
                                </div>

                            </td>


                            {{-- Date --}}
                            <td class="px-6 py-4 text-gray-600">

                                {{ $purchase->created_at
                                    ? $purchase->created_at->format('d/m/Y')
                                    : '-' }}

                            </td>


                            {{-- Supplier --}}
                            <td class="px-6 py-4">

                                <div class="font-medium text-gray-900">
                                    {{ $purchase->supplier->name ?? '-' }}
                                </div>

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @php

                                    $status = strtoupper(
                                        $purchase->status ?? 'PENDING'
                                    );

                                    $statusClass = match ($status) {

                                        'COMPLETED',
                                        'RECEIVED',
                                        'DONE',
                                        'SELESAI'
                                            => 'bg-green-100 text-green-700',

                                        'PROCESS',
                                        'PROCESSING',
                                        'IN_PROGRESS',
                                        'PROSES'
                                            => 'bg-blue-100 text-blue-700',

                                        'CANCELLED',
                                        'CANCELED',
                                        'BATAL'
                                            => 'bg-red-100 text-red-700',

                                        default
                                            => 'bg-yellow-100 text-yellow-700',

                                    };

                                @endphp


                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                    {{ $purchase->status ?? 'Pending' }}
                                </span>

                            </td>


                            {{-- Total --}}
                            <td class="px-6 py-4 text-right font-medium text-gray-900">

                                Rp
                                {{ number_format(
                                    $purchase->grand_total
                                    ?? $purchase->total
                                    ?? $purchase->total_amount
                                    ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                            {{-- Actions --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('purchases.show', $purchase) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                        Detail
                                    </a>


                                    <a href="{{ route('purchases.edit', $purchase) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">
                                        Edit
                                    </a>


                                    <form action="{{ route('purchases.destroy', $purchase) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus transaksi purchase ini?')">

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

                            <td colspan="6" class="px-6 py-12 text-center">

                                <div class="text-gray-400 text-4xl mb-3">
                                    🛒
                                </div>

                                <p class="text-gray-500">
                                    Belum ada transaksi purchase.
                                </p>

                                <a href="{{ route('purchases.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                                    Buat Purchase
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($purchases->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $purchases->links() }}
            </div>

        @endif

    </div>

</div>

@endsection