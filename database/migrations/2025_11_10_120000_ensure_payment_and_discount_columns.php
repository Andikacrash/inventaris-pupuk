<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add columns only if they do not exist to avoid duplicate column errors
        if (!Schema::hasColumn('sales', 'discount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('discount', 10, 2)->default(0)->after('total_amount');
            });
        }

        if (!Schema::hasColumn('sales', 'payment_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('payment_amount', 10, 2)->default(0)->after('discount');
            });
        }

        if (!Schema::hasColumn('sales', 'change_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('change_amount', 10, 2)->default(0)->after('payment_amount');
            });
        }
    }

    public function down(): void
    {
        // Drop columns only if they exist
        if (Schema::hasColumn('sales', 'change_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('change_amount');
            });
        }

        if (Schema::hasColumn('sales', 'payment_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('payment_amount');
            });
        }

        if (Schema::hasColumn('sales', 'discount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('discount');
            });
        }
    }
};
