<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            if (! Schema::hasColumn('sales', 'delivery_method')) {
                $table->string('delivery_method', 20)->default('pickup')->after('customer_phone');
            }
            if (! Schema::hasColumn('sales', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('delivery_method');
            }
            if (! Schema::hasColumn('sales', 'delivery_phone')) {
                $table->string('delivery_phone', 30)->nullable()->after('delivery_address');
            }
            if (! Schema::hasColumn('sales', 'debt_amount')) {
                $table->decimal('debt_amount', 12, 2)->default(0)->after('change_amount');
            }
            if (! Schema::hasColumn('sales', 'debt_status')) {
                $table->string('debt_status', 20)->default('paid')->after('debt_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $cols = ['delivery_method', 'delivery_address', 'delivery_phone', 'debt_amount', 'debt_status'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('sales', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
