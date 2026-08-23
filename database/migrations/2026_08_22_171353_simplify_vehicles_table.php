<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            $columns = [];

            if (Schema::hasColumn('vehicles', 'year')) {
                $columns[] = 'year';
            }

            if (Schema::hasColumn('vehicles', 'color')) {
                $columns[] = 'color';
            }

            if (Schema::hasColumn('vehicles', 'engine_number')) {
                $columns[] = 'engine_number';
            }

            if (Schema::hasColumn('vehicles', 'chassis_number')) {
                $columns[] = 'chassis_number';
            }

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            $table->string('year', 4)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('engine_number', 100)->nullable();
            $table->string('chassis_number', 100)->nullable();

        });
    }
};
