<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {

            $table->foreignId('category_id')
                ->nullable()
                ->after('product_id')
                ->constrained('product_categories')
                ->restrictOnDelete();

            $table->foreignId('supplier_id')
                ->nullable()
                ->after('category_id')
                ->constrained('suppliers')
                ->restrictOnDelete();

            $table->string('barcode', 100)
                ->nullable()
                ->after('item_name');

            $table->string('brand', 100)
                ->nullable()
                ->after('barcode');

            $table->enum('stock_type', [
                'STOCK',
                'NON_STOCK',
            ])
                ->default('STOCK')
                ->after('unit');

            $table->decimal('minimum_stock', 15, 3)
                ->default(0)
                ->after('quantity');

            $table->decimal('selling_price', 15, 2)
                ->default(0)
                ->after('unit_cost');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {

            $table->dropForeign(['category_id']);
            $table->dropForeign(['supplier_id']);

            $table->dropColumn([
                'category_id',
                'supplier_id',
                'barcode',
                'brand',
                'stock_type',
                'minimum_stock',
                'selling_price',
            ]);
        });
    }
};
