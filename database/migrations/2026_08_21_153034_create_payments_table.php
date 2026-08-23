<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->dateTime('paid_at');

            $table->decimal('amount', 15, 2);

            $table->enum('method', [
                'CASH',
                'TRANSFER',
                'DEBIT',
                'CREDIT_CARD',
                'QRIS',
            ]);

            $table->string('reference_number', 100)->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('paid_at');
            $table->index('method');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};