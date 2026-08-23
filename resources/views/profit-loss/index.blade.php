@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">
                Laba & Rugi
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Laporan pendapatan, HPP, biaya operasional, dan laba bersih.
            </p>
        </div>
    </div>

    {{-- Filter Periode --}}
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-6">
        <form method="GET" action="{{ route('profit-loss.index') }}"
              class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Dari Tanggal
                </label>
                <input
                    type="date"
                    name="start_date"
                    value="{{ $startDate }}"
                    class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">
                    Sampai Tanggal
                </label>
                <input
                    type="date"
                    name="end_date"
                    value="{{ $endDate }}"
                    class="w-full rounded-lg border-slate-300 focus:border-slate-500 focus:ring-slate-500"
                >
            </div>

            <div>
                <button
                    type="submit"
                    class="w-full md:w-auto px-5 py-2.5 bg-slate-900 text-white rounded-lg hover:bg-slate-800 transition"
                >
                    Tampilkan Laporan
                </button>
            </div>

        </form>
    </div>

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">

        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">Total Pendapatan</p>
            <p class="text-xl font-bold text-slate-900 mt-2">
                Rp {{ number_format($totalRevenue, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">HPP Sparepart</p>
            <p class="text-xl font-bold text-slate-900 mt-2">
                Rp {{ number_format($productCost, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">Laba Kotor</p>
            <p class="text-xl font-bold text-slate-900 mt-2">
                Rp {{ number_format($grossProfit, 0, ',', '.') }}
            </p>
        </div>

        <div class="bg-white border border-slate-200 rounded-xl p-5">
            <p class="text-sm text-slate-500">Laba Bersih</p>
            <p class="text-xl font-bold text-slate-900 mt-2">
                Rp {{ number_format($netProfit, 0, ',', '.') }}
            </p>
        </div>

    </div>

    {{-- Detail Laporan --}}
    <div class="bg-white border border-slate-200 rounded-xl overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-200">
            <h2 class="text-lg font-semibold text-slate-900">
                Detail Laba & Rugi
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Periode {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}
                sampai
                {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </p>
        </div>

        <div class="p-6">

            {{-- Pendapatan --}}
            <div class="mb-8">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-4">
                    Pendapatan
                </h3>

                <div class="space-y-3">

                    <div class="flex justify-between">
                        <span>Jasa</span>
                        <span class="font-medium">
                            Rp {{ number_format($serviceRevenue, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span>Sparepart</span>
                        <span class="font-medium">
                            Rp {{ number_format($productRevenue, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="border-t border-slate-200 pt-3 flex justify-between font-semibold">
                        <span>Total Pendapatan</span>
                        <span>
                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                        </span>
                    </div>

                </div>
            </div>

            {{-- HPP --}}
            <div class="mb-8">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-4">
                    Harga Pokok Penjualan
                </h3>

                <div class="flex justify-between">
                    <span>HPP Sparepart</span>
                    <span class="font-medium">
                        Rp {{ number_format($productCost, 0, ',', '.') }}
                    </span>
                </div>

                <div class="border-t border-slate-200 mt-3 pt-3 flex justify-between font-semibold">
                    <span>Laba Kotor</span>
                    <span>
                        Rp {{ number_format($grossProfit, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Expenses --}}
            <div class="mb-8">
                <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-500 mb-4">
                    Biaya Operasional
                </h3>

                <div class="flex justify-between">
                    <span>Expenses Operasional</span>
                    <span class="font-medium">
                        Rp {{ number_format($operatingExpenses, 0, ',', '.') }}
                    </span>
                </div>
            </div>

            {{-- Net Profit --}}
            <div class="border-t-2 border-slate-900 pt-5">
                <div class="flex justify-between items-center">
                    <span class="text-lg font-bold">
                        LABA BERSIH
                    </span>

                    <span class="text-2xl font-bold">
                        Rp {{ number_format($netProfit, 0, ',', '.') }}
                    </span>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection
