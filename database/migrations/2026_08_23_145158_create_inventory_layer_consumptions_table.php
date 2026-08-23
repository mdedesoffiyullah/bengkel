<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_layer_consumptions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('inventory_layer_id')
                ->constrained('inventory_layers')
                ->restrictOnDelete();

            $table->foreignId('stock_movement_id')
                ->constrained('stock_movements')
                ->restrictOnDelete();

            $table->foreignId('work_order_id')
                ->nullable()
                ->constrained('work_orders')
                ->nullOnDelete();

            $table->foreignId('work_order_item_id')
                ->nullable()
                ->constrained('work_order_items')
                ->nullOnDelete();

            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_cost', 15, 2);
            $table->decimal('total_cost', 15, 2);

            $table->timestamps();

            $table->index(
                ['inventory_layer_id', 'created_at'],
                'ilc_layer_created_idx'
            );

            $table->index(
                ['work_order_id', 'work_order_item_id'],
                'ilc_wo_item_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_layer_consumptions');
    }
};
