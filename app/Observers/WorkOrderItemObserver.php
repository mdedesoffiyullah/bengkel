<?php

namespace App\Observers;

use App\Models\WorkOrderItem;
use App\Services\InventoryFifoService;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;

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

        // The controller may consume ordinary stock during the transaction.
        // After commit we rebuild FIFO consumption from the final WO item set,
        // which also fixes purchase-linked items and WO edits/rebuilds.
        app(InventoryFifoService::class)->syncWorkOrderConsumption($workOrder);
    }
}
