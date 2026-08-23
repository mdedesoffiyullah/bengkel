<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {

            $table->foreignId('work_order_id')
                ->nullable()
                ->after('supplier_id')
                ->constrained('work_orders')
                ->nullOnDelete();

            $table->enum('purchase_type', [
                'GENERAL',
                'WO',
            ])
                ->default('GENERAL')
                ->after('work_order_id');

            $table->date('purchase_date')
                ->nullable()
                ->after('purchase_type');

            $table->dateTime('received_at')
                ->nullable()
                ->after('purchase_date');

            $table->decimal('grand_total', 15, 2)
                ->default(0)
                ->after('discount');

            $table->foreignId('created_by')
                ->nullable()
                ->after('notes')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'purchased_at',
                'tax',
                'total',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dateTime('purchased_at')
                ->nullable();

            $table->decimal('tax', 15, 2)
                ->default(0);

            $table->decimal('total', 15, 2)
                ->default(0);
        });

        Schema::table('purchases', function (Blueprint $table) {
            $table->dropForeign(['work_order_id']);
            $table->dropForeign(['created_by']);

            $table->dropColumn([
                'work_order_id',
                'purchase_type',
                'purchase_date',
                'received_at',
                'grand_total',
                'created_by',
            ]);
        });
    }
};
