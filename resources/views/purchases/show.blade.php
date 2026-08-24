@extends('layouts.app')
@section('content')
<div class="max-w-6xl mx-auto space-y-6 py-6">
    <div class="flex items-center justify-between"><div><h1 class="text-2xl font-bold">Purchase Detail</h1><p class="text-sm text-gray-500">Detail pembelian, inventory, dan pembayaran supplier.</p></div><a href="{{ route('purchases.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a></div>
    @if(session('success'))<div class="px-4 py-3 rounded-lg bg-green-100 text-green-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="px-4 py-3 rounded-lg bg-red-100 text-red-700">{{ session('error') }}</div>@endif

    @php($paid=(float)$purchase->payments->sum('amount'))
    @php($remaining=max(0,(float)$purchase->grand_total-$paid))
    <div class="bg-white rounded-xl border p-6"><div class="grid grid-cols-1 md:grid-cols-4 gap-6"><div><p class="text-xs text-gray-500">Purchase Code</p><b>{{ $purchase->code }}</b></div><div><p class="text-xs text-gray-500">Supplier</p><b>{{ $purchase->supplier?->name ?? '-' }}</b></div><div><p class="text-xs text-gray-500">Status</p><b>{{ $purchase->status }}</b></div><div><p class="text-xs text-gray-500">Tipe</p><b>{{ $purchase->purchase_type }}</b></div></div></div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border rounded-xl p-5"><p class="text-sm text-gray-500">Grand Total Purchase</p><p class="text-2xl font-bold mt-2">Rp {{ number_format((float)$purchase->grand_total,0,',','.') }}</p></div>
        <div class="bg-white border rounded-xl p-5"><p class="text-sm text-gray-500">Sudah Dibayar Supplier</p><p class="text-2xl font-bold mt-2 text-red-600">Rp {{ number_format($paid,0,',','.') }}</p></div>
        <div class="bg-white border rounded-xl p-5"><p class="text-sm text-gray-500">Sisa Hutang</p><p class="text-2xl font-bold mt-2">Rp {{ number_format($remaining,0,',','.') }}</p></div>
    </div>

    @if($remaining > 0 && !in_array($purchase->status,['CANCELLED','DRAFT']))
        <div><a href="{{ route('payments.create',['purchase_id'=>$purchase->id]) }}" class="inline-flex px-5 py-2.5 rounded-lg bg-slate-900 text-white font-semibold">+ Bayar Purchase / Supplier</a></div>
    @endif

    <div class="bg-white rounded-xl border overflow-hidden"><div class="px-6 py-5 border-b"><h2 class="font-semibold">Purchase Items</h2></div><div class="overflow-x-auto"><table class="w-full text-sm"><thead class="bg-gray-50"><tr><th class="px-6 py-4 text-left">#</th><th class="px-6 py-4 text-left">Product</th><th class="px-6 py-4 text-right">Qty</th><th class="px-6 py-4 text-right">Received</th><th class="px-6 py-4 text-right">Unit Cost</th><th class="px-6 py-4 text-right">Subtotal</th></tr></thead><tbody class="divide-y">@forelse($purchase->items as $item)<tr><td class="px-6 py-4">{{ $loop->iteration }}</td><td class="px-6 py-4 font-medium">{{ $item->product?->name ?? '-' }}</td><td class="px-6 py-4 text-right">{{ $item->quantity }}</td><td class="px-6 py-4 text-right">{{ $item->received_quantity }}</td><td class="px-6 py-4 text-right">Rp {{ number_format((float)$item->unit_cost,0,',','.') }}</td><td class="px-6 py-4 text-right font-semibold">Rp {{ number_format((float)$item->subtotal,0,',','.') }}</td></tr>@empty<tr><td colspan="6" class="px-6 py-10 text-center text-gray-500">Belum ada item.</td></tr>@endforelse</tbody></table></div></div>

    <div class="bg-white rounded-xl border overflow-hidden"><div class="px-6 py-5 border-b"><h2 class="font-semibold">Riwayat Pembayaran Supplier</h2></div><div class="p-6">@forelse($purchase->payments->sortByDesc('paid_at') as $payment)<div class="flex justify-between border-b py-3 last:border-0"><div><b>{{ $payment->code }}</b><div class="text-xs text-gray-500">{{ $payment->paid_at?->format('d/m/Y H:i') }} · {{ $payment->method }}</div></div><b class="text-red-600">- Rp {{ number_format((float)$payment->amount,0,',','.') }}</b></div>@empty<p class="text-gray-500">Belum ada pembayaran supplier.</p>@endforelse</div></div>

    @if($purchase->notes)<div class="bg-white rounded-xl border p-6"><h2 class="font-semibold mb-2">Catatan</h2><p class="whitespace-pre-line text-sm">{{ $purchase->notes }}</p></div>@endif
</div>
@endsection
