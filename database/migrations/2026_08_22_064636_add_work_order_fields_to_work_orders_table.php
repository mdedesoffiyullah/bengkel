<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->enum('type', [
                'REGULAR',
                'WARRANTY',
            ])
            ->default('REGULAR')
            ->after('status');

            $table->dateTime('started_at')
                ->nullable()
                ->after('type');

            $table->dateTime('cancelled_at')
                ->nullable()
                ->after('completed_at');

            $table->decimal('subtotal', 15, 2)
                ->default(0)
                ->after('notes');

            $table->decimal('discount', 15, 2)
                ->default(0)
                ->after('subtotal');

            $table->decimal('grand_total', 15, 2)
                ->default(0)
                ->after('discount');

            $table->text('cancellation_reason')
                ->nullable()
                ->after('grand_total');
        });
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'started_at',
                'cancelled_at',
                'subtotal',
                'discount',
                'grand_total',
                'cancellation_reason',
            ]);
        });
    }
};
