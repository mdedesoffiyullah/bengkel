<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_balances', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->decimal('quantity', 15, 3)->default(0);

            $table->decimal('reserved_quantity', 15, 3)->default(0);

            $table->decimal('available_quantity', 15, 3)->default(0);

            $table->decimal('average_cost', 15, 2)->default(0);

            $table->timestamps();

            $table->unique('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_balances');
    }
};