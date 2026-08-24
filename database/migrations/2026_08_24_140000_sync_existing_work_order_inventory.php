<?php

use App\Models\WorkOrder;
use App\Services\InventoryFifoService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use RuntimeException;

return new class extends Migration
{
    public function up(): void
    {
        WorkOrder::query()
            ->whereHas('items', function ($query) {
                $query->where('item_type', 'PRODUCT')
                    ->whereNotNull('product_id')
                    ->where(function ($q) {
                        $q->where('wo_quantity', '>', 0)
                            ->orWhere('quantity', '>', 0);
                    });
            })
            ->orderBy('id')
            ->get()
            ->each(function (WorkOrder $workOrder) {
                try {
                    app(InventoryFifoService::class)->syncWorkOrderConsumption($workOrder);
                } catch (RuntimeException $e) {
                    // Historical data can contain WO quantities that exceed the
                    // stock that actually existed at that point in time. Do not
                    // abort the whole repair migration; leave that WO untouched
                    // so current inventory remains consistent and the normal WO
                    // workflow can report the shortage when it is processed.
                    if (!str_contains($e->getMessage(), 'Stok fisik tidak mencukupi')
                        && !str_contains($e->getMessage(), 'Stok available tidak mencukupi')
                        && !str_contains($e->getMessage(), 'Inventory layer tidak memiliki stok yang cukup')) {
                        throw $e;
                    }
                }
            });
    }

    public function down(): void
    {
        // Intentionally non-destructive: this migration repairs derived inventory state.
    }
};