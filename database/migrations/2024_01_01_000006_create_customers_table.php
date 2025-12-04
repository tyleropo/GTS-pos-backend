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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->enum('type', ['Regular', 'VIP'])->default('Regular');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->decimal('total_spent', 12, 2)->default(0);
            $table->integer('orders')->default(0);
            $table->date('last_purchase')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('email');
            $table->index('phone');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
