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
            $table->softDeletes();
            // Modify status enum to include 'deleted'
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'completed', 'cancelled', 'deleted'])->default('completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropSoftDeletes();
            // Revert status enum
            $table->dropColumn('status');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
        });
    }
};
