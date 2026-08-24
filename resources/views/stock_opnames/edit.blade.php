@extends('layouts.app')

@section('content')

<div class="space-y-6">

    <div class="flex items-center justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Edit Stock Opname
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Perbarui informasi pemeriksaan {{ $stockOpname->code }}.
            </p>
        </div>

        <a href="{{ route('stock-opnames.show', $stockOpname) }}"
           class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
            ← Kembali
        </a>

    </div>


    @if ($errors->any())
        <div class="px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">

            <div class="font-semibold mb-1">
                Periksa input berikut:
            </div>

            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>
    @endif


    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">

        <div class="px-6 py-5 border-b border-gray-200">

            <h2 class="font-semibold text-gray-900">
                Informasi Stock Opname
            </h2>

            <p class="mt-1 text-sm text-gray-500">
                Stock opname final tidak dapat diedit.
            </p>

        </div>


        <form method="POST" action="{{ route('stock-opnames.update', $stockOpname) }}">

            @csrf
            @method('PUT')

            <div class="p-6 space-y-6">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            No. Opname
                        </label>

                        <input
                            type="text"
                            value="{{ $stockOpname->code }}"
                            disabled
                            class="w-full rounded-lg border-gray-300 bg-gray-100 text-gray-500"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Opname
                        </label>

                        <input
                            type="datetime-local"
                            name="opname_date"
                            value="{{ old('opname_date', optional($stockOpname->opname_date)->format('Y-m-d\TH:i')) }}"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                        >
                    </div>


                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>

                        <select
                            name="status"
                            required
                            class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500"
                        >
                            <option value="DRAFT"
                                @selected(old('status', $stockOpname->status) === 'DRAFT')}>
                                DRAFT
                            </option>

                            <option value="IN_PROGRESS"
                                @selected(old('status', $stockOpname->status) === 'IN_PROGRESS')}>
                                IN PROGRESS
                            </option>
                        </select>
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
                    >{{ old('notes', $stockOpname->notes) }}</textarea>
                </div>

            </div>


            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end gap-3">

                <a href="{{ route('stock-opnames.show', $stockOpname) }}"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition">
                    Batal
                </a>

                <button
                    type="submit"
                    class="px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800 transition"
                >
                    Simpan Perubahan
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
