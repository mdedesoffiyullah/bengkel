<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('work_order_id')->nullable()->change();
        });

        DB::table('payments')
            ->where('transaction_type', 'PURCHASE_PAYMENT')
            ->whereNotNull('purchase_id')
            ->update(['work_order_id' => null]);
    }

    public function down(): void
    {
        // Intentionally non-destructive. Supplier payments require a nullable
        // work_order_id because they are linked through purchase_id only.
    }
};
