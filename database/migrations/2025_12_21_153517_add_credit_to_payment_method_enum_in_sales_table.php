<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the payment_method enum to include 'credit'
        // MySQL doesn't support modifying enum directly, so we use raw SQL
        DB::statement("ALTER TABLE `sales` MODIFY COLUMN `payment_method` ENUM('cash', 'transfer', 'card', 'credit') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove 'credit' from enum (only if no records use it)
        DB::statement("ALTER TABLE `sales` MODIFY COLUMN `payment_method` ENUM('cash', 'transfer', 'card') NOT NULL");
    }
};
