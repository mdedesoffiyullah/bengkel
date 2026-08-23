@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Expenses
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola pengeluaran operasional bengkel.
            </p>
        </div>

        <a href="{{ route('expenses.create') }}"
           class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
            + Buat Expense
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


    <form method="GET"
          action="{{ route('expenses.index') }}"
          class="bg-white rounded-xl border border-gray-200 p-5">

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">

            <div>
                <label class="block text-sm font-medium mb-1">
                    Jenis Pengeluaran
                </label>

                <input type="text"
                       name="expense_type"
                       value="{{ request('expense_type') }}"
                       placeholder="Cari jenis pengeluaran"
                       class="w-full rounded-lg border-gray-300">
            </div>


            <div>
                <label class="block text-sm font-medium mb-1">
                    Status
                </label>

                <select name="status"
                        class="w-full rounded-lg border-gray-300">

                    <option value="">
                        Semua Status
                    </option>

                    <option value="POSTED"
                        @selected(request('status') === 'POSTED')>
                        POSTED
                    </option>

                    <option value="CANCELLED"
                        @selected(request('status') === 'CANCELLED')>
                        CANCELLED
                    </option>

                </select>
            </div>


            <div>
                <label class="block text-sm font-medium mb-1">
                    Dari
                </label>

                <input type="date"
                       name="date_from"
                       value="{{ request('date_from') }}"
                       class="w-full rounded-lg border-gray-300">
            </div>


            <div>
                <label class="block text-sm font-medium mb-1">
                    Sampai
                </label>

                <input type="date"
                       name="date_to"
                       value="{{ request('date_to') }}"
                       class="w-full rounded-lg border-gray-300">
            </div>

        </div>


        <div class="mt-4 flex justify-end gap-2">

            <a href="{{ route('expenses.index') }}"
               class="px-4 py-2 rounded-lg border bg-white text-sm hover:bg-gray-50">
                Reset
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm hover:bg-slate-800">
                Filter
            </button>

        </div>

    </form>


    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Total Expense
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ $expenses->total() }}
            </p>

        </div>


        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Total Pengeluaran
            </p>

            <p class="mt-2 text-2xl font-bold text-red-600">
                Rp {{ number_format((float) $totalExpense, 0, ',', '.') }}
            </p>

        </div>

    </div>


    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b">

            <h2 class="font-semibold text-gray-900">
                Daftar Expense
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Riwayat pengeluaran bengkel.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            No. Expense
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Jenis Pengeluaran
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Keterangan
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Status
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

                    @forelse ($expenses as $expense)

                        <tr class="hover:bg-gray-50">

                            <td class="px-6 py-4 font-semibold text-gray-900">
                                {{ $expense->code }}
                            </td>


                            <td class="px-6 py-4 text-gray-600">
                                {{ $expense->expense_date?->format('d/m/Y') ?? '-' }}
                            </td>


                            <td class="px-6 py-4 text-gray-600">
                                {{ $expense->expense_type ?: '-' }}
                            </td>


                            <td class="px-6 py-4 text-gray-600">

                                <div class="max-w-xs truncate">
                                    {{ $expense->description }}
                                </div>

                            </td>


                            <td class="px-6 py-4">

                                @if ($expense->status === 'POSTED')

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">
                                        POSTED
                                    </span>

                                @else

                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">
                                        CANCELLED
                                    </span>

                                @endif

                            </td>


                            <td class="px-6 py-4 text-right font-semibold text-red-600">
                                Rp {{ number_format((float) $expense->amount, 0, ',', '.') }}
                            </td>


                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('expenses.show', $expense) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border hover:bg-gray-50">
                                        Detail
                                    </a>

                                    @if ($expense->status !== 'POSTED')

                                        <a href="{{ route('expenses.edit', $expense) }}"
                                           class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800">
                                            Edit
                                        </a>

                                    @endif

                                    @if ($expense->status !== 'CANCELLED')

                                        <form action="{{ route('expenses.destroy', $expense) }}"
                                              method="POST"
                                              onsubmit="return confirm('Yakin ingin membatalkan expense ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-300 text-red-600 hover:bg-red-50">
                                                Batalkan
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

                                <div class="text-4xl mb-3">
                                    💸
                                </div>

                                <p class="text-gray-500">
                                    Belum ada pengeluaran.
                                </p>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if ($expenses->hasPages())

            <div class="px-6 py-4 border-t">
                {{ $expenses->links() }}
            </div>

        @endif

    </div>

</div>

@endsection

