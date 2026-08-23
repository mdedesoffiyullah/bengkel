@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Invoice
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Informasi lengkap invoice dan pembayaran.
            </p>
        </div>

        <div class="flex gap-2">

            <a
                href="{{ route('invoices.index') }}"
                class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50"
            >
                Kembali
            </a>

            @if (!$invoice->payments()->exists() && $invoice->status !== 'CANCELLED')
                <a
                    href="{{ route('invoices.edit', $invoice) }}"
                    class="px-4 py-2 text-sm font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800"
                >
                    Edit
                </a>
            @endif

        </div>

    </div>


    @if (session('success'))
        <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-700 text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-200 text-red-700 text-sm">
            {{ session('error') }}
        </div>
    @endif


    {{-- Header Invoice --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-sm text-gray-500">
                    Kode Invoice
                </p>

                <p class="mt-1 text-lg font-bold text-gray-900">
                    {{ $invoice->code }}
                </p>
            </div>


            <div class="md:text-right">

                <p class="text-sm text-gray-500">
                    Status
                </p>

                <span class="inline-flex mt-1 px-3 py-1 text-xs font-medium rounded-full
                    @if ($invoice->status === 'PAID')
                        bg-green-100 text-green-700
                    @elseif ($invoice->status === 'CANCELLED')
                        bg-red-100 text-red-700
                    @elseif ($invoice->status === 'PARTIAL')
                        bg-blue-100 text-blue-700
                    @else
                        bg-yellow-100 text-yellow-700
                    @endif
                ">
                    {{ $invoice->status }}
                </span>

            </div>

        </div>

    </div>


    {{-- Customer & Work Order --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <h2 class="font-semibold text-gray-900 mb-5">
            Informasi Transaksi
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <p class="text-sm text-gray-500">
                    Customer
                </p>

                <p class="mt-1 font-medium text-gray-900">
                    {{ $invoice->customer->name ?? '-' }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Work Order
                </p>

                <p class="mt-1 font-medium text-gray-900">
                    {{ $invoice->workOrder->code ?? '-' }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Tanggal Invoice
                </p>

                <p class="mt-1 text-gray-900">
                    {{ $invoice->invoice_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Jatuh Tempo
                </p>

                <p class="mt-1 text-gray-900">
                    {{ $invoice->due_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>

        </div>

    </div>


    {{-- Financial --}}
    <div class="bg-white rounded-xl border border-gray-200 p-6">

        <h2 class="font-semibold text-gray-900 mb-5">
            Ringkasan Tagihan
        </h2>

        <div class="space-y-3">

            <div class="flex justify-between">
                <span class="text-gray-600">
                    Subtotal
                </span>

                <span class="font-medium">
                    Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}
                </span>
            </div>


            <div class="flex justify-between">
                <span class="text-gray-600">
                    Discount
                </span>

                <span class="font-medium">
                    Rp {{ number_format($invoice->discount, 0, ',', '.') }}
                </span>
            </div>


            <div class="flex justify-between">
                <span class="text-gray-600">
                    Tax
                </span>

                <span class="font-medium">
                    Rp {{ number_format($invoice->tax, 0, ',', '.') }}
                </span>
            </div>


            <div class="pt-3 border-t border-gray-200 flex justify-between">

                <span class="font-semibold text-gray-900">
                    Grand Total
                </span>

                <span class="text-xl font-bold text-gray-900">
                    Rp {{ number_format($invoice->grand_total, 0, ',', '.') }}
                </span>

            </div>


            <div class="flex justify-between">

                <span class="text-gray-600">
                    Sudah Dibayar
                </span>

                <span class="font-medium text-green-600">
                    Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}
                </span>

            </div>


            <div class="pt-3 border-t border-gray-200 flex justify-between">

                <span class="font-semibold text-gray-900">
                    Sisa Tagihan
                </span>

                <span class="text-lg font-bold text-red-600">
                    Rp {{
                        number_format(
                            max(
                                0,
                                (float) $invoice->grand_total -
                                (float) $invoice->paid_amount
                            ),
                            0,
                            ',',
                            '.'
                        )
                    }}
                </span>

            </div>

        </div>

    </div>


    {{-- Notes --}}
    @if ($invoice->notes)

        <div class="bg-white rounded-xl border border-gray-200 p-6">

            <h2 class="font-semibold text-gray-900 mb-3">
                Catatan
            </h2>

            <p class="text-gray-600 whitespace-pre-line">
                {{ $invoice->notes }}
            </p>

        </div>

    @endif


    {{-- Payments --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Riwayat Pembayaran
            </h2>

        </div>

        @if ($invoice->payments->count())

            <div class="overflow-x-auto">

                <table class="w-full text-sm">

                    <thead class="bg-gray-50">

                        <tr>

                            <th class="px-6 py-4 text-left">
                                Kode
                            </th>

                            <th class="px-6 py-4 text-left">
                                Tanggal
                            </th>

                            <th class="px-6 py-4 text-left">
                                Metode
                            </th>

                            <th class="px-6 py-4 text-right">
                                Jumlah
                            </th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-200">

                        @foreach ($invoice->payments as $payment)

                            <tr>

                                <td class="px-6 py-4 font-medium">
                                    {{ $payment->code }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $payment->paid_at?->format('d/m/Y H:i') ?? '-' }}
                                </td>

                                <td class="px-6 py-4">
                                    {{ $payment->method }}
                                </td>

                                <td class="px-6 py-4 text-right font-medium">
                                    Rp {{ number_format($payment->amount, 0, ',', '.') }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="px-6 py-10 text-center text-gray-500">
                Belum ada pembayaran untuk invoice ini.
            </div>

        @endif

    </div>

</div>

@endsection
