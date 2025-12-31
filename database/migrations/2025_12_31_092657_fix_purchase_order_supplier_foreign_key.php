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
            // Drop the incorrect foreign key (which referenced customers)
            // Note: In SQLite, this involves recreating the table, which Laravel handles.
            $table->dropForeign(['supplier_id']);
            
            // Add the correct foreign key referencing suppliers
            $table->foreign('supplier_id')->references('id')->on('suppliers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['supplier_id']);
            // Restore the previous (incorrect) or just leave it?
            // Technically down should reverse, so it would point back to customers? 
            // But pointing to customers was a BUG. So maybe just point to suppliers implies 'restoring' to state?
            // But for strict reversal:
            $table->foreign('supplier_id')->references('id')->on('customers')->nullOnDelete();
        });
    }
};
