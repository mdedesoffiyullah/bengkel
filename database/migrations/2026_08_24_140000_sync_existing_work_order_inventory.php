<?php

use App\Models\WorkOrder;
use App\Services\InventoryFifoService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
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
                    app(InventoryFifoService::class)->syncWorkOrderConsumption($workOrder);
                });
        });
    }

    public function down(): void
    {
        // Intentionally non-destructive: this migration repairs derived inventory state.
    }
};
