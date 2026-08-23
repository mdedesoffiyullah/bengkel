<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();

            $table->foreignId('customer_id')
                ->constrained('customers')
                ->restrictOnDelete();

            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles')
                ->restrictOnDelete();

            $table->foreignId('work_order_id')
                ->nullable()
                ->constrained('work_orders')
                ->restrictOnDelete();

            $table->dateTime('reported_at');

            $table->text('complaint');

            $table->enum('status', [
                'OPEN',
                'IN_PROGRESS',
                'RESOLVED',
                'CLOSED',
            ])->default('OPEN');

            $table->text('resolution')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('reported_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};