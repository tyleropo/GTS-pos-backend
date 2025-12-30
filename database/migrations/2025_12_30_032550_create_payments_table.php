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
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_order_id')->constrained()->onDelete('cascade');
            $table->string('reference_number')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('payment_method', ['cash', 'cheque', 'bank_transfer', 'credit_card'])->default('cash');
            $table->date('date_received');
            $table->boolean('is_deposited')->default(false);
            $table->date('date_deposited')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
