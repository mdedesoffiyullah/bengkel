@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Buat Payment
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Catat pembayaran pelanggan untuk Work Order yang telah selesai.
            </p>
        </div>

        <a href="{{ route('payments.index') }}"
           class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50">
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4">
            <div class="font-semibold text-red-700 mb-2">
                Terdapat kesalahan:
            </div>

            <ul class="list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('payments.store') }}"
          method="POST"
          class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        @csrf

        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">
                Informasi Pembayaran
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Pilih Work Order yang telah selesai, lalu masukkan jumlah pembayaran.
            </p>
        </div>

        <div class="p-6 space-y-6">

            {{-- Work Order --}}
            <div>
                <label for="work_order_id"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Work Order
                </label>

                <select name="work_order_id"
                        id="work_order_id"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">

                    <option value="">
                        -- Pilih Work Order --
                    </option>

                    @foreach ($workOrders as $item)

                        <option value="{{ $item->id }}"
                            {{ old('work_order_id', $workOrder?->id) == $item->id ? 'selected' : '' }}>

                            {{ $item->code }}
                            —
                            {{ $item->customer?->name ?? 'Tanpa Customer' }}
                            —
                            Rp {{ number_format($item->grand_total, 0, ',', '.') }}

                        </option>

                    @endforeach

                </select>

                <p class="mt-1 text-xs text-gray-500">
                    Hanya Work Order dengan status COMPLETED yang dapat menerima pembayaran.
                </p>
            </div>

            {{-- Tanggal --}}
            <div>
                <label for="paid_at"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Tanggal Pembayaran
                </label>

                <input type="datetime-local"
                       name="paid_at"
                       id="paid_at"
                       value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}"
                       required
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            {{-- Amount --}}
            <div>
                <label for="amount"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Jumlah Dibayar
                </label>

                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">
                        Rp
                    </span>

                    <input type="number"
                           name="amount"
                           id="amount"
                           min="1"
                           step="0.01"
                           value="{{ old('amount') }}"
                           required
                           class="w-full rounded-lg border-gray-300 pl-10 focus:border-slate-500 focus:ring-slate-500">
                </div>

                <p class="mt-1 text-xs text-gray-500">
                    Jumlah pembayaran tidak boleh melebihi sisa tagihan Work Order.
                </p>
            </div>

            {{-- Method --}}
            <div>
                <label for="method"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Metode Pembayaran
                </label>

                <select name="method"
                        id="method"
                        required
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">

                    <option value="">
                        -- Pilih Metode --
                    </option>

                    <option value="CASH" {{ old('method') === 'CASH' ? 'selected' : '' }}>
                        Cash
                    </option>

                    <option value="BANK_TRANSFER" {{ old('method') === 'BANK_TRANSFER' ? 'selected' : '' }}>
                        Bank Transfer
                    </option>

                    <option value="DEBIT_CARD" {{ old('method') === 'DEBIT_CARD' ? 'selected' : '' }}>
                        Debit Card
                    </option>

                    <option value="CREDIT_CARD" {{ old('method') === 'CREDIT_CARD' ? 'selected' : '' }}>
                        Credit Card
                    </option>

                    <option value="QRIS" {{ old('method') === 'QRIS' ? 'selected' : '' }}>
                        QRIS
                    </option>

                    <option value="OTHER" {{ old('method') === 'OTHER' ? 'selected' : '' }}>
                        Other
                    </option>

                </select>
            </div>

            {{-- Reference --}}
            <div>
                <label for="reference_number"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Nomor Referensi
                    <span class="font-normal text-gray-400">
                        (opsional)
                    </span>
                </label>

                <input type="text"
                       name="reference_number"
                       id="reference_number"
                       value="{{ old('reference_number') }}"
                       maxlength="100"
                       placeholder="Contoh: nomor transaksi / referensi transfer"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes"
                       class="block text-sm font-medium text-gray-700 mb-2">
                    Catatan
                </label>

                <textarea name="notes"
                          id="notes"
                          rows="3"
                          class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                          placeholder="Catatan pembayaran...">{{ old('notes') }}</textarea>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">

            <a href="{{ route('payments.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-white">
                Batal
            </a>

            <button type="submit"
                    class="px-5 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800">
                Simpan Payment
            </button>

        </div>

    </form>

</div>

@endsection