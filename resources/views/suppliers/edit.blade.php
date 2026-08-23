@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Supplier</h1>
        <p class="mt-1 text-sm text-gray-500">
            Perbarui informasi supplier.
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

    <form action="{{ route('suppliers.update', $supplier) }}" method="POST"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Supplier
                </label>
                <input type="text"
                       name="code"
                       value="{{ old('code', $supplier->code) }}"
                       required
                       maxlength="20"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Supplier
                </label>
                <input type="text"
                       name="name"
                       value="{{ old('name', $supplier->name) }}"
                       required
                       maxlength="255"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Contact Person
                </label>
                <input type="text"
                       name="contact_person"
                       value="{{ old('contact_person', $supplier->contact_person) }}"
                       maxlength="255"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    No. Telepon
                </label>
                <input type="text"
                       name="phone"
                       value="{{ old('phone', $supplier->phone) }}"
                       maxlength="30"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Email
                </label>
                <input type="email"
                       name="email"
                       value="{{ old('email', $supplier->email) }}"
                       maxlength="100"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Alamat
            </label>
            <textarea name="address"
                      rows="4"
                      class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">{{ old('address', $supplier->address) }}</textarea>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Catatan
            </label>
            <textarea name="notes"
                      rows="3"
                      class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">{{ old('notes', $supplier->notes) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">

            <input type="checkbox"
                   name="is_active"
                   value="1"
                   {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-slate-900 focus:ring-slate-500">

            <label class="text-sm text-gray-700">
                Supplier aktif
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">

            <a href="{{ route('suppliers.show', $supplier) }}"
               class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium hover:bg-gray-50">
                Batal
            </a>

            <button type="submit"
                    class="px-4 py-2 rounded-lg bg-slate-900 text-white text-sm font-medium hover:bg-slate-800">
                Simpan Perubahan
            </button>

        </div>

    </form>
</div>
@endsection
