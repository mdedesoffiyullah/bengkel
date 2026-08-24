<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\WorkOrderItem;
use App\Services\InventoryFifoService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Support\Facades\DB;

class WorkOrderItemObserver implements ShouldHandleEventsAfterCommit
{
    public function created(WorkOrderItem $item): void
    {
        if ($item->item_type !== 'PRODUCT' || !$item->product_id) {
            return;
        }

        $workOrder = $item->workOrder;
        if (!$workOrder) {
            return;
        }

        $this->syncPurchaseFromWorkOrderItem($item, $workOrder);

        app(InventoryFifoService::class)->syncWorkOrderConsumption($workOrder);
    }

    private function syncPurchaseFromWorkOrderItem(WorkOrderItem $item, $workOrder): void
    {
        $purchaseQuantity = (int) ($item->purchase_quantity ?? 0);
        if ($purchaseQuantity <= 0) {
            return;
        }

        if (PurchaseItem::where('work_order_item_id', $item->id)->exists()) {
            return;
        }

        $supplierId = $item->supplier_id;
        if (!$supplierId) {
            $activeSuppliers = Supplier::where('is_active', true)->get(['id']);
            if ($activeSuppliers->count() === 1) {
                $supplierId = $activeSuppliers->first()->id;
            }
        }

        if (!$supplierId) {
            return;
        }

        $unitCost = (float) ($item->unit_cost ?? 0);
        $lineTotal = max(0, $purchaseQuantity * $unitCost);

        DB::transaction(function () use ($item, $workOrder, $supplierId, $purchaseQuantity, $unitCost, $lineTotal) {
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

            $purchaseItem = PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'work_order_item_id' => $item->id,
                'product_id' => $item->product_id,
                'quantity' => $purchaseQuantity,
                'unit_cost' => $unitCost,
                'selling_price' => (float) ($item->unit_price ?? 0),
                'discount_amount' => 0,
                'subtotal' => $lineTotal,
                'received_quantity' => 0,
                'notes' => 'Dibuat otomatis dari Work Order ' . $workOrder->code,
            ]);

            app(InventoryFifoService::class)->receivePurchaseItem(
                $purchaseItem,
                $purchaseQuantity,
                $purchase->received_at
            );

            $purchase->update([
                'status' => 'RECEIVED',
                'received_at' => $purchase->received_at ?? now(),
                'subtotal' => (float) $purchase->items()->sum('subtotal'),
                'grand_total' => (float) $purchase->items()->sum('subtotal'),
            ]);

            $payment = Payment::where('purchase_id', $purchase->id)
                ->where('transaction_type', 'PURCHASE_PAYMENT')
                ->first();

            $purchaseTotal = (float) $purchase->grand_total;

            if ($purchaseTotal > 0) {
                if ($payment) {
                    $payment->update([
                        'amount' => $purchaseTotal,
                        'paid_at' => $purchase->received_at,
                    ]);
                } else {
                    Payment::create([
                        'code' => 'PAY-PO-' . $purchase->code,
                        'transaction_type' => 'PURCHASE_PAYMENT',
                        'purchase_id' => $purchase->id,
                        'paid_at' => $purchase->received_at,
                        'amount' => $purchaseTotal,
                        'method' => 'OTHER',
                        'reference_number' => null,
                        'notes' => 'Pembayaran supplier otomatis dari Work Order ' . $workOrder->code,
                    ]);
                }
            }
        });
    }
}
