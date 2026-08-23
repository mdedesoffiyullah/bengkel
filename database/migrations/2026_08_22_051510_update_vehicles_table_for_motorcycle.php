<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('type', 100)->after('brand');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'model',
                'year',
                'color',
                'engine_number',
                'chassis_number',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('model', 100)->after('brand');
            $table->string('year', 4)->nullable()->after('model');
            $table->string('color', 50)->nullable()->after('year');
            $table->string('engine_number', 100)->nullable()->after('color');
            $table->string('chassis_number', 100)->nullable()->after('engine_number');
        });

        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
