<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'transaction_type')) {
                $table->string('transaction_type', 30)->default('CUSTOMER_PAYMENT')->after('code');
            }

            if (!Schema::hasColumn('payments', 'purchase_id')) {
                $table->foreignId('purchase_id')->nullable()->after('work_order_id')->constrained('purchases')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'purchase_id')) {
                $table->dropForeign(['purchase_id']);
                $table->dropColumn('purchase_id');
            }

            if (Schema::hasColumn('payments', 'transaction_type')) {
                $table->dropColumn('transaction_type');
            }
        });
    }
};
