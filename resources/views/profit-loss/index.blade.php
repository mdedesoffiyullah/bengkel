@extends('layouts.app')
@section('content')
<div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
    <div><h1 class="text-2xl font-bold text-slate-900">Laba & Rugi</h1><p class="text-sm text-slate-500 mt-1">Pantau profit berjalan, titik balik modal, dan arus kas periode berjalan.</p></div>

    <div class="bg-white border rounded-xl p-5"><form method="GET" action="{{ route('profit-loss.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end"><div><label class="block text-sm mb-1">Dari</label><input type="date" name="start_date" value="{{ $startDate }}" class="w-full rounded-lg border-slate-300"></div><div><label class="block text-sm mb-1">Sampai</label><input type="date" name="end_date" value="{{ $endDate }}" class="w-full rounded-lg border-slate-300"></div><button class="px-5 py-2.5 bg-slate-900 text-white rounded-lg">Tampilkan Laporan</button></form></div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white border rounded-xl p-5"><p class="text-sm text-slate-500">Pendapatan</p><p class="text-xl font-bold mt-2">Rp {{ number_format($totalRevenue,0,',','.') }}</p></div>
        <div class="bg-white border rounded-xl p-5"><p class="text-sm text-slate-500">Laba Kotor</p><p class="text-xl font-bold mt-2">Rp {{ number_format($grossProfit,0,',','.') }}</p></div>
        <div class="bg-white border rounded-xl p-5"><p class="text-sm text-slate-500">Laba Bersih Berjalan</p><p class="text-xl font-bold mt-2 {{ $netProfit < 0 ? 'text-red-600' : 'text-green-600' }}">Rp {{ number_format($netProfit,0,',','.') }}</p></div>
        <div class="bg-white border rounded-xl p-5"><p class="text-sm text-slate-500">Arus Kas Bersih</p><p class="text-xl font-bold mt-2">Rp {{ number_format($netCashFlow,0,',','.') }}</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 bg-white border rounded-xl p-6">
            <h2 class="font-semibold mb-5">Ringkasan Operasional</h2>
            <div class="space-y-4">
                <div class="flex justify-between"><span>Jasa</span><b>Rp {{ number_format($serviceRevenue,0,',','.') }}</b></div>
                <div class="flex justify-between"><span>Sparepart</span><b>Rp {{ number_format($productRevenue,0,',','.') }}</b></div>
                <div class="flex justify-between"><span>HPP sparepart yang sudah terpakai</span><b>Rp {{ number_format($productCost,0,',','.') }}</b></div>
                <div class="flex justify-between"><span>Expenses operasional</span><b>Rp {{ number_format($operatingExpenses,0,',','.') }}</b></div>
                <div class="pt-4 border-t flex justify-between text-lg"><b>Laba Bersih</b><b>Rp {{ number_format($netProfit,0,',','.') }}</b></div>
            </div>
        </div>
        <div class="bg-white border rounded-xl p-6">
            <h2 class="font-semibold mb-5">Balik Modal</h2>
            <p class="text-sm text-slate-500">Pendapatan minimum untuk menutup HPP + expenses periode ini.</p>
            <p class="text-2xl font-bold mt-3">Rp {{ number_format($breakEvenRevenue,0,',','.') }}</p>
            <div class="mt-5 p-4 rounded-lg {{ $breakEvenReached ? 'bg-green-50 text-green-700' : 'bg-amber-50 text-amber-700' }}">
                <b>{{ $breakEvenReached ? 'BALIK MODAL TERCAPAI' : 'BELUM BALIK MODAL' }}</b>
                @if(!$breakEvenReached)<p class="text-sm mt-1">Masih kurang Rp {{ number_format($breakEvenGap,0,',','.') }} pendapatan.</p>@else<p class="text-sm mt-1">Pendapatan sudah melewati titik impas.</p>@endif
            </div>
        </div>
    </div>

    <div class="bg-white border rounded-xl overflow-hidden">
        <div class="px-6 py-5 border-b"><h2 class="font-semibold">Arus Kas & Transaksi</h2><p class="text-sm text-slate-500 mt-1">Pembelian bukan langsung menjadi beban laba rugi; payment purchase dicatat sebagai uang keluar, sedangkan HPP muncul saat barang benar-benar dipakai.</p></div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <div class="flex justify-between"><span>Uang masuk customer</span><b class="text-green-600">+ Rp {{ number_format($customerReceipts,0,',','.') }}</b></div>
                <div class="flex justify-between"><span>Uang keluar purchase</span><b class="text-red-600">- Rp {{ number_format($purchasePayments,0,',','.') }}</b></div>
                <div class="flex justify-between"><span>Expenses dibayar/dicatat</span><b>- Rp {{ number_format($operatingExpenses,0,',','.') }}</b></div>
                <div class="pt-3 border-t flex justify-between"><b>Arus Kas Bersih</b><b>Rp {{ number_format($netCashFlow,0,',','.') }}</b></div>
            </div>
            <div class="space-y-4">
                <div class="flex justify-between"><span>Work Order selesai</span><b>{{ $completedWorkOrders }}</b></div>
                <div class="flex justify-between"><span>Customer payment</span><b>{{ $customerPaymentCount }}</b></div>
                <div class="flex justify-between"><span>Purchase payment</span><b>{{ $purchasePaymentCount }}</b></div>
                <div class="flex justify-between"><span>Purchase transaksi</span><b>{{ $purchaseCount }}</b></div>
            </div>
        </div>
    </div>
</div>
@endsection
