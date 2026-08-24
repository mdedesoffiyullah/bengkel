@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Buat Payment</h1>
            <p class="mt-1 text-sm text-gray-500">Catat uang masuk dari customer atau uang keluar untuk supplier.</p>
        </div>
        <a href="{{ route('payments.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-sm">Kembali</a>
    </div>

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-600">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('payments.store') }}" method="POST" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        @csrf
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="font-semibold text-gray-900">Informasi Transaksi Keuangan</h2>
            <p class="mt-1 text-sm text-gray-500">Payment customer = uang masuk. Payment purchase = uang keluar ke supplier.</p>
        </div>

        <div class="p-6 space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Transaksi</label>
                <select name="transaction_type" id="transaction_type" required class="w-full rounded-lg border-gray-300">
                    <option value="CUSTOMER_PAYMENT" {{ old('transaction_type', 'CUSTOMER_PAYMENT') === 'CUSTOMER_PAYMENT' ? 'selected' : '' }}>Customer Payment — Uang Masuk</option>
                    <option value="PURCHASE_PAYMENT" {{ old('transaction_type') === 'PURCHASE_PAYMENT' ? 'selected' : '' }}>Purchase Payment — Uang Keluar Supplier</option>
                </select>
            </div>

            <div id="customer_box">
                <label class="block text-sm font-medium text-gray-700 mb-2">Work Order</label>
                <select name="work_order_id" id="work_order_id" class="w-full rounded-lg border-gray-300">
                    <option value="">-- Pilih Work Order --</option>
                    @foreach ($workOrders as $item)
                        <option value="{{ $item->id }}" {{ old('work_order_id', $workOrder?->id) == $item->id ? 'selected' : '' }}>
                            {{ $item->code }} — {{ $item->customer?->name ?? 'Tanpa Customer' }} — Rp {{ number_format($item->grand_total, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="purchase_box" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Purchase / Supplier</label>
                <select name="purchase_id" id="purchase_id" class="w-full rounded-lg border-gray-300">
                    <option value="">-- Pilih Purchase --</option>
                    @foreach ($purchases as $purchase)
                        <option value="{{ $purchase->id }}" {{ old('purchase_id') == $purchase->id ? 'selected' : '' }}>
                            {{ $purchase->code }} — {{ $purchase->supplier?->name ?? '-' }} — Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Pembayaran</label>
                <input type="datetime-local" name="paid_at" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required class="w-full rounded-lg border-gray-300">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                <div class="flex items-center gap-2"><span>Rp</span><input type="number" name="amount" min="1" step="0.01" value="{{ old('amount') }}" required class="w-full rounded-lg border-gray-300"></div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Metode Pembayaran</label>
                <select name="method" required class="w-full rounded-lg border-gray-300">
                    @foreach (['CASH'=>'Cash','BANK_TRANSFER'=>'Bank Transfer','DEBIT_CARD'=>'Debit Card','CREDIT_CARD'=>'Credit Card','QRIS'=>'QRIS','OTHER'=>'Other'] as $value => $label)
                        <option value="{{ $value }}" {{ old('method') === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div><label class="block text-sm font-medium text-gray-700 mb-2">Nomor Referensi <span class="font-normal text-gray-400">(opsional)</span></label><input type="text" name="reference_number" value="{{ old('reference_number') }}" maxlength="100" class="w-full rounded-lg border-gray-300"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-2">Catatan</label><textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300">{{ old('notes') }}</textarea></div>
        </div>

        <div class="px-6 py-4 bg-gray-50 border-t flex justify-end gap-3">
            <a href="{{ route('payments.index') }}" class="px-4 py-2 rounded-lg border">Batal</a>
            <button type="submit" class="px-5 py-2 rounded-lg bg-slate-900 text-white font-semibold">Simpan Payment</button>
        </div>
    </form>
</div>

<script>
(function () {
    const type = document.getElementById('transaction_type');
    const customer = document.getElementById('customer_box');
    const purchase = document.getElementById('purchase_box');
    const wo = document.getElementById('work_order_id');
    const po = document.getElementById('purchase_id');
    function toggle() {
        const isPurchase = type.value === 'PURCHASE_PAYMENT';
        customer.classList.toggle('hidden', isPurchase);
        purchase.classList.toggle('hidden', !isPurchase);
        wo.disabled = isPurchase;
        po.disabled = !isPurchase;
        wo.required = !isPurchase;
        po.required = isPurchase;
    }
    type.addEventListener('change', toggle);
    toggle();
})();
</script>
@endsection
