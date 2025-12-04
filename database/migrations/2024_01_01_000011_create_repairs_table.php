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
        Schema::create('repairs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('ticket_number')->unique();
            $table->unsignedBigInteger('customer_id');
            $table->string('device');
            $table->string('device_model')->nullable();
            $table->text('issue');
            $table->enum('status', ['Diagnostic', 'Waiting for Parts', 'In Progress', 'Completed', 'Cancelled'])->default('Diagnostic');
            $table->decimal('cost_estimate', 12, 2)->default(0);
            $table->decimal('final_cost', 12, 2)->default(0);
            $table->uuid('technician_id')->nullable();
            $table->date('repair_date');
            $table->date('completion_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('restrict');
            
            $table->foreign('technician_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            // Indexes
            $table->index('ticket_number');
            $table->index('customer_id');
            $table->index('technician_id');
            $table->index('status');
            $table->index('repair_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
