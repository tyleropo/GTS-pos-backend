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
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('transaction_number')->unique();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->uuid('cashier_id');
            $table->date('transaction_date');
            $table->time('transaction_time');
            $table->string('payment_method');
            $table->enum('status', ['Completed', 'Refunded', 'Pending', 'Cancelled'])->default('Completed');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('set null');
            
            $table->foreign('cashier_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            // Indexes
            $table->index('transaction_number');
            $table->index('customer_id');
            $table->index('cashier_id');
            $table->index('transaction_date');
            $table->index('status');
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
