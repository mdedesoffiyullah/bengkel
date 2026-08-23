<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->foreignId('work_order_item_id')
                ->nullable()
                ->after('purchase_id')
                ->constrained('work_order_items')
                ->nullOnDelete();

            $table->index('work_order_item_id');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropForeign([
                'work_order_item_id',
            ]);

            $table->dropIndex([
                'purchase_items_work_order_item_id_index',
            ]);

            $table->dropColumn('work_order_item_id');
        });
    }
};
