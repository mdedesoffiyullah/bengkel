<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with([
            'customer',
            'workOrder',
        ]);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query
            ->latest('invoice_date')
            ->paginate(20)
            ->withQueryString();

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $workOrder = null;

        if ($request->filled('work_order_id')) {
            $workOrder = WorkOrder::with('customer')
                ->findOrFail($request->work_order_id);
        }

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        $workOrders = WorkOrder::whereNotIn('status', ['CANCELLED'])
            ->with('customer')
            ->latest()
            ->get();

        return view('invoices.create', compact(
            'workOrder',
            'customers',
            'workOrders'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:30',
                'unique:invoices,code',
            ],

            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id')
                    ->where('is_active', true),
            ],

            'work_order_id' => [
                'nullable',
                'integer',
                'exists:work_orders,id',
            ],

            'invoice_date' => 'required|date',

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:invoice_date',
            ],

            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',

            'notes' => 'nullable|string',
        ]);

        $subtotal = (float) $validated['subtotal'];
        $discount = (float) ($validated['discount'] ?? 0);
        $tax = (float) ($validated['tax'] ?? 0);

        if ($discount > $subtotal) {
            return back()
                ->withErrors([
                    'discount' => 'Discount tidak boleh lebih besar dari subtotal.',
                ])
                ->withInput();
        }

        $grandTotal = $subtotal - $discount + $tax;

        $invoice = DB::transaction(function () use (
            $validated,
            $subtotal,
            $discount,
            $tax,
            $grandTotal
        ) {
            if (!empty($validated['work_order_id'])) {
                $workOrder = WorkOrder::findOrFail(
                    $validated['work_order_id']
                );

                if (
                    (int) $workOrder->customer_id !==
                    (int) $validated['customer_id']
                ) {
                    throw new \RuntimeException(
                        'Customer invoice tidak sesuai dengan customer Work Order.'
                    );
                }
            }

            return Invoice::create([
                'code' => $validated['code'],
                'customer_id' => $validated['customer_id'],
                'work_order_id' => $validated['work_order_id'] ?? null,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['due_date'] ?? null,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'grand_total' => $grandTotal,
                'paid_amount' => 0,
                'status' => 'UNPAID',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
        });

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'customer',
            'workOrder',
            'payments',
        ]);

        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with(
                    'error',
                    'Invoice yang sudah memiliki pembayaran tidak dapat diedit.'
                );
        }

        $customers = Customer::where('is_active', true)
            ->orderBy('name')
            ->get();

        $workOrders = WorkOrder::with('customer')
            ->whereNotIn('status', ['CANCELLED'])
            ->latest()
            ->get();

        return view('invoices.edit', compact(
            'invoice',
            'customers',
            'workOrders'
        ));
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return back()->with(
                'error',
                'Invoice yang sudah memiliki pembayaran tidak dapat diubah.'
            );
        }

        $validated = $request->validate([
            'invoice_date' => 'required|date',

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:invoice_date',
            ],

            'subtotal' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',

            'status' => [
                'required',
                Rule::in([
                    'UNPAID',
                    'CANCELLED',
                ]),
            ],
        ]);

        $subtotal = (float) $validated['subtotal'];
        $discount = (float) ($validated['discount'] ?? 0);
        $tax = (float) ($validated['tax'] ?? 0);

        if ($discount > $subtotal) {
            return back()
                ->withErrors([
                    'discount' => 'Discount tidak boleh lebih besar dari subtotal.',
                ])
                ->withInput();
        }

        $grandTotal = $subtotal - $discount + $tax;

        $invoice->update([
            'invoice_date' => $validated['invoice_date'],
            'due_date' => $validated['due_date'] ?? null,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'grand_total' => $grandTotal,
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil diperbarui.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->payments()->exists()) {
            return back()->with(
                'error',
                'Invoice yang sudah memiliki pembayaran tidak dapat dibatalkan.'
            );
        }

        $invoice->update([
            'status' => 'CANCELLED',
        ]);

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice berhasil dibatalkan.');
    }
}
