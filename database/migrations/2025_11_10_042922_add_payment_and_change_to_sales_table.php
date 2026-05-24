<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // add columns only if they don't exist to avoid duplicate column errors
        if (!Schema::hasColumn('sales', 'payment_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('payment_amount', 10, 2)->default(0);
            });
        }

        if (!Schema::hasColumn('sales', 'change_amount')) {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('change_amount', 10, 2)->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['payment_amount', 'change_amount']);
        });
    }
};
