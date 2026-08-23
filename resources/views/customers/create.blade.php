@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Tambah Customer
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Tambahkan customer beserta data motor.
        </p>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-lg bg-red-50 border border-red-200 text-red-700">
            <ul class="list-disc list-inside text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        action="{{ route('customers.store') }}"
        method="POST"
        class="space-y-6"
    >

        @csrf

        {{-- CUSTOMER --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-5">
                Customer
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Kode Customer *
                    </label>

                    <input
                        type="text"
                        name="code"
                        value="{{ old('code') }}"
                        required
                        maxlength="20"
                        placeholder="CUS-001"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Customer *
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name') }}"
                        required
                        maxlength="255"
                        placeholder="Budi"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        No. Telepon
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="{{ old('phone') }}"
                        maxlength="30"
                        placeholder="08123456789"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>

                    <input
                        type="text"
                        name="notes"
                        value="{{ old('notes') }}"
                        placeholder="Customer langganan"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

            </div>

        </div>

        {{-- MOTOR --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">

            <h2 class="text-lg font-semibold text-gray-900 mb-5">
                Kendaraan / Motor
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Plat Nomor *
                    </label>

                    <input
                        type="text"
                        name="plate_number"
                        value="{{ old('plate_number') }}"
                        required
                        maxlength="20"
                        placeholder="B 1234 XYZ"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Brand *
                    </label>

                    <input
                        type="text"
                        name="brand"
                        value="{{ old('brand') }}"
                        required
                        maxlength="100"
                        placeholder="Honda"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Type Motor *
                    </label>

                    <input
                        type="text"
                        name="type"
                        value="{{ old('type') }}"
                        required
                        maxlength="100"
                        placeholder="Vario 125"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

            </div>

        </div>

        {{-- ACTION --}}
        <div class="flex justify-end gap-3">

            <a
                href="{{ route('customers.index') }}"
                class="px-5 py-3 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50"
            >
                Batal
            </a>

            <button
                type="submit"
                class="px-5 py-3 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800"
            >
                Simpan Customer
            </button>

        </div>

    </form>

</div>

@endsection