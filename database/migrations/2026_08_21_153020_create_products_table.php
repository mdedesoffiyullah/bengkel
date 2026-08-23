<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->string('code', 20)->unique();

            $table->foreignId('category_id')
                ->constrained('product_categories')
                ->restrictOnDelete();

            $table->string('barcode', 100)->nullable()->unique();

            $table->string('name');
            $table->string('brand', 100)->nullable();

            $table->string('unit', 20)->default('PCS');

            $table->enum('stock_type', [
                'STOCK',
                'NON_STOCK',
            ])->default('STOCK');

            $table->decimal('default_purchase_price', 15, 2)
                ->default(0);

            $table->decimal('default_selling_price', 15, 2)
                ->default(0);

            $table->decimal('minimum_stock', 15, 3)
                ->default(0);

            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('name');
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};