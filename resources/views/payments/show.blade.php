@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Payment
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Informasi lengkap transaksi pembayaran pelanggan.
            </p>
        </div>

        <a href="{{ route('payments.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Kembali
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


    {{-- Payment Information --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>
                    <p class="text-sm text-gray-500">
                        No. Payment
                    </p>

                    <h2 class="mt-1 text-xl font-bold text-gray-900">
                        {{ $payment->code ?? '-' }}
                    </h2>
                </div>

                <span class="inline-flex px-3 py-1.5 text-xs font-semibold rounded-full bg-green-100 text-green-700">
                    RECORDED
                </span>

            </div>

        </div>


        <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Tanggal --}}
                <div>
                    <p class="text-sm text-gray-500">
                        Tanggal Pembayaran
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $payment->paid_at
                            ? $payment->paid_at->format('d/m/Y H:i')
                            : '-' }}
                    </p>
                </div>


                {{-- Customer --}}
                <div>
                    <p class="text-sm text-gray-500">
                        Customer
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $payment->workOrder?->customer?->name ?? '-' }}
                    </p>
                </div>


                {{-- Work Order --}}
                <div>
                    <p class="text-sm text-gray-500">
                        Work Order
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $payment->workOrder?->code ?? '-' }}
                    </p>
                </div>


                {{-- Metode --}}
                <div>
                    <p class="text-sm text-gray-500">
                        Metode Pembayaran
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">

                        {{ match($payment->method) {
                            'CASH' => 'Cash',
                            'BANK_TRANSFER' => 'Bank Transfer',
                            'DEBIT_CARD' => 'Debit Card',
                            'CREDIT_CARD' => 'Credit Card',
                            'QRIS' => 'QRIS',
                            'OTHER' => 'Other',
                            default => $payment->method ?? '-',
                        } }}

                    </p>
                </div>


                {{-- Reference --}}
                <div>
                    <p class="text-sm text-gray-500">
                        Nomor Referensi
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $payment->reference_number ?? '-' }}
                    </p>
                </div>


                {{-- Created --}}
                <div>
                    <p class="text-sm text-gray-500">
                        Dicatat Pada
                    </p>

                    <p class="mt-1 font-semibold text-gray-900">
                        {{ $payment->created_at
                            ? $payment->created_at->format('d/m/Y H:i')
                            : '-' }}
                    </p>
                </div>

            </div>

        </div>

    </div>


    {{-- Financial Summary --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Ringkasan Pembayaran
            </h2>

        </div>


        <div class="p-6 space-y-4">

            {{-- Grand Total --}}
            <div class="flex items-center justify-between">

                <span class="text-sm text-gray-500">
                    Total Work Order
                </span>

                <span class="font-semibold text-gray-900">

                    Rp
                    {{ number_format(
                        (float) ($payment->workOrder?->grand_total ?? 0),
                        0,
                        ',',
                        '.'
                    ) }}

                </span>

            </div>


            {{-- Payment --}}
            <div class="flex items-center justify-between">

                <span class="text-sm text-gray-500">
                    Pembayaran Ini
                </span>

                <span class="font-semibold text-green-600">

                    Rp
                    {{ number_format(
                        (float) $payment->amount,
                        0,
                        ',',
                        '.'
                    ) }}

                </span>

            </div>


            {{-- Total Paid --}}
            <div class="flex items-center justify-between">

                <span class="text-sm text-gray-500">
                    Total Pembayaran
                </span>

                <span class="font-semibold text-gray-900">

                    Rp
                    {{ number_format(
                        (float) ($payment->workOrder?->payments?->sum('amount') ?? 0),
                        0,
                        ',',
                        '.'
                    ) }}

                </span>

            </div>


            {{-- Remaining --}}
            <div class="pt-4 border-t border-gray-200 flex items-center justify-between">

                <span class="font-semibold text-gray-700">
                    Sisa Tagihan
                </span>

                <span class="text-lg font-bold text-gray-900">

                    Rp
                    {{ number_format(
                        max(
                            0,
                            (float) ($payment->workOrder?->grand_total ?? 0)
                            -
                            (float) ($payment->workOrder?->payments?->sum('amount') ?? 0)
                        ),
                        0,
                        ',',
                        '.'
                    ) }}

                </span>

            </div>

        </div>

    </div>


    {{-- Notes --}}
    @if ($payment->notes)

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

            <div class="px-6 py-5 border-b border-gray-200">

                <h2 class="font-semibold text-gray-900">
                    Catatan
                </h2>

            </div>

            <div class="p-6">

                <p class="text-sm text-gray-700 whitespace-pre-line">
                    {{ $payment->notes }}
                </p>

            </div>

        </div>

    @endif


    {{-- Actions --}}
    <div class="flex items-center justify-between">

        <a href="{{ route('payments.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
            ← Kembali ke Payments
        </a>

        <div class="text-xs text-gray-400">
            Payment tidak dapat diedit atau dihapus setelah dicatat.
        </div>

    </div>

</div>

@endsection