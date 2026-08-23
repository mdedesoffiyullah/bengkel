@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Work Orders
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola pekerjaan dan servis kendaraan pelanggan.
            </p>
        </div>

        <a href="{{ route('work-orders.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            + Buat Work Order
        </a>

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

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">
                Daftar Work Order
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Total {{ $workOrders->total() }} work order
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            No. WO
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Customer
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Kendaraan
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

                    @forelse ($workOrders as $workOrder)

                        @php
                            $customer = $workOrder->customer;
                        @endphp

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">
                                    {{ $workOrder->code ?? '-' }}
                                </div>
                            </td>

                            <td class="px-6 py-4 text-gray-600">
                                {{ $workOrder->created_at
                                    ? $workOrder->created_at->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="font-medium text-gray-900">
                                    {{ $customer->name ?? '-' }}
                                </div>

                                <div class="text-xs text-gray-500 mt-1">
                                    {{ $customer->phone ?? '-' }}
                                </div>

                            </td>

                            <td class="px-6 py-4">

                                @if ($customer)

                                    <div class="font-medium text-gray-900">
                                        {{ $customer->plate_number ?? '-' }}
                                    </div>

                                    <div class="text-xs text-gray-500 mt-1">
                                        {{ $customer->brand ?? '' }}
                                        {{ $customer->type ?? '' }}
                                    </div>

                                @else

                                    <span class="text-gray-400">
                                        -
                                    </span>

                                @endif

                            </td>

                            <td class="px-6 py-4">

                                @php
                                    $status = strtoupper($workOrder->status ?? 'PENDING');

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

                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                    {{ $workOrder->status ?? 'PENDING' }}
                                </span>

                            </td>

                            <td class="px-6 py-4 text-right font-medium text-gray-900">
                                Rp
                                {{ number_format(
                                    (float) ($workOrder->grand_total ?? 0),
                                    0,
                                    ',',
                                    '.'
                                ) }}
                            </td>

                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('work-orders.show', $workOrder) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                        Detail
                                    </a>

                                    @if ($workOrder->status !== 'COMPLETED')

                                        <a href="{{ route('work-orders.edit', $workOrder) }}"
                                           class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">
                                            Edit
                                        </a>

                                        <form action="{{ route('work-orders.destroy', $workOrder) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus Work Order ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition">
                                                Hapus
                                            </button>

                                        </form>

                                    @endif

                                </div>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="7"
                                class="px-6 py-12 text-center">

                                <div class="text-gray-400 text-4xl mb-3">
                                    📋
                                </div>

                                <p class="text-gray-500">
                                    Belum ada Work Order.
                                </p>

                                <a href="{{ route('work-orders.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                                    Buat Work Order
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        @if ($workOrders->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $workOrders->links() }}
            </div>

        @endif

    </div>

</div>

@endsection