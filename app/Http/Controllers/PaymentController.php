<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    /**
     * Menampilkan daftar pembayaran.
     */
    public function index(Request $request)
    {
        $query = Payment::with([
            'workOrder.customer',
        ]);

        if ($request->filled('work_order_id')) {
            $query->where(
                'work_order_id',
                $request->work_order_id
            );
        }

        $payments = $query
            ->latest('paid_at')
            ->paginate(20)
            ->withQueryString();

        $totalPaid = (float) Payment::sum('amount');

        $todayPayments = Payment::whereDate(
            'paid_at',
            today()
        )->count();

        return view(
            'payments.index',
            compact(
                'payments',
                'totalPaid',
                'todayPayments'
            )
        );
    }

    /**
     * Form pembayaran.
     */
    public function create(Request $request)
    {
        $workOrder = null;

        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with('customer')
                ->where('status', 'COMPLETED')
                ->findOrFail(
                    $request->work_order_id
                );
        }

        /*
         * Hanya Work Order COMPLETED yang boleh menerima pembayaran.
         */
        $workOrders = WorkOrder::with('customer')
            ->where('status', 'COMPLETED')
            ->latest('completed_at')
            ->get()
            ->filter(function ($workOrder) {
                $totalPaid = (float) Payment::where(
                    'work_order_id',
                    $workOrder->id
                )->sum('amount');

                return $totalPaid < (float) $workOrder->grand_total;
            });

        return view(
            'payments.create',
            compact(
                'workOrder',
                'workOrders'
            )
        );
    }

    /**
     * Menyimpan pembayaran.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'work_order_id' => [
                'required',
                'integer',
                Rule::exists(
                    'work_orders',
                    'id'
                ),
            ],

            'paid_at' =>
                'required|date',

            'amount' =>
                'required|numeric|gt:0',

            'method' => [
                'required',
                Rule::in([
                    'CASH',
                    'BANK_TRANSFER',
                    'DEBIT_CARD',
                    'CREDIT_CARD',
                    'QRIS',
                    'OTHER',
                ]),
            ],

            'reference_number' =>
                'nullable|string|max:100',

            'notes' =>
                'nullable|string',
        ]);

        try {
            $payment = DB::transaction(
                function () use ($validated) {

                    $workOrder = WorkOrder::where(
                        'id',
                        $validated['work_order_id']
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($workOrder->status !== 'COMPLETED') {
                        throw new \RuntimeException(
                            'Work Order belum berstatus COMPLETED sehingga belum dapat menerima pembayaran.'
                        );
                    }

                    $totalPaid =
                        (float) Payment::where(
                            'work_order_id',
                            $workOrder->id
                        )->sum('amount');

                    $grandTotal =
                        (float) $workOrder->grand_total;

                    $remaining =
                        $grandTotal - $totalPaid;

                    $amount =
                        (float) $validated['amount'];

                    if ($remaining <= 0) {
                        throw new \RuntimeException(
                            'Work Order ini sudah lunas.'
                        );
                    }

                    if ($amount > $remaining) {
                        throw new \RuntimeException(
                            'Jumlah pembayaran melebihi sisa tagihan Work Order.'
                        );
                    }

                    $payment = Payment::create([
                        'code' =>
                            'PAY-' .
                            now()->format('YmdHis'),

                        'work_order_id' =>
                            $workOrder->id,

                        'paid_at' =>
                            $validated['paid_at'],

                        'amount' =>
                            $amount,

                        'method' =>
                            $validated['method'],

                        'reference_number' =>
                            $validated[
                                'reference_number'
                            ] ?? null,

                        'notes' =>
                            $validated['notes']
                            ?? null,
                    ]);

                    return $payment;
                }
            );

        } catch (\RuntimeException $e) {

            return back()
                ->withErrors([
                    'amount' =>
                        $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()
            ->route(
                'payments.show',
                $payment
            )
            ->with(
                'success',
                'Pembayaran berhasil dicatat.'
            );
    }

    /**
     * Detail pembayaran.
     */
    public function show(
        Payment $payment
    ) {
        $payment->load([
            'workOrder.customer',
        ]);

        return view(
            'payments.show',
            compact('payment')
        );
    }

    /**
     * Pembayaran yang sudah dicatat tidak diedit.
     */
    public function edit(
        Payment $payment
    ) {
        return redirect()
            ->route(
                'payments.show',
                $payment
            )
            ->with(
                'error',
                'Pembayaran yang sudah dicatat tidak dapat diedit.'
            );
    }

    /**
     * Update tidak diperbolehkan.
     */
    public function update(
        Request $request,
        Payment $payment
    ) {
        return redirect()
            ->route(
                'payments.show',
                $payment
            )
            ->with(
                'error',
                'Pembayaran tidak dapat diubah setelah dicatat.'
            );
    }

    /**
     * Pembayaran tidak dihapus.
     */
    public function destroy(
        Payment $payment
    ) {
        return redirect()
            ->route(
                'payments.show',
                $payment
            )
            ->with(
                'error',
                'Pembayaran tidak dapat dihapus. Gunakan proses koreksi/refund.'
            );
    }
}
