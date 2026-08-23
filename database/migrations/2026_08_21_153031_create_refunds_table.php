<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();

            $table->foreignId('invoice_id')
                ->nullable()
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->date('refund_date');

            $table->decimal('amount', 15, 2)->default(0);

            $table->enum('status', [
                'DRAFT',
                'COMPLETED',
                'CANCELLED',
            ])->default('DRAFT');

            $table->text('reason')->nullable();

            $table->timestamps();

            $table->index('refund_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};