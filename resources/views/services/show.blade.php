@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Detail Jasa
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Informasi lengkap jasa servis.
            </p>
        </div>

        <div class="flex gap-2">

            <a href="{{ route('services.edit', $service) }}"
               class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
                Edit
            </a>

            <a href="{{ route('services.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50">
                Kembali
            </a>

        </div>

    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <div class="flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        {{ $service->name }}
                    </h2>

                    <p class="text-sm text-gray-500">
                        {{ $service->code }}
                    </p>
                </div>

                @if ($service->is_active)

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
                    Kode Jasa
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $service->code }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Nama Jasa
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $service->name }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Harga Default
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    Rp {{ number_format($service->default_price, 2, ',', '.') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-medium text-gray-500 uppercase">
                    Status
                </p>

                <p class="mt-1 text-sm text-gray-900">
                    {{ $service->is_active ? 'Aktif' : 'Tidak Aktif' }}
                </p>
            </div>

            <div class="md:col-span-2">

                <p class="text-xs font-medium text-gray-500 uppercase">
                    Deskripsi
                </p>

                <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">
                    {{ $service->description ?? '-' }}
                </p>

            </div>

        </div>

    </div>

</div>
@endsection
