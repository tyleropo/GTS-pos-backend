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
        Schema::table('payroll_records', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_records', 'employee_id')) {
                $table->unsignedBigInteger('employee_id')->nullable()->after('user_id');
                $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            }
            // Make user_id nullable for future transition, though DB modifications might be tricky on existing data without nulling first.
            // For now, let's just add employee_id. We'll handle data migration/sync in logic or separate step if needed.
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_records', function (Blueprint $table) {
            //
        });
    }
};
