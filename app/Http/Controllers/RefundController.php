<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RefundController extends Controller
{
    /**
     * Menampilkan daftar refund.
     */
    public function index(Request $request)
    {
        $query = Refund::with([
            'invoice',
            'payment',
        ]);

        if ($request->filled('invoice_id')) {
            $query->where(
                'invoice_id',
                $request->invoice_id
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->status
            );
        }

        $refunds = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'refunds.index',
            compact('refunds')
        );
    }

    /**
     * Form membuat refund.
     */
    public function create(Request $request)
    {
        $payment = null;

        if ($request->filled('payment_id')) {
            $payment = Payment::with([
                'invoice',
            ])->findOrFail(
                $request->payment_id
            );
        }

        $payments = Payment::with([
            'invoice',
        ])
            ->where(
                'status',
                'POSTED'
            )
            ->latest()
            ->get();

        return view(
            'refunds.create',
            compact(
                'payment',
                'payments'
            )
        );
    }

    /**
     * Membuat refund.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'payment_id' => [
                'required',
                'integer',
                Rule::exists(
                    'payments',
                    'id'
                ),
            ],

            'refund_date' =>
                'required|date',

            'amount' =>
                'required|numeric|gt:0',

            'reason' =>
                'required|string|max:255',

            'notes' =>
                'nullable|string',
        ]);

        try {
            $refund = DB::transaction(
                function () use ($validated) {

                    $payment = Payment::where(
                        'id',
                        $validated['payment_id']
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    if (
                        $payment->status
                        !== 'POSTED'
                    ) {
                        throw new \RuntimeException(
                            'Payment tidak berstatus POSTED.'
                        );
                    }

                    $invoice = Invoice::where(
                        'id',
                        $payment->invoice_id
                    )
                        ->lockForUpdate()
                        ->firstOrFail();

                    /*
                     * Hitung refund yang sudah dilakukan
                     * terhadap payment ini.
                     */
                    $existingRefund =
                        (float) Refund::where(
                            'payment_id',
                            $payment->id
                        )
                            ->where(
                                'status',
                                'POSTED'
                            )
                            ->sum('amount');

                    $amount =
                        (float) $validated['amount'];

                    $remainingRefundable =
                        (float) $payment->amount
                        - $existingRefund;

                    if (
                        $amount
                        > $remainingRefundable
                    ) {
                        throw new \RuntimeException(
                            'Jumlah refund melebihi nilai payment yang masih dapat direfund.'
                        );
                    }

                    /*
                     * Buat refund.
                     */
                    $refund = Refund::create([
                        'payment_id' =>
                            $payment->id,

                        'invoice_id' =>
                            $invoice->id,

                        'refund_date' =>
                            $validated[
                                'refund_date'
                            ],

                        'amount' =>
                            $amount,

                        'reason' =>
                            $validated['reason'],

                        'status' =>
                            'POSTED',

                        'notes' =>
                            $validated['notes']
                            ?? null,
                    ]);

                    /*
                     * Hitung total payment efektif:
                     *
                     * Payment POSTED
                     * dikurangi
                     * Refund POSTED
                     */
                    $totalPaid =
                        (float) Payment::where(
                            'invoice_id',
                            $invoice->id
                        )
                            ->where(
                                'status',
                                'POSTED'
                            )
                            ->sum('amount');

                    $totalRefunded =
                        (float) Refund::where(
                            'invoice_id',
                            $invoice->id
                        )
                            ->where(
                                'status',
                                'POSTED'
                            )
                            ->sum('amount');

                    $netPaid =
                        $totalPaid
                        - $totalRefunded;

                    /*
                     * Update status invoice.
                     */
                    if (
                        $netPaid <= 0
                    ) {
                        $invoice->update([
                            'status' =>
                                'UNPAID',
                        ]);
                    } elseif (
                        $netPaid
                        < (
                            (float)
                            $invoice->total_amount
                        )
                    ) {
                        $invoice->update([
                            'status' =>
                                'PARTIAL',
                        ]);
                    } else {
                        $invoice->update([
                            'status' =>
                                'PAID',
                        ]);
                    }

                    return $refund;
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
                'refunds.show',
                $refund
            )
            ->with(
                'success',
                'Refund berhasil dicatat.'
            );
    }

    /**
     * Detail refund.
     */
    public function show(
        Refund $refund
    ) {
        $refund->load([
            'invoice',
            'payment',
        ]);

        return view(
            'refunds.show',
            compact('refund')
        );
    }

    /**
     * Refund tidak diedit.
     */
    public function edit(
        Refund $refund
    ) {
        return redirect()
            ->route(
                'refunds.show',
                $refund
            )
            ->with(
                'error',
                'Refund yang sudah diposting tidak dapat diedit.'
            );
    }

    /**
     * Update tidak diperbolehkan.
     */
    public function update(
        Request $request,
        Refund $refund
    ) {
        return redirect()
            ->route(
                'refunds.show',
                $refund
            )
            ->with(
                'error',
                'Refund tidak dapat diubah setelah diposting.'
            );
    }

    /**
     * Refund tidak dihapus.
     *
     * Koreksi dilakukan melalui transaksi reversal.
     */
    public function destroy(
        Refund $refund
    ) {
        return redirect()
            ->route(
                'refunds.show',
                $refund
            )
            ->with(
                'error',
                'Refund tidak dapat dihapus. Gunakan reversal untuk koreksi.'
            );
    }
}