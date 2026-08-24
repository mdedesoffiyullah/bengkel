@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
        <p class="mt-1 text-sm text-gray-500">Buku transaksi uang masuk dan uang keluar.</p>
    </div>

    @if (session('success'))<div class="px-4 py-3 rounded-lg bg-green-100 border border-green-200 text-green-700 text-sm">{{ session('success') }}</div>@endif
    @if (session('error'))<div class="px-4 py-3 rounded-lg bg-red-100 border border-red-200 text-red-700 text-sm">{{ session('error') }}</div>@endif

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border p-5"><p class="text-sm text-gray-500">Total Transaksi</p><p class="mt-2 text-2xl font-bold">{{ $payments->total() }}</p></div>
        <div class="bg-white rounded-xl border p-5"><p class="text-sm text-gray-500">Customer Masuk</p><p class="mt-2 text-2xl font-bold text-green-600">Rp {{ number_format($customerReceived,0,',','.') }}</p></div>
        <div class="bg-white rounded-xl border p-5"><p class="text-sm text-gray-500">Purchase Keluar</p><p class="mt-2 text-2xl font-bold text-red-600">Rp {{ number_format($purchasePaid,0,',','.') }}</p></div>
        <div class="bg-white rounded-xl border p-5"><p class="text-sm text-gray-500">Arus Kas Bersih</p><p class="mt-2 text-2xl font-bold">Rp {{ number_format($customerReceived-$purchasePaid,0,',','.') }}</p></div>
    </div>

    <div class="flex justify-end"><a href="{{ route('payments.create') }}" class="px-5 py-2 rounded-lg bg-slate-900 text-white text-sm font-semibold">+ Catat Payment</a></div>

    <div class="bg-white rounded-xl border overflow-hidden">
        <div class="px-6 py-5 border-b"><h2 class="font-semibold">Buku Payment</h2><p class="mt-1 text-sm text-gray-500">Customer payment dan purchase payment berada dalam satu histori keuangan.</p></div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b"><tr>
                    <th class="px-6 py-4 text-left">Kode</th><th class="px-6 py-4 text-left">Tanggal</th><th class="px-6 py-4 text-left">Jenis</th><th class="px-6 py-4 text-left">Pihak / Dokumen</th><th class="px-6 py-4 text-left">Metode</th><th class="px-6 py-4 text-right">Jumlah</th><th class="px-6 py-4 text-right">Aksi</th>
                </tr></thead>
                <tbody class="divide-y">
                @forelse ($payments as $payment)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-semibold">{{ $payment->code }}</td>
                        <td class="px-6 py-4 text-gray-600">{{ $payment->paid_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        <td class="px-6 py-4"><span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium {{ $payment->transaction_type === 'PURCHASE_PAYMENT' ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">{{ $payment->transaction_type === 'PURCHASE_PAYMENT' ? 'PURCHASE / OUT' : 'CUSTOMER / IN' }}</span></td>
                        <td class="px-6 py-4">
                            @if ($payment->transaction_type === 'PURCHASE_PAYMENT')
                                <div class="font-medium">{{ $payment->purchase?->supplier?->name ?? '-' }}</div><div class="text-xs text-gray-500">{{ $payment->purchase?->code ?? '-' }}</div>
                            @else
                                <div class="font-medium">{{ $payment->workOrder?->customer?->name ?? 'Tanpa Customer' }}</div><div class="text-xs text-gray-500">{{ $payment->workOrder?->code ?? '-' }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4">{{ match($payment->method){'CASH'=>'Cash','BANK_TRANSFER'=>'Bank Transfer','DEBIT_CARD'=>'Debit Card','CREDIT_CARD'=>'Credit Card','QRIS'=>'QRIS','OTHER'=>'Other',default=>$payment->method} }}</td>
                        <td class="px-6 py-4 text-right font-semibold {{ $payment->transaction_type === 'PURCHASE_PAYMENT' ? 'text-red-600' : 'text-green-600' }}">{{ $payment->transaction_type === 'PURCHASE_PAYMENT' ? '-' : '+' }} Rp {{ number_format((float)$payment->amount,0,',','.') }}</td>
                        <td class="px-6 py-4 text-right"><a href="{{ route('payments.show',$payment) }}" class="px-3 py-1.5 text-xs rounded-lg border">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-12 text-center text-gray-500">Belum ada transaksi pembayaran.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($payments->hasPages())<div class="px-6 py-4 border-t">{{ $payments->links() }}</div>@endif
    </div>
</div>
@endsection
