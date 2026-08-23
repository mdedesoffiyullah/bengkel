@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Buat Invoice
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Buat invoice baru untuk pelanggan.
        </p>
    </div>

    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('invoices.store') }}" method="POST"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">

        @csrf

        {{-- Invoice Code --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Kode Invoice
            </label>

            <input
                type="text"
                name="code"
                value="{{ old('code', 'INV-' . date('YmdHis')) }}"
                maxlength="30"
                required
                class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                placeholder="INV-202608220001"
            >

            @error('code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Customer --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Customer
            </label>

            <select
                name="customer_id"
                required
                class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
            >
                <option value="">-- Pilih Customer --</option>

                @foreach ($customers as $customer)
                    <option
                        value="{{ $customer->id }}"
                        @selected(
                            old(
                                'customer_id',
                                $workOrder?->customer_id
                            ) == $customer->id
                        )
                    >
                        {{ $customer->name }}
                    </option>
                @endforeach
            </select>

            @error('customer_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Work Order --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Work Order
            </label>

            <select
                name="work_order_id"
                class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
            >
                <option value="">-- Tanpa Work Order --</option>

                @foreach ($workOrders as $wo)
                    <option
                        value="{{ $wo->id }}"
                        @selected(
                            old(
                                'work_order_id',
                                $workOrder?->id
                            ) == $wo->id
                        )
                    >
                        {{ $wo->code }}
                        -
                        {{ $wo->customer->name ?? '-' }}
                    </option>
                @endforeach
            </select>

            @error('work_order_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Dates --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Invoice
                </label>

                <input
                    type="date"
                    name="invoice_date"
                    value="{{ old('invoice_date', date('Y-m-d')) }}"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                >

                @error('invoice_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Jatuh Tempo
                </label>

                <input
                    type="date"
                    name="due_date"
                    value="{{ old('due_date') }}"
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                >

                @error('due_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Amount --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Subtotal
                </label>

                <input
                    type="number"
                    name="subtotal"
                    id="subtotal"
                    value="{{ old('subtotal', 0) }}"
                    min="0"
                    step="0.01"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                >

                @error('subtotal')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Discount
                </label>

                <input
                    type="number"
                    name="discount"
                    id="discount"
                    value="{{ old('discount', 0) }}"
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                >

                @error('discount')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tax
                </label>

                <input
                    type="number"
                    name="tax"
                    id="tax"
                    value="{{ old('tax', 0) }}"
                    min="0"
                    step="0.01"
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                >

                @error('tax')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

        </div>

        {{-- Total Preview --}}
        <div class="bg-slate-50 rounded-lg border border-slate-200 p-4">

            <div class="flex items-center justify-between">

                <span class="font-medium text-gray-700">
                    Grand Total
                </span>

                <span
                    id="grand-total-display"
                    class="text-xl font-bold text-gray-900"
                >
                    Rp 0
                </span>

            </div>

        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Catatan
            </label>

            <textarea
                name="notes"
                rows="4"
                class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                placeholder="Catatan invoice..."
            >{{ old('notes') }}</textarea>

            @error('notes')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">

            <a
                href="{{ route('invoices.index') }}"
                class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50"
            >
                Batal
            </a>

            <button
                type="submit"
                class="px-4 py-2 text-sm font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800"
            >
                Simpan Invoice
            </button>

        </div>

    </form>

</div>

<script>
    function calculateTotal() {
        const subtotal = parseFloat(
            document.getElementById('subtotal').value
        ) || 0;

        const discount = parseFloat(
            document.getElementById('discount').value
        ) || 0;

        const tax = parseFloat(
            document.getElementById('tax').value
        ) || 0;

        const total = subtotal - discount + tax;

        document.getElementById('grand-total-display').textContent =
            'Rp ' + new Intl.NumberFormat('id-ID').format(
                Math.max(total, 0)
            );
    }

    document.getElementById('subtotal')
        .addEventListener('input', calculateTotal);

    document.getElementById('discount')
        .addEventListener('input', calculateTotal);

    document.getElementById('tax')
        .addEventListener('input', calculateTotal);

    calculateTotal();
</script>

@endsection
