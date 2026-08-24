<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 15, 2)
                ->default(0)
                ->after('difference_quantity');

            $table->decimal('difference_value', 15, 2)
                ->default(0)
                ->after('unit_cost');
        });

        // Sinkronkan status dengan controller.
        DB::statement("
            ALTER TABLE stock_opnames
            MODIFY status ENUM(
                'DRAFT',
                'IN_PROGRESS',
                'POSTED',
                'CANCELLED'
            ) NOT NULL DEFAULT 'DRAFT'
        ");
    }

    public function down(): void
    {
        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->dropColumn([
                'unit_cost',
                'difference_value',
            ]);
        });

        DB::statement("
            ALTER TABLE stock_opnames
            MODIFY status ENUM(
                'DRAFT',
                'COMPLETED',
                'CANCELLED'
            ) NOT NULL DEFAULT 'DRAFT'
        ");
    }
};
