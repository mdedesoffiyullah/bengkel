@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-gray-900">Edit Kategori Produk</h1>
        <p class="mt-1 text-sm text-gray-500">Perbarui data kategori produk.</p>
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

    <form action="{{ route('product-categories.update', $productCategory) }}" method="POST"
          class="bg-white rounded-xl border border-gray-200 p-6 space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Kode Kategori
                </label>
                <input type="text" name="code"
                       value="{{ old('code', $productCategory->code) }}"
                       required maxlength="20"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Kategori
                </label>
                <input type="text" name="name"
                       value="{{ old('name', $productCategory->name) }}"
                       required maxlength="255"
                       class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">
            </div>

        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Deskripsi
            </label>
            <textarea name="description" rows="4"
                      class="w-full rounded-lg border-gray-300 focus:border-slate-500 focus:ring-slate-500">{{ old('description', $productCategory->description) }}</textarea>
        </div>

        <div class="flex items-center gap-2">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   {{ old('is_active', $productCategory->is_active) ? 'checked' : '' }}
                   class="rounded border-gray-300 text-slate-900 focus:ring-slate-500">
            <label class="text-sm text-gray-700">
                Kategori aktif
            </label>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="{{ route('product-categories.show', $productCategory) }}"
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
