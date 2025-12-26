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
    Schema::create('transactions', function (Blueprint $table) {
        $table->uuid('id')->primary();
        $table->string('invoice_number')->unique();
        $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
        $table->decimal('subtotal', 12, 2)->default(0);
        $table->decimal('tax', 12, 2)->default(0);
        $table->decimal('total', 12, 2)->default(0);
        $table->enum('payment_method', ['cash', 'card', 'gcash'])->default('cash');
        $table->json('items')->nullable(); // line items snapshot
        $table->json('meta')->nullable();  // any additional data
        $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
