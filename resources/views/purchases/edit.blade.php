@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Edit Purchase</h1>
            <p class="text-muted mb-0">
                Perbarui transaksi pembelian.
            </p>
        </div>

        <a
            href="{{ route('purchases.show', $purchase) }}"
            class="btn btn-secondary"
        >
            Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">

            <strong>Periksa kembali data:</strong>

            <ul class="mb-0 mt-2">

                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>
    @endif

    <form
        action="{{ route('purchases.update', $purchase) }}"
        method="POST"
    >

        @csrf
        @method('PUT')

        <div class="card mb-4">

            <div class="card-header">
                <strong>Purchase Information</strong>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label for="code" class="form-label">
                            Purchase Code
                        </label>

                        <input
                            type="text"
                            id="code"
                            name="code"
                            value="{{ old('code', $purchase->code) }}"
                            class="form-control @error('code') is-invalid @enderror"
                            required
                        >

                        @error('code')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="supplier_id" class="form-label">
                            Supplier
                        </label>

                        <select
                            id="supplier_id"
                            name="supplier_id"
                            class="form-select @error('supplier_id') is-invalid @enderror"
                            required
                        >

                            <option value="">
                                -- Pilih Supplier --
                            </option>

                            @foreach ($suppliers as $supplier)

                                <option
                                    value="{{ $supplier->id }}"
                                    @selected(old('supplier_id', $purchase->supplier_id) == $supplier->id)
                                >
                                    {{ $supplier->name }}
                                </option>

                            @endforeach

                        </select>

                        @error('supplier_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label for="purchase_type" class="form-label">
                            Purchase Type
                        </label>

                        <select
                            id="purchase_type"
                            name="purchase_type"
                            class="form-select @error('purchase_type') is-invalid @enderror"
                            required
                        >

                            <option
                                value="GENERAL"
                                @selected(old('purchase_type', $purchase->purchase_type) === 'GENERAL')
                            >
                                GENERAL
                            </option>

                            <option
                                value="WO"
                                @selected(old('purchase_type', $purchase->purchase_type) === 'WO')
                            >
                                WORK ORDER
                            </option>

                        </select>

                        @error('purchase_type')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-6 mb-3">

                        <label for="work_order_id" class="form-label">
                            Work Order
                        </label>

                        <select
                            id="work_order_id"
                            name="work_order_id"
                            class="form-select @error('work_order_id') is-invalid @enderror"
                        >

                            <option value="">
                                -- Tidak menggunakan Work Order --
                            </option>

                            @foreach ($workOrders as $workOrder)

                                <option
                                    value="{{ $workOrder->id }}"
                                    @selected(old('work_order_id', $purchase->work_order_id) == $workOrder->id)
                                >
                                    {{ $workOrder->code ?? 'WO #' . $workOrder->id }}
                                </option>

                            @endforeach

                        </select>

                        @error('work_order_id')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label for="status" class="form-label">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required
                        >

                            @foreach ([
                                'DRAFT',
                                'ORDERED',
                                'PARTIAL',
                                'RECEIVED',
                                'CANCELLED'
                            ] as $status)

                                <option
                                    value="{{ $status }}"
                                    @selected(old('status', $purchase->status) === $status)
                                >
                                    {{ $status }}
                                </option>

                            @endforeach

                        </select>

                        @error('status')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-4 mb-3">

                        <label for="purchase_date" class="form-label">
                            Purchase Date
                        </label>

                        <input
                            type="date"
                            id="purchase_date"
                            name="purchase_date"
                            value="{{ old(
                                'purchase_date',
                                $purchase->purchase_date?->format('Y-m-d')
                            ) }}"
                            class="form-control @error('purchase_date') is-invalid @enderror"
                            required
                        >

                        @error('purchase_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-4 mb-3">

                        <label for="received_at" class="form-label">
                            Received At
                        </label>

                        <input
                            type="datetime-local"
                            id="received_at"
                            name="received_at"
                            value="{{ old(
                                'received_at',
                                $purchase->received_at?->format('Y-m-d\TH:i')
                            ) }}"
                            class="form-control @error('received_at') is-invalid @enderror"
                        >

                        @error('received_at')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

        <div class="card mb-4">

            <div class="card-header">
                <strong>Purchase Total</strong>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <label for="subtotal" class="form-label">
                            Subtotal
                        </label>

                        <input
                            type="number"
                            id="subtotal"
                            name="subtotal"
                            value="{{ old('subtotal', $purchase->subtotal) }}"
                            min="0"
                            step="0.01"
                            class="form-control @error('subtotal') is-invalid @enderror"
                        >

                        @error('subtotal')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-4 mb-3">

                        <label for="discount" class="form-label">
                            Discount
                        </label>

                        <input
                            type="number"
                            id="discount"
                            name="discount"
                            value="{{ old('discount', $purchase->discount) }}"
                            min="0"
                            step="0.01"
                            class="form-control @error('discount') is-invalid @enderror"
                        >

                        @error('discount')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="col-md-4 mb-3">

                        <label for="grand_total" class="form-label">
                            Grand Total
                        </label>

                        <input
                            type="number"
                            id="grand_total"
                            name="grand_total"
                            value="{{ old('grand_total', $purchase->grand_total) }}"
                            min="0"
                            step="0.01"
                            class="form-control @error('grand_total') is-invalid @enderror"
                        >

                        @error('grand_total')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>

        <div class="card mb-4">

            <div class="card-header">
                <strong>Notes</strong>
            </div>

            <div class="card-body">

                <textarea
                    name="notes"
                    id="notes"
                    rows="4"
                    class="form-control @error('notes') is-invalid @enderror"
                >{{ old('notes', $purchase->notes) }}</textarea>

                @error('notes')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror

            </div>

        </div>

        <div class="d-flex justify-content-end gap-2">

            <a
                href="{{ route('purchases.show', $purchase) }}"
                class="btn btn-secondary"
            >
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan Perubahan
            </button>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const purchaseType = document.getElementById('purchase_type');
    const workOrder = document.getElementById('work_order_id');

    function updateWorkOrderState() {

        if (purchaseType.value === 'WO') {
            workOrder.required = true;
        } else {
            workOrder.required = false;
            workOrder.value = '';
        }

    }

    purchaseType.addEventListener('change', updateWorkOrderState);

    updateWorkOrderState();
});
</script>
@endsection
