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
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            $table->enum('movement_type', ['adjustment', 'sale', 'purchase', 'return', 'transfer']);
            $table->integer('quantity')->comment('Positive for incoming, negative for outgoing');
            $table->string('reference_type')->nullable();
            $table->string('reference_id')->nullable();
            $table->uuid('user_id');
            $table->text('notes')->nullable();
            $table->dateTime('movement_date');
            $table->timestamps();

            // Foreign keys
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('restrict');
            
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');

            // Indexes
            $table->index('product_id');
            $table->index('movement_type');
            $table->index('movement_date');
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
