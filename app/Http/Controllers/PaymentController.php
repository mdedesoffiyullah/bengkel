<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['workOrder.customer', 'purchase.supplier']);

        if ($request->filled('transaction_type')) {
            $query->where('transaction_type', $request->transaction_type);
        }

        if ($request->filled('work_order_id')) {
            $query->where('work_order_id', $request->work_order_id);
        }

        $payments = $query->latest('paid_at')->paginate(20)->withQueryString();
        $totalPaid = (float) Payment::sum('amount');
        $customerReceived = (float) Payment::where('transaction_type', 'CUSTOMER_PAYMENT')->sum('amount');
        $purchasePaid = (float) Payment::where('transaction_type', 'PURCHASE_PAYMENT')->sum('amount');
        $todayPayments = Payment::whereDate('paid_at', today())->count();

        return view('payments.index', compact(
            'payments', 'totalPaid', 'customerReceived', 'purchasePaid', 'todayPayments'
        ));
    }

    public function create(Request $request)
    {
        $workOrder = null;
        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with('customer')->findOrFail($request->work_order_id);
        }

        $workOrders = WorkOrder::with('customer')
            ->whereNotIn('status', ['CANCELLED'])
            ->latest('opened_at')
            ->get()
            ->filter(function ($workOrder) {
                $paid = (float) Payment::where('transaction_type', 'CUSTOMER_PAYMENT')
                    ->where('work_order_id', $workOrder->id)->sum('amount');
                return $paid < (float) $workOrder->grand_total;
            });

        $purchases = Purchase::with('supplier')
            ->whereNotIn('status', ['CANCELLED', 'DRAFT'])
            ->latest('purchase_date')
            ->get()
            ->filter(function ($purchase) {
                $paid = (float) Payment::where('transaction_type', 'PURCHASE_PAYMENT')
                    ->where('purchase_id', $purchase->id)->sum('amount');
                return $paid < (float) $purchase->grand_total;
            });

        return view('payments.create', compact('workOrder', 'workOrders', 'purchases'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => ['required', Rule::in(['CUSTOMER_PAYMENT', 'PURCHASE_PAYMENT'])],
            'work_order_id' => ['nullable', 'integer', 'exists:work_orders,id'],
            'purchase_id' => ['nullable', 'integer', 'exists:purchases,id'],
            'paid_at' => 'required|date',
            'amount' => 'required|numeric|gt:0',
            'method' => ['required', Rule::in(['CASH','BANK_TRANSFER','DEBIT_CARD','CREDIT_CARD','QRIS','OTHER'])],
            'reference_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            $payment = DB::transaction(function () use ($validated) {
                $type = $validated['transaction_type'];
                $amount = (float) $validated['amount'];

                if ($type === 'CUSTOMER_PAYMENT') {
                    if (empty($validated['work_order_id'])) {
                        throw new \RuntimeException('Work Order wajib dipilih untuk pembayaran customer.');
                    }
                    if (!empty($validated['purchase_id'])) {
                        throw new \RuntimeException('Pembayaran customer tidak boleh terhubung ke Purchase.');
                    }

                    $workOrder = WorkOrder::whereKey($validated['work_order_id'])->lockForUpdate()->firstOrFail();
                    $paid = (float) Payment::where('transaction_type', $type)
                        ->where('work_order_id', $workOrder->id)->sum('amount');
                    $remaining = (float) $workOrder->grand_total - $paid;

                    if ($remaining <= 0) throw new \RuntimeException('Work Order ini sudah lunas.');
                    if ($amount > $remaining) throw new \RuntimeException('Pembayaran melebihi sisa tagihan Work Order.');
                } else {
                    if (empty($validated['purchase_id'])) {
                        throw new \RuntimeException('Purchase wajib dipilih untuk pembayaran supplier.');
                    }
                    if (!empty($validated['work_order_id'])) {
                        throw new \RuntimeException('Pembayaran supplier tidak boleh terhubung ke Work Order.');
                    }

                    $purchase = Purchase::whereKey($validated['purchase_id'])->lockForUpdate()->firstOrFail();
                    $paid = (float) Payment::where('transaction_type', $type)
                        ->where('purchase_id', $purchase->id)->sum('amount');
                    $remaining = (float) $purchase->grand_total - $paid;

                    if ($remaining <= 0) throw new \RuntimeException('Purchase ini sudah lunas.');
                    if ($amount > $remaining) throw new \RuntimeException('Pembayaran melebihi sisa hutang Purchase.');
                }

                return Payment::create([
                    'code' => 'PAY-' . now()->format('YmdHisv'),
                    'transaction_type' => $type,
                    'work_order_id' => $validated['work_order_id'] ?? null,
                    'purchase_id' => $validated['purchase_id'] ?? null,
                    'paid_at' => $validated['paid_at'],
                    'amount' => $amount,
                    'method' => $validated['method'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } catch (\RuntimeException $e) {
            return back()->withErrors(['amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('payments.show', $payment)->with('success', 'Transaksi pembayaran berhasil dicatat.');
    }

    public function show(Payment $payment)
    {
        $payment->load(['workOrder.customer', 'purchase.supplier', 'purchase.items.product']);
        return view('payments.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return redirect()->route('payments.show', $payment)->with('error', 'Pembayaran yang sudah dicatat tidak dapat diedit.');
    }

    public function update(Request $request, Payment $payment)
    {
        return redirect()->route('payments.show', $payment)->with('error', 'Pembayaran tidak dapat diubah setelah dicatat.');
    }

    public function destroy(Payment $payment)
    {
        return redirect()->route('payments.show', $payment)->with('error', 'Pembayaran tidak dapat dihapus. Gunakan proses koreksi/refund.');
    }
}
