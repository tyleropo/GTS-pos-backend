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
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('sku')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->uuid('supplier_id')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->decimal('cost_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->decimal('markup_percentage', 8, 2)->nullable();
            $table->decimal('tax_rate', 8, 2)->nullable()->default(0);
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->integer('max_stock_level')->nullable();
            $table->string('unit_of_measure')->default('pcs');
            $table->decimal('weight', 10, 2)->nullable();
            $table->string('dimensions')->nullable();
            $table->string('image_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_serialized')->default(false);
            $table->integer('warranty_period')->nullable()->comment('Warranty period in months');
            $table->timestamps();

            // Foreign keys
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('set null');
            
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->onDelete('set null');

            // Indexes
            $table->index('sku');
            $table->index('barcode');
            $table->index('name');
            $table->index('category_id');
            $table->index('supplier_id');
            $table->index('is_active');
            $table->index('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
