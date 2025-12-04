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
        Schema::create('repair_parts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('repair_id');
            $table->uuid('product_id')->nullable();
            $table->string('part_name');
            $table->string('part_number')->nullable();
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('line_total', 12, 2);
            $table->timestamps();

            // Foreign keys
            $table->foreign('repair_id')
                  ->references('id')
                  ->on('repairs')
                  ->onDelete('cascade');
            
            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('set null');

            // Indexes
            $table->index('repair_id');
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_parts');
    }
};
