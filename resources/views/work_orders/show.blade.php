@extends('layouts.app')

@section('content')

<div class="max-w-7xl mx-auto space-y-6">

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="px-4 py-3 rounded-lg bg-red-100 border border-red-200 text-red-700">
            {{ session('error') }}
        </div>
    @endif


    {{-- Header --}}
    <div class="flex items-center justify-between print:hidden">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Work Order
            </h1>

            <p class="text-sm text-gray-500 mt-1">
                {{ $workOrder->code }}
            </p>
        </div>

        <div class="flex items-center gap-2">

            <a
                href="{{ route('work-orders.index') }}"
                class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50 transition"
            >
                Kembali
            </a>

            <a
                href="{{ route('work-orders.create') }}"
                class="inline-flex items-center px-4 py-2 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition"
            >
                + Buat WO Baru
            </a>

            @if($workOrder->status !== 'COMPLETED')
                <a
                    href="{{ route('work-orders.edit', $workOrder) }}"
                    class="inline-flex items-center px-4 py-2 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition"
                >
                    Edit WO
                </a>
            @endif

            <button
                type="button"
                onclick="window.print()"
                class="px-4 py-2 rounded-lg border border-gray-300 bg-white text-gray-700 font-medium hover:bg-gray-50"
            >
                Print
            </button>

        </div>

    </div>


    {{-- Work Order Summary --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

        <div class="flex items-start justify-between">

            <div>

                <h2 class="text-xl font-bold text-gray-900">
                    {{ $workOrder->code }}
                </h2>

                <div class="text-sm text-gray-500 mt-1">
                    {{ $workOrder->type }}
                </div>

            </div>

            @php
                $status = strtoupper($workOrder->status ?? 'OPEN');

                $statusClass = match ($status) {
                    'COMPLETED'
                        => 'bg-green-100 text-green-700',

                    'IN_PROGRESS', 'WAITING_PARTS'
                        => 'bg-blue-100 text-blue-700',

                    'CANCELLED'
                        => 'bg-red-100 text-red-700',

                    default
                        => 'bg-yellow-100 text-yellow-700',
                };
            @endphp

            <span class="inline-flex px-3 py-1 rounded-full text-sm font-medium {{ $statusClass }}">
                {{ $workOrder->status }}
            </span>

        </div>


        {{-- Customer --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-6 pt-6 border-t">

            <div>
                <div class="text-xs text-gray-500">
                    Customer
                </div>

                <div class="font-semibold mt-1">
                    {{ $workOrder->customer->name ?? '-' }}
                </div>

                <div class="text-sm text-gray-500 mt-1">
                    {{ $workOrder->customer->phone ?? '-' }}
                </div>
            </div>


            <div>
                <div class="text-xs text-gray-500">
                    Plat Nomor
                </div>

                <div class="font-semibold mt-1">
                    {{ $workOrder->customer->plate_number ?? '-' }}
                </div>
            </div>


            <div>
                <div class="text-xs text-gray-500">
                    Kendaraan
                </div>

                <div class="font-semibold mt-1">
                    {{ trim(
                        ($workOrder->customer->brand ?? '') . ' ' .
                        ($workOrder->customer->type ?? '')
                    ) ?: '-' }}
                </div>
            </div>


            <div>
                <div class="text-xs text-gray-500">
                    Dibuka
                </div>

                <div class="font-semibold mt-1">
                    {{ optional($workOrder->opened_at)->format('d/m/Y H:i') }}
                </div>
            </div>

        </div>


        {{-- Complaint --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6 pt-6 border-t">

            <div>
                <div class="text-xs text-gray-500">
                    Keluhan
                </div>

                <div class="mt-1">
                    {{ $workOrder->complaint ?: '-' }}
                </div>
            </div>


            <div>
                <div class="text-xs text-gray-500">
                    Diagnosa
                </div>

                <div class="mt-1">
                    {{ $workOrder->diagnosis ?: '-' }}
                </div>
            </div>


            <div>
                <div class="text-xs text-gray-500">
                    Catatan
                </div>

                <div class="mt-1">
                    {{ $workOrder->notes ?: '-' }}
                </div>
            </div>

        </div>

    </div>


    {{-- Items --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">

        <div class="mb-5">

            <h2 class="text-lg font-semibold text-gray-900">
                Item Pekerjaan
            </h2>

            <p class="text-sm text-gray-500">
                Jasa dan sparepart yang digunakan pada Work Order.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead>

                    <tr class="border-b text-left text-gray-500">

                        <th class="py-3 pr-4">
                            Kode
                        </th>

                        <th class="py-3 pr-4">
                            Item
                        </th>

                        <th class="py-3 pr-4">
                            Tipe
                        </th>

                        <th class="py-3 pr-4 text-right">
                            Qty
                        </th>

                        <th class="py-3 pr-4">
                            Satuan
                        </th>

                        <th class="py-3 pr-4 text-right">
                            Harga
                        </th>

                        <th class="py-3 pr-4 text-right">
                            Discount
                        </th>

                        <th class="py-3 text-right">
                            Subtotal
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($workOrder->items as $item)

                        <tr class="border-b">

                            <td class="py-4 pr-4">
                                {{ $item->item_code ?: '-' }}
                            </td>

                            <td class="py-4 pr-4 font-medium">

                                {{ $item->item_name }}

                                @if($item->notes)

                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $item->notes }}
                                    </div>

                                @endif

                            </td>

                            <td class="py-4 pr-4">
                                {{ $item->item_type === 'PRODUCT'
                                    ? 'SPAREPART'
                                    : 'SERVICE' }}
                            </td>

                            <td class="py-4 pr-4 text-right">
                                {{ number_format(
                                    (float) $item->quantity,
                                    3,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="py-4 pr-4">
                                {{ $item->unit }}
                            </td>

                            <td class="py-4 pr-4 text-right">
                                Rp
                                {{ number_format(
                                    (float) $item->unit_price,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="py-4 pr-4 text-right">
                                Rp
                                {{ number_format(
                                    (float) $item->discount_amount,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="py-4 text-right font-semibold">
                                Rp
                                {{ number_format(
                                    (float) $item->subtotal,
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="8"
                                class="py-10 text-center text-gray-500"
                            >
                                Belum ada item pekerjaan.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if($workOrder->status !== 'COMPLETED')

            <div class="mt-5 pt-5 border-t print:hidden">

                <a
                    href="{{ route('work-orders.edit', $workOrder) }}"
                    class="inline-flex px-5 py-3 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700"
                >
                    + Tambah Item
                </a>

            </div>

        @endif


        {{-- Work Order Total --}}
        <div class="mt-8 ml-auto max-w-md border-t pt-5 space-y-3">

            <div class="flex justify-between">

                <span class="text-gray-600">
                    Subtotal
                </span>

                <strong>
                    Rp
                    {{ number_format(
                        (float) $workOrder->subtotal,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>


            <div class="flex justify-between">

                <span class="text-gray-600">
                    Discount WO
                </span>

                <strong>
                    Rp
                    {{ number_format(
                        (float) $workOrder->discount,
                        0,
                        ',',
                        '.'
                    ) }}
                </strong>

            </div>


            <div class="flex justify-between text-xl font-bold border-t pt-3">

                <span>
                    Grand Total
                </span>

                <span>
                    Rp
                    {{ number_format(
                        (float) $workOrder->grand_total,
                        0,
                        ',',
                        '.'
                    ) }}
                </span>

            </div>

        </div>

    </div>


    {{-- PAYMENT --}}
    @php
        $totalPaid = (float) ($workOrder->payments?->sum('amount') ?? 0);

        $grandTotal = (float) ($workOrder->grand_total ?? 0);

        $remaining = max(
            0,
            $grandTotal - $totalPaid
        );

        $paymentStatus = match (true) {
            $grandTotal <= 0
                => 'NO BILL',

            $totalPaid >= $grandTotal
                => 'PAID',

            $totalPaid > 0
                => 'PARTIAL',

            default
                => 'UNPAID',
        };

        $paymentStatusClass = match ($paymentStatus) {
            'PAID'
                => 'bg-green-100 text-green-700',

            'PARTIAL'
                => 'bg-yellow-100 text-yellow-700',

            'NO BILL'
                => 'bg-gray-100 text-gray-600',

            default
                => 'bg-red-100 text-red-700',
        };
    @endphp


    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <div class="flex items-center justify-between gap-4">

                <div>

                    <h2 class="text-lg font-semibold text-gray-900">
                        Payment
                    </h2>

                    <p class="text-sm text-gray-500 mt-1">
                        Pembayaran pelanggan untuk Work Order ini.
                    </p>

                </div>

                <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold {{ $paymentStatusClass }}">
                    {{ $paymentStatus }}
                </span>

            </div>

        </div>


        {{-- Payment Summary --}}
        <div class="p-6">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                <div class="rounded-lg bg-gray-50 border border-gray-200 p-4">

                    <div class="text-xs text-gray-500">
                        Total Work Order
                    </div>

                    <div class="mt-1 text-lg font-bold text-gray-900">
                        Rp
                        {{ number_format(
                            $grandTotal,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>

                </div>


                <div class="rounded-lg bg-green-50 border border-green-200 p-4">

                    <div class="text-xs text-gray-500">
                        Total Dibayar
                    </div>

                    <div class="mt-1 text-lg font-bold text-green-700">
                        Rp
                        {{ number_format(
                            $totalPaid,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>

                </div>


                <div class="rounded-lg bg-yellow-50 border border-yellow-200 p-4">

                    <div class="text-xs text-gray-500">
                        Sisa Tagihan
                    </div>

                    <div class="mt-1 text-lg font-bold text-gray-900">
                        Rp
                        {{ number_format(
                            $remaining,
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>

                </div>

            </div>


            {{-- Payment History --}}
            <div class="mt-6">

                <div class="flex items-center justify-between mb-3">

                    <h3 class="font-semibold text-gray-900">
                        Riwayat Pembayaran
                    </h3>

                    @if(
                        $workOrder->status === 'COMPLETED' &&
                        $remaining > 0
                    )

                        <a
                            href="{{ route('payments.create', ['work_order_id' => $workOrder->id]) }}"
                            class="inline-flex px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 print:hidden"
                        >
                            + Buat Payment
                        </a>

                    @endif

                </div>


                @if($workOrder->payments && $workOrder->payments->count())

                    <div class="overflow-x-auto border border-gray-200 rounded-lg">

                        <table class="w-full text-sm">

                            <thead class="bg-gray-50 border-b border-gray-200">

                                <tr>

                                    <th class="px-4 py-3 text-left font-semibold text-gray-600">
                                        Payment
                                    </th>

                                    <th class="px-4 py-3 text-left font-semibold text-gray-600">
                                        Tanggal
                                    </th>

                                    <th class="px-4 py-3 text-left font-semibold text-gray-600">
                                        Metode
                                    </th>

                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">
                                        Jumlah
                                    </th>

                                    <th class="px-4 py-3 text-right font-semibold text-gray-600">
                                        Aksi
                                    </th>

                                </tr>

                            </thead>


                            <tbody class="divide-y divide-gray-200">

                                @foreach($workOrder->payments->sortByDesc('paid_at') as $payment)

                                    <tr>

                                        <td class="px-4 py-3 font-semibold text-gray-900">
                                            {{ $payment->code ?? '-' }}
                                        </td>

                                        <td class="px-4 py-3 text-gray-600">
                                            {{ $payment->paid_at
                                                ? $payment->paid_at->format('d/m/Y H:i')
                                                : '-' }}
                                        </td>

                                        <td class="px-4 py-3">

                                            <span class="inline-flex px-2.5 py-1 rounded-full bg-gray-100 text-gray-700 text-xs font-medium">

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

                                        <td class="px-4 py-3 text-right font-semibold text-gray-900">

                                            Rp
                                            {{ number_format(
                                                (float) $payment->amount,
                                                0,
                                                ',',
                                                '.'
                                            ) }}

                                        </td>

                                        <td class="px-4 py-3 text-right">

                                            <a
                                                href="{{ route('payments.show', $payment) }}"
                                                class="inline-flex px-3 py-1.5 rounded-lg border border-gray-300 text-xs font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Detail
                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="rounded-lg border border-dashed border-gray-300 p-8 text-center">

                        <div class="text-gray-400 text-3xl mb-2">
                            ðŸ’³
                        </div>

                        <p class="text-sm text-gray-500">
                            Belum ada pembayaran untuk Work Order ini.
                        </p>

                        @if($workOrder->status === 'COMPLETED' && $remaining > 0)

                            <a
                                href="{{ route('payments.create', ['work_order_id' => $workOrder->id]) }}"
                                class="inline-flex mt-3 text-sm font-semibold text-slate-700 hover:text-slate-900 print:hidden"
                            >
                                Catat pembayaran pertama
                            </a>

                        @endif

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- Final Action --}}
    <div class="print:hidden">

        @if($workOrder->status !== 'COMPLETED')

            <div class="flex justify-end">

                <form
                    method="POST"
                    action="{{ route('work-orders.final', $workOrder) }}"
                    onsubmit="return confirm('FINAL-kan Work Order ini? Setelah FINAL, WO tidak dapat diedit lagi.')"
                >

                    @csrf
                    @method('PATCH')

                    <button
                        type="submit"
                        class="px-6 py-3 rounded-lg bg-green-600 text-white font-semibold hover:bg-green-700"
                    >
                        FINAL WORK ORDER
                    </button>

                </form>

            </div>

        @else

            <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-center font-semibold text-green-700">
                WORK ORDER FINAL
            </div>

        @endif

    </div>

</div>


<style>

@media print {

    body {
        background: white !important;
    }

    .print\:hidden {
        display: none !important;
    }

    nav,
    header,
    aside {
        display: none !important;
    }

    .shadow-sm,
    .shadow {
        box-shadow: none !important;
    }

    .border {
        border-color: #ddd !important;
    }

}

</style>

@endsection
