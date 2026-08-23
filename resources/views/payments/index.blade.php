@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Payments
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola pembayaran pelanggan berdasarkan Work Order.
            </p>
        </div>

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


    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Total Payment
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ $payments->total() }}
            </p>

        </div>


        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Total Dibayar
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">

                Rp
                {{ number_format(
                    $totalPaid ?? 0,
                    0,
                    ',',
                    '.'
                ) }}

            </p>

        </div>


        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Transaksi Hari Ini
            </p>

            <p class="mt-2 text-2xl font-bold text-blue-600">
                {{ $todayPayments ?? 0 }}
            </p>

        </div>

    </div>


    {{-- Payment Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Daftar Payment
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Riwayat pembayaran pelanggan.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            No. Payment
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Customer
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Work Order
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Metode
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Jumlah
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200">

                    @forelse ($payments as $payment)

                        <tr class="hover:bg-gray-50">

                            {{-- Payment Number --}}
                            <td class="px-6 py-4">

                                <div class="font-semibold text-gray-900">
                                    {{ $payment->code ?? '-' }}
                                </div>

                            </td>


                            {{-- Date --}}
                            <td class="px-6 py-4 text-gray-600">

                                {{ $payment->paid_at
                                    ? $payment->paid_at->format('d/m/Y H:i')
                                    : '-' }}

                            </td>


                            {{-- Customer --}}
                            <td class="px-6 py-4">

                                <div class="font-medium text-gray-900">
                                    {{ $payment->workOrder?->customer?->name ?? '-' }}
                                </div>

                            </td>


                            {{-- Work Order --}}
                            <td class="px-6 py-4">

                                @if ($payment->workOrder)

                                    <a href="{{ route('work-orders.show', $payment->workOrder) }}"
                                       class="font-medium text-slate-700 hover:text-slate-900">
                                        {{ $payment->workOrder->code }}
                                    </a>

                                @else

                                    <span class="text-gray-400">
                                        -
                                    </span>

                                @endif

                            </td>


                            {{-- Method --}}
                            <td class="px-6 py-4">

                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-700">

                                    {{ match($payment->method) {
                                        'CASH' => 'Cash',
                                        'BANK_TRANSFER' => 'Bank Transfer',
                                        'DEBIT_CARD' => 'Debit Card',
                                        'CREDIT_CARD' => 'Credit Card',
                                        'QRIS' => 'QRIS',
                                        'OTHER' => 'Other',
                                        default => $payment->method ?? '-',
                                    } }}

                                </span>

                            </td>


                            {{-- Amount --}}
                            <td class="px-6 py-4 text-right font-semibold text-gray-900">

                                Rp
                                {{ number_format(
                                    (float) $payment->amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}

                            </td>


                            {{-- Actions --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('payments.show', $payment) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                        Detail
                                    </a>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="7" class="px-6 py-12 text-center">

                                <div class="text-gray-400 text-4xl mb-3">
                                    Ã°Å¸â€™Â³
                                </div>

                                <p class="text-gray-500">
                                    Belum ada pembayaran.
                                </p>

                                <a href="{{ route('payments.create') }}"
                                   class="inline-block mt-3 text-sm font-medium text-slate-700 hover:text-slate-900">
                                    Catat pembayaran pertama
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($payments->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $payments->links() }}
            </div>

        @endif

    </div>

</div>

@endsection
