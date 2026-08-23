<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->string('item_code', 50)
                ->nullable()
                ->after('product_id');

            $table->string('item_name')
                ->nullable()
                ->after('item_code');

            $table->string('unit', 20)
                ->default('PCS')
                ->after('item_name');

            $table->decimal('discount_amount', 15, 2)
                ->default(0)
                ->after('unit_price');

            $table->decimal('unit_cost', 15, 2)
                ->default(0)
                ->after('discount_amount');

            $table->decimal('total_cost', 15, 2)
                ->default(0)
                ->after('subtotal');

            $table->string('status', 30)
                ->default('PENDING')
                ->after('total_cost');
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)
                ->default(0)
                ->after('unit_price');

            $table->dropColumn([
                'item_code',
                'item_name',
                'unit',
                'discount_amount',
                'unit_cost',
                'total_cost',
                'status',
            ]);
        });
    }
};
