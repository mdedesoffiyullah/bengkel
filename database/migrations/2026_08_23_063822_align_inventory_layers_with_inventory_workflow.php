<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_layers', function (Blueprint $table) {

            $table->foreignId('purchase_item_id')
                ->nullable()
                ->after('product_id')
                ->constrained('purchase_items')
                ->nullOnDelete();

            $table->foreignId('stock_movement_id')
                ->nullable()
                ->after('purchase_item_id')
                ->constrained('stock_movements')
                ->nullOnDelete();

            $table->decimal('total_cost', 15, 2)
                ->default(0)
                ->after('unit_cost');

            $table->enum('status', [
                'ACTIVE',
                'DEPLETED',
                'CANCELLED',
            ])
                ->default('ACTIVE')
                ->after('received_at');

            $table->index('purchase_item_id');
            $table->index('stock_movement_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_layers', function (Blueprint $table) {

            $table->dropForeign([
                'purchase_item_id',
            ]);

            $table->dropForeign([
                'stock_movement_id',
            ]);

            $table->dropIndex([
                'inventory_layers_purchase_item_id_index',
            ]);

            $table->dropIndex([
                'inventory_layers_stock_movement_id_index',
            ]);

            $table->dropIndex([
                'inventory_layers_status_index',
            ]);

            $table->dropColumn([
                'purchase_item_id',
                'stock_movement_id',
                'total_cost',
                'status',
            ]);
        });
    }
};
