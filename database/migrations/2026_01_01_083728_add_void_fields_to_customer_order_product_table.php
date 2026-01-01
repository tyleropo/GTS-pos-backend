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
        Schema::table('customer_order_product', function (Blueprint $table) {
            $table->boolean('is_voided')->default(false);
            $table->string('void_reason')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_order_product', function (Blueprint $table) {
            $table->dropColumn(['is_voided', 'void_reason']);
        });
    }
};
