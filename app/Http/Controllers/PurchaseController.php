<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Services\InventoryFifoService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = Purchase::with(['supplier', 'workOrder'])->latest()->paginate(10);
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $workOrders = WorkOrder::whereNotIn('status', ['COMPLETED', 'CANCELLED'])->latest()->get();
        return view('purchases.create', compact('suppliers', 'workOrders'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePurchase($request);

        if ($validated['purchase_type'] === 'WO' && empty($validated['work_order_id'])) {
            return back()->withErrors(['work_order_id' => 'Purchase tipe WO wajib memiliki Work Order.'])->withInput();
        }

        if ($validated['purchase_type'] === 'GENERAL') {
            $validated['work_order_id'] = null;
        }

        Purchase::create($validated + [
            'status' => $validated['status'] ?? 'DRAFT',
            'subtotal' => $validated['subtotal'] ?? 0,
            'discount' => $validated['discount'] ?? 0,
            'grand_total' => $validated['grand_total'] ?? 0,
        ]);

        return redirect()->route('purchases.index')->with('success', 'Purchase berhasil dibuat.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'workOrder', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        if ($purchase->status === 'RECEIVED') {
            return redirect()->route('purchases.show', $purchase)->with('error', 'Purchase yang sudah diterima tidak dapat diedit.');
        }

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $workOrders = WorkOrder::whereNotIn('status', ['COMPLETED', 'CANCELLED'])->latest()->get();
        return view('purchases.edit', compact('purchase', 'suppliers', 'workOrders'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        if ($purchase->status === 'RECEIVED') {
            return redirect()->route('purchases.show', $purchase)->with('error', 'Purchase yang sudah diterima tidak dapat diedit.');
        }

        $validated = $this->validatePurchase($request, $purchase);

        if ($validated['purchase_type'] === 'WO' && empty($validated['work_order_id'])) {
            return back()->withErrors(['work_order_id' => 'Purchase tipe WO wajib memiliki Work Order.'])->withInput();
        }

        if ($validated['purchase_type'] === 'GENERAL') {
            $validated['work_order_id'] = null;
        }

        if ($validated['status'] === 'RECEIVED') {
            DB::transaction(function () use ($purchase, $validated) {
                $purchase->load('items');
                $fifo = app(InventoryFifoService::class);

                foreach ($purchase->items as $item) {
                    $targetReceived = (int) $item->received_quantity;
                    $previousReceived = (int) $item->getOriginal('received_quantity');
                    $delta = $targetReceived - $previousReceived;

                    if ($delta > 0) {
                        $fifo->receivePurchaseItem(
                            $item,
                            $delta,
                            $validated['received_at'] ?? now()
                        );
                    }
                }

                $purchase->update([
                    'status' => 'RECEIVED',
                    'received_at' => $validated['received_at'] ?? now(),
                    'code' => $validated['code'],
                    'supplier_id' => $validated['supplier_id'],
                    'work_order_id' => $validated['work_order_id'],
                    'purchase_type' => $validated['purchase_type'],
                    'purchase_date' => $validated['purchase_date'],
                    'subtotal' => $validated['subtotal'] ?? 0,
                    'discount' => $validated['discount'] ?? 0,
                    'grand_total' => $validated['grand_total'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                ]);
            });
        } else {
            $purchase->update($validated);
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Purchase berhasil diperbarui.');
    }

    public function destroy(Purchase $purchase)
    {
        if ($purchase->status === 'RECEIVED') {
            return redirect()->route('purchases.show', $purchase)->with('error', 'Purchase yang sudah diterima tidak dapat dihapus.');
        }

        $purchase->delete();
        return redirect()->route('purchases.index')->with('success', 'Purchase berhasil dihapus.');
    }

    private function validatePurchase(Request $request, ?Purchase $purchase = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('purchases', 'code')->ignore($purchase?->id)],
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'work_order_id' => ['nullable', 'integer', 'exists:work_orders,id'],
            'status' => ['nullable', Rule::in(['DRAFT', 'ORDERED', 'PARTIAL', 'RECEIVED', 'CANCELLED'])],
            'purchase_type' => ['required', Rule::in(['GENERAL', 'WO'])],
            'purchase_date' => 'required|date',
            'received_at' => 'nullable|date',
            'subtotal' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);
    }
}
