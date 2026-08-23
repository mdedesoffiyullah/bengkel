@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Complaints
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola keluhan dan komplain pelanggan.
            </p>
        </div>

        <a href="{{ route('complaints.create') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition">
            + Buat Complaint
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


    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Total --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Total Complaint
            </p>

            <p class="mt-2 text-2xl font-bold text-gray-900">
                {{ $complaints->total() }}
            </p>

        </div>


        {{-- Open --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Complaint Terbuka
            </p>

            <p class="mt-2 text-2xl font-bold text-yellow-600">
                {{ $openComplaints ?? 0 }}
            </p>

        </div>


        {{-- Resolved --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">

            <p class="text-sm text-gray-500">
                Complaint Selesai
            </p>

            <p class="mt-2 text-2xl font-bold text-green-600">
                {{ $resolvedComplaints ?? 0 }}
            </p>

        </div>

    </div>


    {{-- Complaint Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Daftar Complaint
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Riwayat keluhan pelanggan.
            </p>

        </div>


        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-200">

                    <tr>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            No. Complaint
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Tanggal
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Customer
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Judul / Keluhan
                        </th>

                        <th class="px-6 py-4 text-left font-semibold text-gray-600">
                            Status
                        </th>

                        <th class="px-6 py-4 text-right font-semibold text-gray-600">
                            Aksi
                        </th>

                    </tr>

                </thead>


                <tbody class="divide-y divide-gray-200">

                    @forelse ($complaints as $complaint)

                        @php

                            $status = strtoupper(
                                $complaint->status ?? 'OPEN'
                            );

                            $statusClass = match ($status) {

                                'RESOLVED',
                                'CLOSED',
                                'SELESAI'
                                    => 'bg-green-100 text-green-700',

                                'IN_PROGRESS',
                                'PROCESS',
                                'DIPROSES'
                                    => 'bg-blue-100 text-blue-700',

                                'CANCELLED',
                                'CANCELED',
                                'BATAL'
                                    => 'bg-red-100 text-red-700',

                                default
                                    => 'bg-yellow-100 text-yellow-700',

                            };

                        @endphp


                        <tr class="hover:bg-gray-50">

                            {{-- Complaint Number --}}
                            <td class="px-6 py-4">

                                <div class="font-semibold text-gray-900">

                                    {{ $complaint->complaint_number
                                        ?? $complaint->number
                                        ?? $complaint->code
                                        ?? '-' }}

                                </div>

                            </td>


                            {{-- Date --}}
                            <td class="px-6 py-4 text-gray-600">

                                {{ $complaint->complaint_date
                                    ? \Carbon\Carbon::parse($complaint->complaint_date)->format('d/m/Y')
                                    : ($complaint->created_at
                                        ? $complaint->created_at->format('d/m/Y')
                                        : '-') }}

                            </td>


                            {{-- Customer --}}
                            <td class="px-6 py-4">

                                <div class="font-medium text-gray-900">

                                    {{ $complaint->customer->name
                                        ?? $complaint->customer_name
                                        ?? '-' }}

                                </div>

                            </td>


                            {{-- Complaint --}}
                            <td class="px-6 py-4">

                                <div class="max-w-sm">

                                    <p class="font-medium text-gray-900 truncate">

                                        {{ $complaint->title
                                            ?? $complaint->subject
                                            ?? $complaint->complaint
                                            ?? 'Complaint' }}

                                    </p>

                                    @if (
                                        isset($complaint->description) &&
                                        $complaint->description
                                    )

                                        <p class="text-xs text-gray-500 mt-1 truncate">

                                            {{ $complaint->description }}

                                        </p>

                                    @endif

                                </div>

                            </td>


                            {{-- Status --}}
                            <td class="px-6 py-4">

                                <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full {{ $statusClass }}">

                                    {{ $complaint->status ?? 'Open' }}

                                </span>

                            </td>


                            {{-- Actions --}}
                            <td class="px-6 py-4">

                                <div class="flex items-center justify-end gap-2">

                                    <a href="{{ route('complaints.show', $complaint) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                                        Detail
                                    </a>


                                    <a href="{{ route('complaints.edit', $complaint) }}"
                                       class="px-3 py-1.5 text-xs font-medium rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">
                                        Edit
                                    </a>


                                    <form action="{{ route('complaints.destroy', $complaint) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus complaint ini?')">

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
                                    💬
                                </div>

                                <p class="text-gray-500">
                                    Belum ada complaint.
                                </p>

                                <a href="{{ route('complaints.create') }}"
                                   class="inline-block mt-4 px-4 py-2 bg-slate-900 text-white text-sm rounded-lg hover:bg-slate-800 transition">
                                    Buat Complaint
                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- Pagination --}}
        @if ($complaints->hasPages())

            <div class="px-6 py-4 border-t border-gray-200">

                {{ $complaints->links() }}

            </div>

        @endif

    </div>

</div>

@endsection