@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Invoices
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola invoice dan tagihan pelanggan.
            </p>
        </div>

        <a href="{{ route('invoices.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            + Buat Invoice
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


    {{-- Invoice Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Daftar Invoice
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Total {{ $invoices->total() }} invoice
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            No. Invoice
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Customer
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

                    @forelse ($invoices as $invoice)

                        <tr class="hover:bg-gray-50">

                            {{-- Invoice Number --}}
                            <td class="px-6 py-4">

                                <div class="font-semibold text-gray-900">
                                    {{ $invoice->invoice_number
                                        ?? $invoice->number
                                        ?? $invoice->code
                                        ?? '-' }}
                                </div>

                            </td>


                            {{-- Date --}}
                            <td class="px-6 py-4 text-gray-600">

                                {{ $invoice->created_at
                                    ? $invoice->created_at->format('d/m/Y')
                                    : '-' }}

                            </td>


                            {{-- Customer --}}
                            <td class="px-6 py-4">

                                <div class="font-medium text-gray-900">
                                    {{ $invoice->customer->name
                                        ?? $invoice->customer_name
                                        ?? '-' }}
                                </div>

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @php

                                    $status = strtoupper(
                                        $invoice->status ?? 'UNPAID'
                                    );

                                    $statusClass = match ($status) {

                                        'PAID',
                                        'LUNAS',
                                        'COMPLETED'
                                            => 'bg-green-100 text-green-700',

                                        'PARTIAL',
                                        'PARTIALLY_PAID',
                                        'SEBAGIAN'
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
                                    {{ $invoice->status ?? 'Unpaid' }}
                                </span>

                            </td>


                            {{-- Total --}}
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">

                                Rp
                                {{ number_format(
                                    $invoice->grand_total
                                    ?? $invoice->total
                                    ?? $invoice->total_amount
                                    ?? 0,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                            {{-- Actions --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('invoices.show', $invoice) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                        Detail
                                    </a>

                                    <a href="{{ route('invoices.edit', $invoice) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">
                                        Edit
                                    </a>

                                    <form action="{{ route('invoices.destroy', $invoice) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus invoice ini?')">

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
                                    🧾
                                </div>

                                <p class="text-gray-500">
                                    Belum ada invoice.
                                </p>

                                <a href="{{ route('invoices.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                                    Buat Invoice
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($invoices->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $invoices->links() }}
            </div>

        @endif

    </div>

</div>

@endsection