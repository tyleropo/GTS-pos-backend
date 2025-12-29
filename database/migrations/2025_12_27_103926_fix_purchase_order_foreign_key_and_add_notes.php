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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // 1. Drop existing foreign key. Note: Laravel typically names it table_column_foreign
            $table->dropForeign(['supplier_id']);

            // 2. Add new foreign key referencing customers
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('customers')
                  ->nullOnDelete();

            // 3. Add notes column
            $table->text('notes')->nullable()->after('items');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Drop notes
            $table->dropColumn('notes');

            // Drop customer FK
            $table->dropForeign(['supplier_id']);

            // Restore supplier FK (referencing suppliers table if it exists, or just leave it)
            // Assuming we want to revert to previous state where it referenced suppliers
            $table->foreign('supplier_id')
                  ->references('id')
                  ->on('suppliers')
                  ->nullOnDelete();
        });
    }
};
