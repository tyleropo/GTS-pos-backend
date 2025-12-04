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
        Schema::create('billing_line_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('billing_statement_id');
            $table->enum('type', ['repair', 'product']);
            $table->string('reference_id');
            $table->date('date');
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->timestamps();

            // Foreign keys
            $table->foreign('billing_statement_id')
                  ->references('id')
                  ->on('billing_statements')
                  ->onDelete('cascade');

            // Indexes
            $table->index('billing_statement_id');
            $table->index('type');
            $table->index('reference_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_line_items');
    }
};
