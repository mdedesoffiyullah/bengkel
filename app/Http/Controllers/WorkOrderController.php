<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Service;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class WorkOrderController extends Controller
{
    public function index()
    {
        $workOrders = WorkOrder::with('customer')->latest()->paginate(15);
        return view('work_orders.index', compact('workOrders'));
    }

    public function create()
    {
        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $nextCode = $this->generateCode('WO-', 'work_orders', 'code');

        return view('work_orders.create', compact(
            'customers', 'products', 'services', 'suppliers', 'nextCode'
        ));
    }

    public function store(Request $request)
    {
        $validated = $this->validateWorkOrder($request);

        return DB::transaction(function () use ($request, $validated) {
            $customer = $this->resolveCustomer($validated);

            $workOrder = WorkOrder::create([
                'code' => $validated['code'],
                'status' => $validated['status'] ?? 'OPEN',
                'customer_id' => $customer?->id,
                'type' => $validated['type'] ?? 'REGULAR',
                'opened_at' => $validated['opened_at'] ?? now(),
                'complaint' => $validated['complaint'] ?? null,
                'diagnosis' => $validated['diagnosis'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'discount' => $validated['discount'] ?? 0,
                'subtotal' => 0,
                'grand_total' => 0,
            ]);

            $subtotal = $this->saveItems($workOrder, $request->input('items', []));
            $discount = (float) ($validated['discount'] ?? 0);
            $grandTotal = max(0, $subtotal - $discount);

            $workOrder->update([
                'subtotal' => $subtotal,
                'grand_total' => $grandTotal,
            ]);

            $paymentAmount = (float) ($validated['payment_amount'] ?? 0);
            if ($paymentAmount > 0) {
                if ($paymentAmount > $grandTotal) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'payment_amount' => 'Jumlah pembayaran tidak boleh melebihi Grand Total Work Order.',
                    ]);
                }

                $paymentMethod = $validated['payment_method'] ?? null;
                if (!$paymentMethod) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'payment_method' => 'Metode pembayaran wajib dipilih jika ada pembayaran.',
                    ]);
                }

                \App\Models\Payment::create([
                    'code' => 'PAY-' . now()->format('YmdHisv'),
                    'transaction_type' => 'CUSTOMER_PAYMENT',
                    'work_order_id' => $workOrder->id,
                    'paid_at' => $validated['payment_date'] ?? now(),
                    'amount' => $paymentAmount,
                    'method' => $paymentMethod,
                    'reference_number' => $validated['payment_reference'] ?? null,
                    'notes' => $validated['payment_notes'] ?? null,
                ]);
            }

            return redirect()->route('work-orders.show', $workOrder)
                ->with('success', 'Work Order berhasil dibuat.');
        });
    }

    // The remainder of the controller is intentionally unchanged.
