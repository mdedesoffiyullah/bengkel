<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')
                ->constrained('products')
                ->restrictOnDelete();

            $table->enum('type', [
                'PURCHASE',
                'SALE',
                'USAGE',
                'ADJUSTMENT',
                'RETURN',
                'STOCK_OPNAME',
            ]);

            $table->decimal('quantity', 15, 3);

            $table->decimal('unit_cost', 15, 2)->default(0);

            $table->nullableMorphs('reference');

            $table->dateTime('moved_at');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('moved_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};