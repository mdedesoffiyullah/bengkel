@extends('layouts.app')

@section('content')

<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">
            Tambah Kendaraan
        </h1>

        <p class="mt-1 text-sm text-gray-500">
            Tambahkan data kendaraan motor customer.
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

    <form action="{{ route('vehicles.store') }}"
          method="POST"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">

        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Kendaraan
                </label>

                <input
                    type="text"
                    name="code"
                    value="{{ old('code') }}"
                    required
                    maxlength="20"
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    placeholder="MTR-001"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Customer
                </label>

                <select
                    name="customer_id"
                    required
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                >
                    <option value="">Pilih Customer</option>

                    @foreach ($customers as $customer)
                        <option
                            value="{{ $customer->id }}"
                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                        >
                            {{ $customer->code }} - {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nomor Polisi
                </label>

                <input
                    type="text"
                    name="plate_number"
                    value="{{ old('plate_number') }}"
                    required
                    maxlength="20"
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    placeholder="B 1234 XYZ"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Merek Motor
                </label>

                <input
                    type="text"
                    name="brand"
                    value="{{ old('brand') }}"
                    required
                    maxlength="100"
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    placeholder="Honda"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipe Motor
                </label>

                <input
                    type="text"
                    name="type"
                    value="{{ old('type') }}"
                    required
                    maxlength="100"
                    class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                    placeholder="Vario 125"
                >
            </div>

        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Catatan
            </label>

            <textarea
                name="notes"
                rows="4"
                class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                placeholder="Catatan kendaraan (opsional)"
            >{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center gap-2">

            <input type="hidden" name="is_active" value="0">

            <input
                type="checkbox"
                name="is_active"
                value="1"
                {{ old('is_active', true) ? 'checked' : '' }}
                class="rounded border-gray-300 text-slate-900 focus:ring-slate-500"
            >

            <label class="text-sm text-gray-700">
                Kendaraan aktif
            </label>

        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">

            <a
                href="{{ route('vehicles.index') }}"
                class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50"
            >
                Batal
            </a>

            <button
                type="submit"
                class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800"
            >
                Simpan Kendaraan
            </button>

        </div>

    </form>

</div>

@endsection
