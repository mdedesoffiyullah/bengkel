<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();

            $table->string('code', 30)->unique();

            $table->date('opname_date');

            $table->enum('status', [
                'DRAFT',
                'COMPLETED',
                'CANCELLED',
            ])->default('DRAFT');

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('opname_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_opnames');
    }
};