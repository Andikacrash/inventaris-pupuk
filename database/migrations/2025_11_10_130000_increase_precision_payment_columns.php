<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Increase precision for payment_amount and change_amount to handle large values
        if (Schema::hasTable('sales')) {
            $columns = Schema::getColumnListing('sales');
            if (in_array('payment_amount', $columns)) {
                DB::statement("ALTER TABLE `sales` MODIFY `payment_amount` DECIMAL(15,2) NOT NULL DEFAULT '0'");
            }
            if (in_array('change_amount', $columns)) {
                DB::statement("ALTER TABLE `sales` MODIFY `change_amount` DECIMAL(15,2) NOT NULL DEFAULT '0'");
            }
            // ensure discount has reasonable precision
            if (in_array('discount', $columns)) {
                DB::statement("ALTER TABLE `sales` MODIFY `discount` DECIMAL(10,2) NOT NULL DEFAULT '0'");
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales')) {
            $columns = Schema::getColumnListing('sales');
            if (in_array('payment_amount', $columns)) {
                DB::statement("ALTER TABLE `sales` MODIFY `payment_amount` DECIMAL(10,2) NOT NULL DEFAULT '0'");
            }
            if (in_array('change_amount', $columns)) {
                DB::statement("ALTER TABLE `sales` MODIFY `change_amount` DECIMAL(10,2) NOT NULL DEFAULT '0'");
            }
            if (in_array('discount', $columns)) {
                DB::statement("ALTER TABLE `sales` MODIFY `discount` DECIMAL(10,2) NOT NULL DEFAULT '0'");
            }
        }
    }
};
