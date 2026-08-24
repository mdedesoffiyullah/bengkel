<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')
            ->where('transaction_type', 'PURCHASE_PAYMENT')
            ->whereNotNull('purchase_id')
            ->update(['work_order_id' => null]);
    }

    public function down(): void
    {
        // Intentionally non-destructive: the correct supplier-payment relationship
        // is purchase_id only, not work_order_id.
    }
};
