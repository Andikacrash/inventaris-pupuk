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
        Schema::create('installment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('debt_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 15, 2);
            $table->integer('installment_count');
            $table->decimal('installment_amount', 15, 2);
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->integer('paid_count')->default(0);
            $table->enum('status', ['active', 'completed', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_plans');
    }
};
