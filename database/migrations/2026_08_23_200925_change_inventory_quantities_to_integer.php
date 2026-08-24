<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('reserved_quantity')->change();
            $table->integer('available_quantity')->change();
        });

        Schema::table('inventory_layers', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('remaining_quantity')->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
            $table->integer('received_quantity')->change();
        });

        Schema::table('stock_allocations', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });

        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->integer('system_quantity')->change();
            $table->integer('physical_quantity')->change();
            $table->integer('difference_quantity')->change();
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->integer('quantity')->change();
        });
    }

    public function down(): void
    {
        Schema::table('inventory_balances', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->change();
            $table->decimal('reserved_quantity', 15, 3)->change();
            $table->decimal('available_quantity', 15, 3)->change();
        });

        Schema::table('inventory_layers', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->change();
            $table->decimal('remaining_quantity', 15, 3)->change();
        });

        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->change();
            $table->decimal('received_quantity', 15, 3)->change();
        });

        Schema::table('stock_allocations', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->change();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->change();
        });

        Schema::table('stock_opname_items', function (Blueprint $table) {
            $table->decimal('system_quantity', 15, 3)->change();
            $table->decimal('physical_quantity', 15, 3)->change();
            $table->decimal('difference_quantity', 15, 3)->change();
        });

        Schema::table('work_order_items', function (Blueprint $table) {
            $table->decimal('quantity', 15, 3)->change();
        });
    }
};

