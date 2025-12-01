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
        Schema::table('products', function (Blueprint $table) {
            // Drop the old category column (string) - we'll use category_id instead
            $table->dropColumn('category');
            
            // Add SKU after id (rename stock_keeping_unit)
            $table->renameColumn('stock_keeping_unit', 'sku');
            $table->string('sku', 100)->unique()->change();
            
            // Update barcode
            $table->string('barcode', 100)->unique()->nullable()->change();
            
            // Add category_id foreign key
            $table->unsignedBigInteger('category_id')->nullable()->after('description');
            
            // Add model field
            $table->string('model')->nullable()->after('brand');
            
            // Rename and update pricing fields
            $table->renameColumn('price', 'selling_price');
            $table->decimal('cost_price', 10, 2)->after('model');
            $table->decimal('markup_percentage', 5, 2)->nullable()->after('selling_price');
            $table->decimal('tax_rate', 5, 2)->default(0.12)->after('markup_percentage');
            
            // Rename and update stock fields
            $table->renameColumn('stocks', 'stock_quantity');
            $table->integer('reorder_level')->default(0)->after('stock_quantity');
            $table->integer('max_stock_level')->nullable()->after('reorder_level');
            $table->string('unit_of_measure', 50)->default('piece')->after('max_stock_level');
            
            // Add product specifications
            $table->decimal('weight', 8, 2)->nullable()->after('unit_of_measure');
            $table->string('dimensions', 100)->nullable()->after('weight');
            
            // Rename image to image_url
            $table->renameColumn('image', 'image_url');
            
            // Add product flags
            $table->boolean('is_active')->default(true)->after('image_url');
            $table->boolean('is_serialized')->default(false)->after('is_active');
            $table->integer('warranty_period')->nullable()->after('is_serialized')->comment('Warranty period in months');
            
            // Add foreign keys
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            
            // Add indexes
            $table->index(['sku', 'is_active']);
            $table->index('barcode');
            $table->index(['category_id', 'is_active']);
            $table->index(['supplier_id', 'is_active']);
            $table->index('stock_quantity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['sku', 'is_active']);
            $table->dropIndex(['barcode']);
            $table->dropIndex(['category_id', 'is_active']);
            $table->dropIndex(['supplier_id', 'is_active']);
            $table->dropIndex(['stock_quantity']);
            
            // Drop foreign keys
            $table->dropForeign(['category_id']);
            
            // Remove new columns
            $table->dropColumn([
                'category_id', 'model', 'cost_price', 'markup_percentage', 'tax_rate',
                'reorder_level', 'max_stock_level', 'unit_of_measure', 'weight', 'dimensions',
                'is_active', 'is_serialized', 'warranty_period'
            ]);
            
            // Rename columns back
            $table->renameColumn('sku', 'stock_keeping_unit');
            $table->renameColumn('selling_price', 'price');
            $table->renameColumn('stock_quantity', 'stocks');
            $table->renameColumn('image_url', 'image');
            
            // Add back category string column
            $table->string('category')->after('name');
        });
    }
};
