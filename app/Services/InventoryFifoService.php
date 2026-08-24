<?php

namespace App\Services;

use App\Models\InventoryBalance;
use App\Models\InventoryLayer;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryFifoService
{
    public function receivePurchaseItem(
        PurchaseItem $purchaseItem,
        int $quantity,
        ?string $receivedAt = null
    ): StockMovement {
        return DB::transaction(function () use (
            $purchaseItem,
            $quantity,
            $receivedAt
        ) {
            if ($quantity <= 0) {
                throw new RuntimeException(
                    'Quantity penerimaan harus lebih besar dari 0.'
                );
            }

            $purchaseItem->loadMissing('purchase');

            $alreadyReceived = (int) $purchaseItem->received_quantity;
            $orderedQuantity = (int) $purchaseItem->quantity;

            if (($alreadyReceived + $quantity) > $orderedQuantity) {
                throw new RuntimeException(
                    'Quantity penerimaan melebihi quantity purchase.'
                );
            }

            $unitCost = (float) $purchaseItem->unit_cost;

            $balance = InventoryBalance::where(
                'product_id',
                $purchaseItem->product_id
            )
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                $balance = InventoryBalance::create([
                    'product_id' => $purchaseItem->product_id,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'available_quantity' => 0,
                    'average_cost' => 0,
                ]);
            }

            $movement = StockMovement::create([
                'product_id' => $purchaseItem->product_id,
                'type' => 'PURCHASE',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'reference_type' => 'purchase_item',
                'reference_id' => $purchaseItem->id,
                'moved_at' => $receivedAt ?? now(),
                'notes' => 'Stock IN dari Purchase',
            ]);

            InventoryLayer::create([
                'product_id' => $purchaseItem->product_id,
                'purchase_item_id' => $purchaseItem->id,
                'stock_movement_id' => $movement->id,
                'quantity' => $quantity,
                'remaining_quantity' => $quantity,
                'unit_cost' => $unitCost,
                'total_cost' => $quantity * $unitCost,
                'received_at' => $receivedAt ?? now(),
                'status' => 'ACTIVE',
            ]);

            $oldQuantity = (int) $balance->quantity;
            $oldAverageCost = (float) $balance->average_cost;

            $oldTotalCost = $oldQuantity * $oldAverageCost;
            $newQuantity = $oldQuantity + $quantity;
            $newTotalCost = $oldTotalCost + ($quantity * $unitCost);

            $newAverageCost = $newQuantity > 0
                ? $newTotalCost / $newQuantity
                : 0;

            $reserved = (int) $balance->reserved_quantity;

            $balance->update([
                'quantity' => $newQuantity,
                'average_cost' => $newAverageCost,
                'available_quantity' => max(
                    0,
                    $newQuantity - $reserved
                ),
            ]);

            $purchaseItem->update([
                'received_quantity' =>
                    $alreadyReceived + $quantity,
            ]);

            return $movement;
        });
    }

    public function consumeForWorkOrder(
        WorkOrder $workOrder,
        int $productId,
        int $quantity,
        ?int $workOrderItemId = null
    ): float {
        return DB::transaction(function () use (
            $workOrder,
            $productId,
            $quantity,
            $workOrderItemId
        ) {
            if ($quantity <= 0) {
                return 0;
            }

            $balance = InventoryBalance::where(
                'product_id',
                $productId
            )
                ->lockForUpdate()
                ->first();

            if (!$balance) {
                throw new RuntimeException(
                    'Inventory balance tidak ditemukan.'
                );
            }

            $physicalStock = (int) $balance->quantity;

            $available = $physicalStock -
                (int) $balance->reserved_quantity;

            if ($quantity > $physicalStock) {
                throw new RuntimeException(
                    'Stok fisik tidak mencukupi untuk Work Order.'
                );
            }

            if ($quantity > $available) {
                throw new RuntimeException(
                    'Stok available tidak mencukupi untuk Work Order.'
                );
            }

            $movement = StockMovement::create([
                'product_id' => $productId,
                'type' => 'USAGE',
                'quantity' => $quantity,
                'unit_cost' => 0,
                'reference_type' => 'work_order',
                'reference_id' => $workOrder->id,
                'moved_at' => now(),
                'notes' => 'Pemakaian sparepart Work Order - FIFO',
            ]);

            $remainingToConsume = $quantity;
            $totalCost = 0;

            $layers = InventoryLayer::where(
                'product_id',
                $productId
            )
                ->where('status', 'ACTIVE')
                ->where('remaining_quantity', '>', 0)
                ->orderBy('received_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            foreach ($layers as $layer) {
                if ($remainingToConsume <= 0) {
                    break;
                }

                $layerRemaining =
                    (int) $layer->remaining_quantity;

                $consume = min(
                    $remainingToConsume,
                    $layerRemaining
                );

                $unitCost = (float) $layer->unit_cost;
                $lineCost = $consume * $unitCost;

                $totalCost += $lineCost;

                $newRemaining =
                    $layerRemaining - $consume;

                $layer->update([
                    'remaining_quantity' => $newRemaining,
                    'status' => $newRemaining > 0
                        ? 'ACTIVE'
                        : 'DEPLETED',
                ]);

                DB::table('inventory_layer_consumptions')->insert([
                    'inventory_layer_id' => $layer->id,
                    'stock_movement_id' => $movement->id,
                    'work_order_id' => $workOrder->id,
                    'work_order_item_id' => $workOrderItemId,
                    'quantity' => $consume,
                    'unit_cost' => $unitCost,
                    'total_cost' => $lineCost,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $remainingToConsume -= $consume;
            }

            if ($remainingToConsume > 0) {
                throw new RuntimeException(
                    'Inventory layer tidak memiliki stok yang cukup.'
                );
            }

            $oldQuantity = (int) $balance->quantity;
            $newQuantity = $oldQuantity - $quantity;

            $oldAverageCost = (float) $balance->average_cost;
            $oldTotalCost = $oldQuantity * $oldAverageCost;

            $newTotalCost = max(
                0,
                $oldTotalCost - $totalCost
            );

            $newAverageCost = $newQuantity > 0
                ? $newTotalCost / $newQuantity
                : 0;

            $reserved = (int) $balance->reserved_quantity;

            $balance->update([
                'quantity' => $newQuantity,
                'average_cost' => $newAverageCost,
                'available_quantity' => max(
                    0,
                    $newQuantity - $reserved
                ),
            ]);

            $movement->update([
                'unit_cost' => $quantity > 0
                    ? $totalCost / $quantity
                    : 0,
            ]);

            return $totalCost;
        });
    }
}

