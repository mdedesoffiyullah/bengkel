<?php

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Services\InventoryFifoService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $purchaseItemsByWoItem = PurchaseItem::query()
                ->pluck('id', 'work_order_item_id');

            DB::table('work_order_items')
                ->where('item_type', 'PRODUCT')
                ->whereNotNull('product_id')
                ->where('purchase_quantity', '>', 0)
                ->orderBy('id')
                ->get()
                ->each(function ($woItem) use ($purchaseItemsByWoItem) {
                    if ($purchaseItemsByWoItem->has($woItem->id)) {
                        return;
                    }

                    $supplierId = $woItem->supplier_id;
                    if (!$supplierId) {
                        $activeSuppliers = Supplier::where('is_active', true)->get(['id']);
                        if ($activeSuppliers->count() === 1) {
                            $supplierId = $activeSuppliers->first()->id;
                        }
                    }

                    if (!$supplierId) {
                        return;
                    }

                    $workOrder = DB::table('work_orders')->where('id', $woItem->work_order_id)->first();
                    if (!$workOrder) {
                        return;
                    }

                    $purchase = Purchase::where('work_order_id', $workOrder->id)
                        ->where('purchase_type', 'WO')
                        ->where('supplier_id', $supplierId)
                        ->where('status', '!=', 'CANCELLED')
                        ->latest('id')
                        ->first();

                    if (!$purchase) {
                        $purchase = Purchase::create([
                            'code' => 'PO-WO-' . $workOrder->code . '-' . $supplierId,
                            'supplier_id' => $supplierId,
                            'work_order_id' => $workOrder->id,
                            'status' => 'RECEIVED',
                            'purchase_type' => 'WO',
                            'purchase_date' => $workOrder->opened_at ?? now(),
                            'received_at' => now(),
                            'subtotal' => 0,
                            'discount' => 0,
                            'grand_total' => 0,
                            'notes' => 'Purchase otomatis dari Work Order ' . $workOrder->code,
                        ]);
                    }

                    $quantity = (int) $woItem->purchase_quantity;
                    $unitCost = (float) $woItem->unit_cost;
                    $subtotal = $quantity * $unitCost;

                    $purchaseItem = PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'work_order_item_id' => $woItem->id,
                        'product_id' => $woItem->product_id,
                        'quantity' => $quantity,
                        'unit_cost' => $unitCost,
                        'selling_price' => (float) $woItem->unit_price,
                        'discount_amount' => 0,
                        'subtotal' => $subtotal,
                        'received_quantity' => 0,
                        'notes' => 'Repaired from existing Work Order ' . $workOrder->code,
                    ]);

                    app(InventoryFifoService::class)->receivePurchaseItem(
                        $purchaseItem,
                        $quantity,
                        $purchase->received_at
                    );

                    $purchase->update([
                        'status' => 'RECEIVED',
                        'subtotal' => (float) $purchase->items()->sum('subtotal'),
                        'grand_total' => (float) $purchase->items()->sum('subtotal'),
                    ]);

                    $payment = Payment::where('purchase_id', $purchase->id)
                        ->where('transaction_type', 'PURCHASE_PAYMENT')
                        ->first();

                    $total = (float) $purchase->grand_total;
                    if ($total > 0) {
                        if ($payment) {
                            $payment->update(['amount' => $total, 'paid_at' => $purchase->received_at]);
                        } else {
                            Payment::create([
                                'code' => 'PAY-PO-' . $purchase->code,
                                'transaction_type' => 'PURCHASE_PAYMENT',
                                'purchase_id' => $purchase->id,
                                'paid_at' => $purchase->received_at,
                                'amount' => $total,
                                'method' => 'OTHER',
                                'notes' => 'Pembayaran supplier otomatis dari Work Order ' . $workOrder->code,
                            ]);
                        }
                    }
                });
        });
    }

    public function down(): void
    {
        // Data repair migrations are intentionally not destructive.
    }
};
