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
            $table->id();
            $table->string('repair_number', 50)->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('technician_id')->nullable();
            $table->string('device_type');
            $table->string('device_brand')->nullable();
            $table->string('device_model')->nullable();
            $table->string('serial_number')->nullable();
            $table->text('issue_description');
            $table->text('diagnosis')->nullable();
            $table->text('repair_notes')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->decimal('actual_cost', 10, 2)->nullable();
            $table->decimal('labor_cost', 10, 2)->nullable();
            $table->decimal('parts_cost', 10, 2)->nullable();
            $table->enum('status', [
                'received', 
                'diagnosed', 
                'waiting_approval', 
                'in_progress', 
                'waiting_parts', 
                'completed', 
                'ready_pickup', 
                'delivered', 
                'cancelled'
            ])->default('received');
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->timestamp('received_date');
            $table->timestamp('estimated_completion_date')->nullable();
            $table->timestamp('actual_completion_date')->nullable();
            $table->integer('warranty_period')->default(30)->comment('Warranty period in days');
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->foreign('technician_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('repair_number');
            $table->index(['customer_id', 'status']);
            $table->index(['technician_id', 'status']);
            $table->index(['status', 'priority']);
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
