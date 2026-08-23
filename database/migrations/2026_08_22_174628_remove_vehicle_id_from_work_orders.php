<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('work_orders', 'vehicle_id')) {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropForeign(['vehicle_id']);
                $table->dropColumn('vehicle_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles')
                ->restrictOnDelete();
        });
    }
};