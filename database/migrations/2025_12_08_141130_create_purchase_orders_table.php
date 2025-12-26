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
    Schema::create('purchase_orders', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('po_number')->unique();
        $table->foreignUuid('supplier_id')->nullable()->constrained()->nullOnDelete();
        $table->enum('status', ['draft', 'submitted', 'received', 'cancelled'])->default('draft');
        $table->date('expected_at')->nullable();
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('tax', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->json('items')->nullable();
        $table->json('meta')->nullable();
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
