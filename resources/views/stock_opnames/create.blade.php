@extends('layouts.app')

@section('content')

<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Buat Stock Opname
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Periksa stok fisik dan bandingkan dengan stok inventory sistem.
            </p>
        </div>

        <a href="{{ route('stock-opnames.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
            ← Kembali
        </a>

    </div>


    {{-- Error --}}
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <div class="font-semibold">Periksa input:</div>

            <ul class="mt-1 list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form method="POST"
          action="{{ route('stock-opnames.store') }}"
          class="space-y-6">

        @csrf

        {{-- Header opname --}}
        <div class="rounded-xl border border-gray-200 bg-white">

            <div class="border-b border-gray-200 px-6 py-5">

                <h2 class="font-semibold text-gray-900">
                    Informasi Stock Opname
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Tentukan nomor dan tanggal pemeriksaan stok.
                </p>

            </div>


            <div class="grid grid-cols-1 gap-5 p-6 md:grid-cols-3">

                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        No. Opname
                    </label>

                    <input type="text"
                           name="code"
                           value="{{ old('code', 'SO-' . now()->format('Ymd-His')) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">

                    <p class="mt-1 text-xs text-gray-500">
                        Nomor unik pemeriksaan.
                    </p>
                </div>


                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Tanggal Opname
                    </label>

                    <input type="date"
                           name="opname_date"
                           value="{{ old('opname_date', now()->format('Y-m-d')) }}"
                           required
                           class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">
                </div>


                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Status
                    </label>

                    <select name="status"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">

                        <option value="DRAFT"
                            {{ old('status', 'DRAFT') === 'DRAFT' ? 'selected' : '' }}>
                            DRAFT
                        </option>

                        <option value="IN_PROGRESS"
                            {{ old('status') === 'IN_PROGRESS' ? 'selected' : '' }}>
                            IN PROGRESS
                        </option>

                    </select>
                </div>


                <div class="md:col-span-3">
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Catatan
                    </label>

                    <textarea name="notes"
                              rows="3"
                              placeholder="Catatan pemeriksaan..."
                              class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-slate-500 focus:outline-none focus:ring-1 focus:ring-slate-500">{{ old('notes') }}</textarea>
                </div>

            </div>

        </div>


        {{-- Informasi alur --}}
        <div class="rounded-xl border border-blue-200 bg-blue-50 p-5">

            <div class="flex gap-3">

                <div class="text-blue-600">
                    ℹ
                </div>

                <div>

                    <div class="font-semibold text-blue-900">
                        Pemeriksaan barang dilakukan setelah opname dibuat
                    </div>

                    <p class="mt-1 text-sm text-blue-800">
                        Setelah menekan tombol Simpan Stock Opname,
                        sistem akan membuka daftar inventory sehingga setiap barang
                        dapat dibandingkan antara stok sistem dan stok fisik.
                    </p>

                </div>

            </div>

        </div>


        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3">

            <a href="{{ route('stock-opnames.index') }}"
               class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">
                Batal
            </a>

            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                Simpan Stock Opname →
            </button>

        </div>

    </form>

</div>

@endsection
