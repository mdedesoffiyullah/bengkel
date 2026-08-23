@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Purchase Detail</h1>
            <p class="text-muted mb-0">
                Detail transaksi pembelian.
            </p>
        </div>

        <div class="d-flex gap-2">

            @if ($purchase->status !== 'RECEIVED')
                <a
                    href="{{ route('purchases.edit', $purchase) }}"
                    class="btn btn-warning"
                >
                    Edit
                </a>
            @endif

            <a
                href="{{ route('purchases.index') }}"
                class="btn btn-secondary"
            >
                Kembali
            </a>

        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            <strong>Purchase Information</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Purchase Code
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->code }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Supplier
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->supplier?->name ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Purchase Type
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->purchase_type }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Work Order
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->workOrder?->code ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Status
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->status }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Purchase Date
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->purchase_date?->format('d/m/Y') ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Received At
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->received_at?->format('d/m/Y H:i') ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Created At
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->created_at?->format('d/m/Y H:i') ?? '-' }}
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <div class="text-muted small">
                        Created By
                    </div>

                    <div class="fw-bold">
                        {{ $purchase->creator?->name ?? '-' }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Purchase Items</strong>
        </div>

        <div class="card-body p-0">

            @if ($purchase->items->count())

                <div class="table-responsive">

                    <table class="table table-bordered mb-0">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Product</th>
                                <th>Quantity</th>
                                <th>Unit Price</th>
                                <th>Discount</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach ($purchase->items as $item)

                                <tr>

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        {{ $item->product?->name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $item->quantity ?? 0 }}
                                    </td>

                                    <td>
                                        Rp {{ number_format($item->unit_price ?? 0, 2, ',', '.') }}
                                    </td>

                                    <td>
                                        Rp {{ number_format($item->discount ?? 0, 2, ',', '.') }}
                                    </td>

                                    <td>
                                        Rp {{ number_format($item->subtotal ?? 0, 2, ',', '.') }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            @else

                <div class="p-4 text-muted">
                    Belum ada item purchase.
                </div>

            @endif

        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Purchase Total</strong>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-4">
                    <div class="text-muted small">
                        Subtotal
                    </div>

                    <div class="fs-5 fw-bold">
                        Rp {{ number_format($purchase->subtotal ?? 0, 2, ',', '.') }}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">
                        Discount
                    </div>

                    <div class="fs-5 fw-bold">
                        Rp {{ number_format($purchase->discount ?? 0, 2, ',', '.') }}
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="text-muted small">
                        Grand Total
                    </div>

                    <div class="fs-5 fw-bold">
                        Rp {{ number_format($purchase->grand_total ?? 0, 2, ',', '.') }}
                    </div>
                </div>

            </div>

        </div>
    </div>

    <div class="card">

        <div class="card-header">
            <strong>Notes</strong>
        </div>

        <div class="card-body">
            {{ $purchase->notes ?: '-' }}
        </div>

    </div>

</div>
@endsection
