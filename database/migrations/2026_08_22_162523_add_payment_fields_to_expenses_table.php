<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {

            $table->string('payment_method', 30)
                ->default('CASH')
                ->after('amount');

            $table->string('reference_number', 100)
                ->nullable()
                ->after('payment_method');

            $table->string('status', 20)
                ->default('POSTED')
                ->after('reference_number');

            $table->foreignId('created_by')
                ->nullable()
                ->after('status')
                ->constrained('users')
                ->nullOnDelete();

            $table->index('status');
            $table->index('payment_method');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {

            $table->dropForeign(['created_by']);
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_method']);

            $table->dropColumn([
                'payment_method',
                'reference_number',
                'status',
                'created_by',
            ]);
        });
    }
};
