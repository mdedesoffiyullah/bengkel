@extends('layouts.app')
@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between"><div><h1 class="text-2xl font-bold">Detail Payment</h1><p class="mt-1 text-sm text-gray-500">Riwayat transaksi keuangan.</p></div><a href="{{ route('payments.index') }}" class="px-4 py-2 rounded-lg border">Kembali</a></div>
    @if(session('success'))<div class="px-4 py-3 rounded-lg bg-green-100 text-green-700">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="px-4 py-3 rounded-lg bg-red-100 text-red-700">{{ session('error') }}</div>@endif

    @php($purchasePayment = $payment->transaction_type === 'PURCHASE_PAYMENT')
    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-6 py-5 border-b flex items-center justify-between"><div><p class="text-sm text-gray-500">No. Payment</p><h2 class="text-xl font-bold">{{ $payment->code }}</h2></div><span class="px-3 py-1.5 rounded-full text-xs font-semibold {{ $purchasePayment ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $purchasePayment ? 'PURCHASE / UANG KELUAR' : 'CUSTOMER / UANG MASUK' }}</span></div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div><p class="text-sm text-gray-500">Tanggal</p><p class="mt-1 font-semibold">{{ $payment->paid_at?->format('d/m/Y H:i') ?? '-' }}</p></div>
            <div><p class="text-sm text-gray-500">Pihak</p><p class="mt-1 font-semibold">{{ $purchasePayment ? ($payment->purchase?->supplier?->name ?? '-') : ($payment->workOrder?->customer?->name ?? 'Tanpa Customer') }}</p></div>
            <div><p class="text-sm text-gray-500">Dokumen</p><p class="mt-1 font-semibold">{{ $purchasePayment ? ($payment->purchase?->code ?? '-') : ($payment->workOrder?->code ?? '-') }}</p></div>
            <div><p class="text-sm text-gray-500">Metode</p><p class="mt-1 font-semibold">{{ match($payment->method){'CASH'=>'Cash','BANK_TRANSFER'=>'Bank Transfer','DEBIT_CARD'=>'Debit Card','CREDIT_CARD'=>'Credit Card','QRIS'=>'QRIS','OTHER'=>'Other',default=>$payment->method} }}</p></div>
            <div><p class="text-sm text-gray-500">Referensi</p><p class="mt-1 font-semibold">{{ $payment->reference_number ?? '-' }}</p></div>
            <div><p class="text-sm text-gray-500">Jumlah</p><p class="mt-1 text-xl font-bold {{ $purchasePayment ? 'text-red-600' : 'text-green-600' }}">{{ $purchasePayment ? '-' : '+' }} Rp {{ number_format((float)$payment->amount,0,',','.') }}</p></div>
        </div>
    </div>

    @if($purchasePayment && $payment->purchase)
        <div class="bg-white rounded-xl border p-6 space-y-4"><h2 class="font-semibold">Ringkasan Purchase</h2><div class="flex justify-between"><span>Total Purchase</span><b>Rp {{ number_format((float)$payment->purchase->grand_total,0,',','.') }}</b></div><div class="flex justify-between"><span>Total Dibayar</span><b>Rp {{ number_format((float)$payment->purchase->payments->sum('amount'),0,',','.') }}</b></div><div class="pt-4 border-t flex justify-between"><b>Sisa Hutang Supplier</b><b>Rp {{ number_format(max(0,(float)$payment->purchase->grand_total-(float)$payment->purchase->payments->sum('amount')),0,',','.') }}</b></div></div>
    @elseif($payment->workOrder)
        <div class="bg-white rounded-xl border p-6 space-y-4"><h2 class="font-semibold">Ringkasan Work Order</h2><div class="flex justify-between"><span>Total WO</span><b>Rp {{ number_format((float)$payment->workOrder->grand_total,0,',','.') }}</b></div><div class="flex justify-between"><span>Total Dibayar</span><b>Rp {{ number_format((float)$payment->workOrder->payments->sum('amount'),0,',','.') }}</b></div><div class="pt-4 border-t flex justify-between"><b>Sisa Tagihan</b><b>Rp {{ number_format(max(0,(float)$payment->workOrder->grand_total-(float)$payment->workOrder->payments->sum('amount')),0,',','.') }}</b></div></div>
    @endif

    @if($payment->notes)<div class="bg-white rounded-xl border p-6"><h2 class="font-semibold mb-2">Catatan</h2><p class="text-sm whitespace-pre-line">{{ $payment->notes }}</p></div>@endif
    <div class="text-xs text-gray-400">Payment tercatat sebagai transaksi finansial dan tidak dapat diedit/hapus.</div>
</div>
@endsection
