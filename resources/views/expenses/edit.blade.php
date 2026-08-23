@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Edit Expense
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui data pengeluaran.
            </p>
        </div>

        <a href="{{ route('expenses.show', $expense) }}"
           class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">
            Kembali
        </a>

    </div>


    @if ($errors->any())

        <div class="p-4 rounded-lg bg-red-50 border border-red-200">

            <ul class="text-sm text-red-700 space-y-1">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    <form method="POST"
          action="{{ route('expenses.update', $expense) }}"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">

        @csrf
        @method('PUT')


        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

            <div>
                <label class="block text-sm font-medium mb-1">
                    Jenis Pengeluaran
                </label>

                <input type="text"
                       name="expense_type"
                       value="{{ old('expense_type', $expense->expense_type) }}"
                       required
                       maxlength="100"
                       placeholder="Contoh: Gaji, Listrik, Pembelian, Operasional"
                       class="w-full rounded-lg border-gray-300">
            </div>


            <div>
                <label class="block text-sm font-medium mb-1">
                    Tanggal
                </label>

                <input type="date"
                       name="expense_date"
                       value="{{ old('expense_date', $expense->expense_date?->format('Y-m-d')) }}"
                       required
                       class="w-full rounded-lg border-gray-300">
            </div>


            <div class="md:col-span-2">

                <label class="block text-sm font-medium mb-1">
                    Keterangan
                </label>

                <input type="text"
                       name="description"
                       value="{{ old('description', $expense->description) }}"
                       required
                       maxlength="255"
                       class="w-full rounded-lg border-gray-300">

            </div>


            <div>
                <label class="block text-sm font-medium mb-1">
                    Jumlah
                </label>

                <input type="number"
                       name="amount"
                       value="{{ old('amount', $expense->amount) }}"
                       min="0.01"
                       step="0.01"
                       required
                       class="w-full rounded-lg border-gray-300">
            </div>


            <div>
                <label class="block text-sm font-medium mb-1">
                    Metode Pembayaran
                </label>

                <select name="payment_method"
                        required
                        class="w-full rounded-lg border-gray-300">

                    @foreach ([
                        'CASH' => 'Cash',
                        'BANK_TRANSFER' => 'Bank Transfer',
                        'DEBIT_CARD' => 'Debit Card',
                        'CREDIT_CARD' => 'Credit Card',
                        'QRIS' => 'QRIS',
                        'OTHER' => 'Other',
                    ] as $value => $label)

                        <option value="{{ $value }}"
                            @selected(old('payment_method', $expense->payment_method) === $value)>

                            {{ $label }}

                        </option>

                    @endforeach

                </select>
            </div>


            <div>
                <label class="block text-sm font-medium mb-1">
                    No. Referensi
                </label>

                <input type="text"
                       name="reference_number"
                       value="{{ old('reference_number', $expense->reference_number) }}"
                       maxlength="100"
                       class="w-full rounded-lg border-gray-300">
            </div>


            <div class="md:col-span-2">

                <label class="block text-sm font-medium mb-1">
                    Catatan
                </label>

                <textarea name="notes"
                          rows="4"
                          class="w-full rounded-lg border-gray-300">{{ old('notes', $expense->notes) }}</textarea>

            </div>

        </div>


        <div class="flex justify-end gap-3 pt-4 border-t">

            <a href="{{ route('expenses.show', $expense) }}"
               class="px-5 py-2.5 rounded-lg border bg-white hover:bg-gray-50">
                Batal
            </a>

            <button type="submit"
                    class="px-5 py-2.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800">
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>

@endsection

