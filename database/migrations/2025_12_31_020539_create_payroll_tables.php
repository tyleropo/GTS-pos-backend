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
        // Payroll periods table
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "January 2025", "Week 1-2 Jan 2025"
            $table->enum('period_type', ['weekly', 'bi-weekly', 'monthly', 'custom']);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'finalized', 'paid'])->default('draft');
            $table->timestamps();

            // Indexes
            $table->index('status');
            $table->index('start_date');
        });

        // Payroll records for each employee per period
        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            $table->decimal('base_salary', 10, 2)->default(0);
            $table->decimal('commission', 10, 2)->default(0); // Manual entry by manager
            $table->decimal('gross_pay', 10, 2)->default(0);
            $table->decimal('total_deductions', 10, 2)->default(0);
            $table->decimal('net_pay', 10, 2)->default(0);
            $table->json('benefit_items')->nullable(); // [{name, amount}, ...]
            $table->json('deduction_items')->nullable(); // [{name, amount}, ...]
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('user_id');
            $table->index('payroll_period_id');
            $table->unique(['user_id', 'payroll_period_id']); // One record per employee per period
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('payroll_periods');
    }
};
