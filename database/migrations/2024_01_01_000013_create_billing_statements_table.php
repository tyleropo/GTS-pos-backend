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
        Schema::create('billing_statements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('customer_id');
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('repair_subtotal', 12, 2)->default(0);
            $table->decimal('product_subtotal', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2);
            $table->date('generated_date');
            $table->enum('status', ['Draft', 'Sent', 'Paid'])->default('Draft');
            $table->timestamps();

            // Foreign keys
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('restrict');

            // Indexes
            $table->index('customer_id');
            $table->index('period_start');
            $table->index('period_end');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_statements');
    }
};
