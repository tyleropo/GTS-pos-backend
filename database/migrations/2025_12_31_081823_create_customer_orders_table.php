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
        // Create customer_orders table
        Schema::create('customer_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('co_number')->unique(); // Customer Order Number
            $table->foreignUuid('customer_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['draft', 'submitted', 'fulfilled', 'cancelled'])->default('draft');
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');
            $table->date('expected_at')->nullable(); // Expected delivery date
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('items')->nullable(); // Backup storage of items
            $table->json('meta')->nullable(); // For tax rates, discount info, etc
            $table->timestamps();

            // Indexes for better query performance
            $table->index('customer_id');
            $table->index('status');
            $table->index('expected_at');
        });

        // Create pivot table for customer order products
        Schema::create('customer_order_product', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('customer_order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_ordered')->default(1);
            $table->integer('quantity_fulfilled')->default(0); // Track how much has been shipped
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Composite index for faster queries
            $table->index(['customer_order_id', 'product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_order_product');
        Schema::dropIfExists('customer_orders');
    }
};
