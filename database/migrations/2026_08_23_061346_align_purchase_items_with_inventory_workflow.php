<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {

            $table->renameColumn(
                'unit_price',
                'unit_cost'
            );

            $table->decimal('selling_price', 15, 2)
                ->default(0)
                ->after('unit_cost');

            $table->decimal('discount_amount', 15, 2)
                ->default(0)
                ->after('selling_price');

            $table->decimal('received_quantity', 15, 3)
                ->default(0)
                ->after('subtotal');

            $table->text('notes')
                ->nullable()
                ->after('received_quantity');
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn('discount');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->renameColumn(
                'unit_cost',
                'unit_price'
            );

            $table->dropColumn([
                'selling_price',
                'discount_amount',
                'received_quantity',
                'notes',
            ]);
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('discount', 15, 2)
                ->default(0)
                ->after('unit_price');
        });
    }
};
