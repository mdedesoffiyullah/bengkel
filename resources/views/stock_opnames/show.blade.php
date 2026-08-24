@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">Detail Stock Opname</h1>
                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $stockOpname->status === 'CANCELLED' ? 'bg-red-100 text-red-700' : ($stockOpname->status === 'POSTED' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700') }}">
                    {{ $stockOpname->status }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-500">Perbandingan stok sistem dengan hasil perhitungan fisik.</p>
            <div class="mt-3 flex flex-wrap gap-x-6 gap-y-2 text-sm">
                <div><span class="text-gray-500">No. Opname:</span> <span class="font-semibold text-gray-900">{{ $stockOpname->code }}</span></div>
                <div><span class="text-gray-500">Tanggal:</span> <span class="font-semibold text-gray-900">{{ $stockOpname->opname_date?->format('d/m/Y') }}</span></div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stock-opnames.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50">&larr; Kembali</a>
            @if (!in_array($stockOpname->status, ['POSTED', 'CANCELLED']))
                <a href="{{ route('stock-opnames.edit', $stockOpname) }}" class="inline-flex items-center px-4 py-2 bg-slate-900 text-white text-sm font-medium rounded-lg hover:bg-slate-800">Edit Opname</a>
            @endif
        </div>
    </div>

    @if (session('success')) <div class="px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div> @endif
    @if ($stockOpname->notes)
        <div class="bg-white rounded-xl border border-gray-200 p-5"><div class="text-sm font-semibold text-gray-900">Catatan</div><div class="mt-2 text-sm text-gray-600">{{ $stockOpname->notes }}</div></div>
    @endif

    @php
        $totalItems = $stockOpname->items->count();
        $totalDifference = $stockOpname->items->sum(fn ($item) => (float) ($item->difference_value ?? 0));
        $totalDifferenceQty = $stockOpname->items->sum(fn ($item) => (float) ($item->difference_quantity ?? 0));
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-5"><div class="text-sm text-gray-500">Item Diperiksa</div><div class="mt-2 text-2xl font-bold text-gray-900">{{ $totalItems }}</div></div>
        <div class="bg-white rounded-xl border border-gray-200 p-5"><div class="text-sm text-gray-500">Total Selisih Qty</div><div class="mt-2 text-2xl font-bold {{ $totalDifferenceQty < 0 ? 'text-red-600' : ($totalDifferenceQty > 0 ? 'text-green-600' : 'text-gray-900') }}">{{ number_format($totalDifferenceQty, 0, ',', '.') }}</div></div>
        <div class="bg-white rounded-xl border border-gray-200 p-5"><div class="text-sm text-gray-500">Total Nilai Selisih</div><div class="mt-2 text-2xl font-bold {{ $totalDifference < 0 ? 'text-red-600' : ($totalDifference > 0 ? 'text-green-600' : 'text-gray-900') }}">Rp {{ number_format($totalDifference, 0, ',', '.') }}</div></div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div><h2 class="font-semibold text-gray-900">Pemeriksaan Stok Barang</h2><p class="mt-1 text-sm text-gray-500">Stok sistem adalah referensi. Isi stok fisik sesuai hasil hitungan nyata; kosong berarti belum diperiksa.</p></div>
                <div class="text-sm text-gray-500">{{ $products->count() }} produk aktif</div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200"><tr>
                    <th class="px-5 py-4 text-left font-semibold text-gray-600">Barang</th><th class="px-5 py-4 text-left font-semibold text-gray-600">Kode</th><th class="px-5 py-4 text-right font-semibold text-gray-600">Stok Sistem</th><th class="px-5 py-4 text-right font-semibold text-gray-600">Stok Fisik</th><th class="px-5 py-4 text-right font-semibold text-gray-600">Selisih</th><th class="px-5 py-4 text-right font-semibold text-gray-600">Harga Pokok</th><th class="px-5 py-4 text-right font-semibold text-gray-600">Nilai Selisih</th><th class="px-5 py-4 text-left font-semibold text-gray-600">Catatan</th><th class="px-5 py-4 text-right font-semibold text-gray-600">Aksi</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-200">
                @forelse ($products as $product)
                    @php
                        $item = $stockOpname->items->firstWhere('product_id', $product->id);
                        $balance = $product->inventoryBalance;
                        $systemQuantity = $balance ? (float) $balance->quantity : 0;
                        $unitCost = $balance ? (float) ($balance->average_cost ?? 0) : 0;
                        if ($unitCost <= 0) $unitCost = (float) ($product->last_buy_price ?? 0);
                        $hasPhysical = $item !== null;
                        $physicalQuantity = $hasPhysical ? (float) $item->physical_quantity : null;
                        $differenceQuantity = $hasPhysical ? (float) $item->difference_quantity : null;
                        $differenceValue = $hasPhysical ? (float) ($item->difference_value ?? 0) : null;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-4"><div class="font-semibold text-gray-900">{{ $product->name }}</div><div class="mt-1 text-xs text-gray-500">{{ $product->brand ?: 'Tanpa brand' }} · {{ $product->unit ?: 'PCS' }}</div></td>
                        <td class="px-5 py-4"><span class="font-mono text-xs text-gray-600">{{ $product->code }}</span></td>
                        <td class="px-5 py-4 text-right"><span class="font-semibold text-gray-900">{{ number_format($systemQuantity, 0, ',', '.') }}</span><div class="mt-1 text-xs {{ $balance ? 'text-green-600' : 'text-gray-400' }}">{{ $balance ? 'Inventory' : 'Belum ada balance' }}</div></td>
                        <td class="px-5 py-4">
                            @if (!in_array($stockOpname->status, ['POSTED', 'CANCELLED']))
                                <form method="POST" action="{{ route('stock-opnames.items.store', $stockOpname) }}" class="flex items-center justify-end gap-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="number" name="physical_quantity" min="0" step="1" value="{{ $hasPhysical ? number_format($physicalQuantity, 0, '.', '') : '' }}" placeholder="Isi hasil hitung" class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-right focus:ring-2 focus:ring-slate-500 focus:border-slate-500" required>
                            @else
                                <div class="text-right font-semibold text-gray-900">{{ $hasPhysical ? number_format($physicalQuantity, 0, ',', '.') : '-' }}</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right"><span class="font-bold {{ $hasPhysical ? ($differenceQuantity < 0 ? 'text-red-600' : ($differenceQuantity > 0 ? 'text-green-600' : 'text-gray-500')) : 'text-gray-400' }}">{{ $hasPhysical ? (($differenceQuantity > 0 ? '+' : '') . number_format($differenceQuantity, 0, ',', '.')) : '-' }}</span></td>
                        <td class="px-5 py-4 text-right whitespace-nowrap">Rp {{ number_format($unitCost, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right whitespace-nowrap"><span class="font-semibold {{ $hasPhysical ? ($differenceValue < 0 ? 'text-red-600' : ($differenceValue > 0 ? 'text-green-600' : 'text-gray-500')) : 'text-gray-400' }}">{{ $hasPhysical ? 'Rp ' . number_format($differenceValue, 0, ',', '.') : '-' }}</span></td>
                        <td class="px-5 py-4">
                            @if (!in_array($stockOpname->status, ['POSTED', 'CANCELLED']))
                                <input type="text" name="notes" value="{{ $item?->notes }}" placeholder="Catatan..." class="w-40 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            @else <span class="text-gray-500">{{ $item?->notes ?: '-' }}</span> @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            @if (!in_array($stockOpname->status, ['POSTED', 'CANCELLED']))
                                <button type="submit" class="inline-flex items-center px-3 py-2 bg-slate-900 text-white rounded-lg text-xs font-semibold hover:bg-slate-800">Simpan</button>
                                </form>
                            @else <span class="text-xs text-gray-400">Final</span> @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-6 py-12 text-center"><div class="text-gray-500">Belum ada produk aktif.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
