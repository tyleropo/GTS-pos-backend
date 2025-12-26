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
    $table->foreignUuid('category_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignUuid('supplier_id')->nullable()->constrained()->nullOnDelete();
    $table->string('brand')->nullable();
    $table->string('model')->nullable();
    $table->decimal('cost_price', 12, 2)->default(0);
    $table->decimal('selling_price', 12, 2)->default(0);
    $table->decimal('markup_percentage', 6, 2)->nullable();
    $table->decimal('tax_rate', 5, 2)->nullable();
    $table->integer('stock_quantity')->default(0);
    $table->integer('reorder_level')->default(0);
    $table->integer('max_stock_level')->nullable();
    $table->string('unit_of_measure')->nullable();
    $table->decimal('weight', 8, 2)->nullable();
    $table->string('dimensions')->nullable();
    $table->string('image_url')->nullable();
    $table->boolean('is_active')->default(true);
    $table->boolean('is_serialized')->default(false);
    $table->integer('warranty_period')->nullable();
    $table->timestamps();
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
