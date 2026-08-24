@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Stock Opname
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola pemeriksaan dan penyesuaian stok fisik dengan stok sistem.
            </p>
        </div>

        <a href="{{ route('stock-opnames.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            + Buat Stock Opname
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


    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Daftar Stock Opname
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Total {{ $stockOpnames->total() }} pemeriksaan stok
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            No. Opname
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Gudang
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Selisih
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200">

                    @forelse ($stockOpnames as $stockOpname)

                        <tr class="hover:bg-gray-50">

                            {{-- Number --}}
                            <td class="px-6 py-4">

                                <div class="font-semibold text-gray-900">
                                    {{ $stockOpname->opname_number
                                        ?? $stockOpname->number
                                        ?? $stockOpname->code
                                        ?? '-' }}
                                </div>

                            </td>


                            {{-- Date --}}
                            <td class="px-6 py-4 text-gray-600">

                                {{ $stockOpname->created_at
                                    ? $stockOpname->created_at->format('d/m/Y')
                                    : '-' }}

                            </td>


                            {{-- Warehouse --}}
                            <td class="px-6 py-4 text-gray-900">

                                {{ $stockOpname->warehouse->name
                                    ?? $stockOpname->warehouse_name
                                    ?? '-' }}

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                @php

                                    $status = strtoupper(
                                        $stockOpname->status ?? 'DRAFT'
                                    );

                                    $statusClass = match ($status) {

                                        'COMPLETED',
                                        'APPROVED',
                                        'DONE',
                                        'SELESAI'
                                            => 'bg-green-100 text-green-700',

                                        'IN_PROGRESS',
                                        'PROCESSING',
                                        'COUNTING',
                                        'PROSES'
                                            => 'bg-blue-100 text-blue-700',

                                        'CANCELLED',
                                        'CANCELED',
                                        'BATAL'
                                            => 'bg-red-100 text-red-700',

                                        default
                                            => 'bg-yellow-100 text-yellow-700',

                                    };

                                @endphp

                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                    {{ $stockOpname->status ?? 'Draft' }}
                                </span>

                            </td>


                            {{-- Difference --}}
                            <td class="px-6 py-4 text-right font-medium">

                                @php
                                    $difference =
                                        $stockOpname->total_difference
                                        ?? $stockOpname->difference
                                        ?? $stockOpname->variance
                                        ?? 0;
                                @endphp

                                <span class="{{ $difference < 0 ? 'text-red-600' : ($difference > 0 ? 'text-green-600' : 'text-gray-600') }}">
                                    {{ number_format($difference, 0, ',', '.') }}
                                </span>

                            </td>


                            {{-- Actions --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('stock-opnames.show', $stockOpname) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                        Detail
                                    </a>


                                    <a href="{{ route('stock-opnames.edit', $stockOpname) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">
                                        Edit
                                    </a>


                                    <form action="{{ route('stock-opnames.destroy', $stockOpname) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus stock opname ini?')">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium rounded-lg border border-red-300 text-red-600 hover:bg-red-50 transition">
                                            Hapus
                                        </button>

                                    </form>

                                </div>

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td colspan="6" class="px-6 py-12 text-center">

                                <div class="text-gray-400 text-4xl mb-3">
                                    📋
                                </div>

                                <p class="text-gray-500">
                                    Belum ada data stock opname.
                                </p>

                                <a href="{{ route('stock-opnames.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                                    Buat Stock Opname
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($stockOpnames->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $stockOpnames->links() }}
            </div>

        @endif

    </div>

</div>

@endsection
