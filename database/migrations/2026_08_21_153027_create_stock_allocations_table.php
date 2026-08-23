<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_allocations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->foreignId('work_order_id')
                ->nullable()
                ->constrained('work_orders')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 3);

            $table->enum('status', [
                'RESERVED',
                'RELEASED',
                'CONSUMED',
            ])->default('RESERVED');

            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_allocations');
    }
};