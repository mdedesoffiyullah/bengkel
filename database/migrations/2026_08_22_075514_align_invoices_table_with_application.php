<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('customer_id')
                ->nullable()
                ->after('work_order_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->date('due_date')
                ->nullable()
                ->after('invoice_date');

            $table->decimal('grand_total', 15, 2)
                ->default(0)
                ->after('tax');

            $table->decimal('paid_amount', 15, 2)
                ->default(0)
                ->after('grand_total');

            $table->text('notes')
                ->nullable()
                ->after('paid_amount');

            $table->foreignId('created_by')
                ->nullable()
                ->after('notes')
                ->constrained('users')
                ->nullOnDelete();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('total');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', [
                'UNPAID',
                'PARTIAL',
                'PAID',
                'CANCELLED',
            ])->default('UNPAID')->change();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('total', 15, 2)
                ->default(0)
                ->after('tax');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['customer_id']);

            $table->dropColumn([
                'customer_id',
                'due_date',
                'grand_total',
                'paid_amount',
                'notes',
                'created_by',
            ]);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('status', [
                'DRAFT',
                'ISSUED',
                'PAID',
                'CANCELLED',
            ])->default('DRAFT')->change();
        });
    }
};
