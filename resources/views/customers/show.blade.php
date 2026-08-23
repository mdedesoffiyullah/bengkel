@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Customer
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Informasi customer dan motor.
            </p>
        </div>

        <div class="flex gap-2">

            <a
                href="{{ route('customers.edit', $customer) }}"
                class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800"
            >
                Edit
            </a>

            <a
                href="{{ route('customers.index') }}"
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50"
            >
                Kembali
            </a>

        </div>

    </div>

    {{-- CUSTOMER --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $customer->name }}
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ $customer->code }}
                    </p>
                </div>

                @if ($customer->is_active)

                    <span class="px-3 py-1 rounded-full bg-green-100 text-green-700 text-xs font-medium">
                        Aktif
                    </span>

                @else

                    <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-xs font-medium">
                        Tidak Aktif
                    </span>

                @endif

            </div>

        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Kode Customer
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $customer->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Nama Customer
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $customer->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    No. Telepon
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $customer->phone ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Catatan
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $customer->notes ?? '-' }}
                </p>
            </div>

        </div>

    </div>

    {{-- MOTOR --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="text-lg font-semibold text-gray-900">
                Kendaraan / Motor
            </h2>

        </div>

        <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Plat Nomor
                </p>

                <p class="mt-1 text-sm font-medium text-gray-900">
                    {{ $customer->plate_number ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Brand
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $customer->brand ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Type Motor
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $customer->type ?? '-' }}
                </p>
            </div>

        </div>

    </div>

</div>

@endsection