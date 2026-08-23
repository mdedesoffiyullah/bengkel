@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Expense
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Informasi lengkap pengeluaran bengkel.
            </p>
        </div>

        <a href="{{ route('expenses.index') }}"
           class="px-4 py-2 rounded-lg border bg-white hover:bg-gray-50">
            Kembali
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

        <div class="px-6 py-5 border-b flex items-center justify-between">

            <div>
                <p class="text-sm text-gray-500">
                    No. Expense
                </p>

                <p class="text-xl font-bold text-gray-900">
                    {{ $expense->code }}
                </p>
            </div>


            @if ($expense->status === 'POSTED')

                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                    POSTED
                </span>

            @elseif ($expense->status === 'CANCELLED')

                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                    CANCELLED
                </span>

            @else

                <span class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                    {{ $expense->status ?? '-' }}
                </span>

            @endif

        </div>


        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-sm text-gray-500">
                    Jenis Pengeluaran
                </p>

                <p class="mt-1 font-medium text-gray-900">
                    {{ $expense->expense_type ?: '-' }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Tanggal
                </p>

                <p class="mt-1 font-medium text-gray-900">
                    {{ $expense->expense_date?->format('d/m/Y') ?? '-' }}
                </p>
            </div>


            <div class="md:col-span-2">

                <p class="text-sm text-gray-500">
                    Keterangan
                </p>

                <p class="mt-1 font-medium text-gray-900">
                    {{ $expense->description }}
                </p>

            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Jumlah
                </p>

                <p class="mt-1 text-2xl font-bold text-red-600">
                    Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Metode Pembayaran
                </p>

                <p class="mt-1 font-medium text-gray-900">

                    {{ match($expense->payment_method) {
                        'CASH' => 'Cash',
                        'BANK_TRANSFER' => 'Bank Transfer',
                        'DEBIT_CARD' => 'Debit Card',
                        'CREDIT_CARD' => 'Credit Card',
                        'QRIS' => 'QRIS',
                        'OTHER' => 'Other',
                        default => $expense->payment_method ?? '-',
                    } }}

                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    No. Referensi
                </p>

                <p class="mt-1 font-medium text-gray-900">
                    {{ $expense->reference_number ?: '-' }}
                </p>
            </div>


            <div>
                <p class="text-sm text-gray-500">
                    Dibuat Oleh
                </p>

                <p class="mt-1 font-medium text-gray-900">
                    {{ $expense->creator?->name ?? '-' }}
                </p>
            </div>


            <div class="md:col-span-2">

                <p class="text-sm text-gray-500">
                    Catatan
                </p>

                <div class="mt-2 p-4 rounded-lg bg-gray-50 text-gray-700 whitespace-pre-line">
                    {{ $expense->notes ?: '-' }}
                </div>

            </div>

        </div>


        <div class="px-6 py-4 border-t flex justify-end gap-3">

            @if ($expense->status !== 'POSTED')

                <a href="{{ route('expenses.edit', $expense) }}"
                   class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
                    Edit
                </a>

            @endif


            @if ($expense->status !== 'CANCELLED')

                <form method="POST"
                      action="{{ route('expenses.destroy', $expense) }}"
                      onsubmit="return confirm('Yakin ingin membatalkan expense ini?')">

                    @csrf
                    @method('DELETE')

                    <button type="submit"
                            class="px-4 py-2 rounded-lg border border-red-300 text-red-600 text-sm font-medium hover:bg-red-50">
                        Batalkan
                    </button>

                </form>

            @endif

        </div>

    </div>

</div>

@endsection

