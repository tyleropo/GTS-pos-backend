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
        // Rename table
        Schema::rename('product_categories', 'categories');
        
        // Add new columns
        Schema::table('categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('name');
            $table->unsignedBigInteger('parent_id')->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('parent_id');
            $table->timestamps();
            
            // Add foreign key for hierarchical categories
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');
            
            // Add indexes
            $table->index(['name', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex(['name', 'is_active']);
            $table->dropColumn(['description', 'parent_id', 'is_active', 'created_at', 'updated_at']);
        });
        
        Schema::rename('categories', 'product_categories');
    }
};
