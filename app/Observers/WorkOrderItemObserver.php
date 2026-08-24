<?php

namespace App\Observers;

use App\Models\WorkOrderItem;
use App\Services\InventoryFifoService;
use RuntimeException;

class WorkOrderItemObserver
{
    public function created(WorkOrderItem $item): void
    {
        if ($item->item_type !== 'PRODUCT' || !$item->product_id) {
            return;
        }

        // WorkOrderController already consumes normal stock when there is no
        // linked purchase. Only purchase-linked WO items need this observer.
        if ((int) $item->purchase_quantity <= 0) {
            return;
        }

        $quantity = (int) ($item->wo_quantity ?: $item->quantity);
        if ($quantity <= 0) {
            return;
        }

        app(InventoryFifoService::class)->consumeForWorkOrder(
            $item->workOrder,
            (int) $item->product_id,
            $quantity,
            $item->id
        );

        $item->updateQuietly(['status' => 'USED']);
    }
}
