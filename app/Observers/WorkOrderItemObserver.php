<?php

namespace App\Observers;

use App\Models\WorkOrderItem;
use App\Services\InventoryFifoService;

class WorkOrderItemObserver
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

        // Every successful WO item save represents a physical issue from stock.
        // Synchronizing here also restores/rebuilds previous WO consumption
        // when the controller rebuilds the item list during an edit.
        app(InventoryFifoService::class)->syncWorkOrderConsumption($workOrder);
    }
}
