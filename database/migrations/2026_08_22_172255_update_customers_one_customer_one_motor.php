<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            if (!Schema::hasColumn('customers', 'plate_number')) {
                $table->string('plate_number', 20)->nullable()->after('phone');
            }

            if (!Schema::hasColumn('customers', 'brand')) {
                $table->string('brand', 100)->nullable()->after('plate_number');
            }

            if (!Schema::hasColumn('customers', 'type')) {
                $table->string('type', 100)->nullable()->after('brand');
            }
        });

        if (Schema::hasColumn('customers', 'address')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropColumn('address');
            });
        }
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {

            if (!Schema::hasColumn('customers', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }

            if (Schema::hasColumn('customers', 'plate_number')) {
                $table->dropColumn('plate_number');
            }

            if (Schema::hasColumn('customers', 'brand')) {
                $table->dropColumn('brand');
            }

            if (Schema::hasColumn('customers', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};