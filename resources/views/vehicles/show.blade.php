@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Kendaraan
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Informasi kendaraan motor customer.
            </p>
        </div>

        <div class="flex gap-2">

            <a
                href="{{ route('vehicles.edit', $vehicle) }}"
                class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800"
            >
                Edit
            </a>

            <a
                href="{{ route('vehicles.index') }}"
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50"
            >
                Kembali
            </a>

        </div>

    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>

                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $vehicle->plate_number }}
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ $vehicle->code }}
                    </p>

                </div>

                @if ($vehicle->is_active)

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
                    Kode Kendaraan
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $vehicle->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Customer
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $vehicle->customer->name ?? '-' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Nomor Polisi
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $vehicle->plate_number }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Merek
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $vehicle->brand }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Tipe Motor
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $vehicle->type }}
                </p>
            </div>

            <div class="md:col-span-2">

                <p class="text-xs font-medium text-gray-500 uppercase">
                    Catatan
                </p>

                <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                    {{ $vehicle->notes ?? '-' }}
                </p>

            </div>

        </div>

    </div>

</div>

@endsection
