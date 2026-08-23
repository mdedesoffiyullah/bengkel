@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Edit Customer
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Perbarui data customer dan motor.
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
        action="{{ route('customers.update', $customer) }}"
        method="POST"
        class="space-y-6"
    >

        @csrf
        @method('PUT')

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
                        value="{{ old('code', $customer->code) }}"
                        required
                        maxlength="20"
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
                        value="{{ old('name', $customer->name) }}"
                        required
                        maxlength="255"
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
                        value="{{ old('phone', $customer->phone) }}"
                        maxlength="30"
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
                        value="{{ old('notes', $customer->notes) }}"
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
                        value="{{ old('plate_number', $customer->plate_number) }}"
                        required
                        maxlength="20"
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
                        value="{{ old('brand', $customer->brand) }}"
                        required
                        maxlength="100"
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
                        value="{{ old('type', $customer->type) }}"
                        required
                        maxlength="100"
                        class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    >
                </div>

            </div>

        </div>

        {{-- STATUS --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">

            <div class="flex items-center gap-2">

                <input
                    type="hidden"
                    name="is_active"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="is_active"
                    value="1"
                    {{ old('is_active', $customer->is_active) ? 'checked' : '' }}
                    class="rounded border-gray-300 text-slate-900 focus:ring-slate-500"
                >

                <label class="text-sm text-gray-700">
                    Customer aktif
                </label>

            </div>

        </div>

        <div class="flex justify-end gap-3">

            <a
                href="{{ route('customers.show', $customer) }}"
                class="px-5 py-3 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50"
            >
                Batal
            </a>

            <button
                type="submit"
                class="px-5 py-3 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800"
            >
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>

@endsection