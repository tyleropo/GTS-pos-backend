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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'adjustment', 'return', 'damage', 'transfer']);
            $table->integer('quantity'); // positive for in, negative for out
            $table->integer('previous_stock');
            $table->integer('new_stock');
            $table->string('reference_type')->nullable(); // transaction, purchase_order, adjustment, etc.
            $table->uuid('reference_id')->nullable(); // ID of the related record
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // who made the change
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['product_id', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
