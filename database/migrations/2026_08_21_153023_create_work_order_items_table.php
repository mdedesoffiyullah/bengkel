<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('work_order_id')
                ->constrained('work_orders')
                ->cascadeOnDelete();

            $table->enum('item_type', [
                'SERVICE',
                'PRODUCT',
            ]);

            $table->foreignId('service_id')
                ->nullable()
                ->constrained('services')
                ->restrictOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 3)->default(1);

            $table->decimal('unit_price', 15, 2)->default(0);

            $table->decimal('discount', 15, 2)->default(0);

            $table->decimal('subtotal', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('item_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_items');
    }
};