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
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('adjustment_number')->unique();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['physical_count', 'damage', 'loss', 'found', 'correction']);
            $table->integer('old_quantity');
            $table->integer('new_quantity');
            $table->integer('difference'); // can be positive or negative
            $table->text('reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('adjusted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['product_id', 'created_at']);
            $table->index('adjustment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
