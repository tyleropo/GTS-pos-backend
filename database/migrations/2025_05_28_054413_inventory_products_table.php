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
        Schema::create('inventory_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_product')
                ->constrained('products')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('stock_keeping_unit');
            $table->integer('stocks');
            $table->foreignId('inventory_supplier')
                ->nullable()
                ->constrained('suppliers')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->timestamps();

            $table->unique(['inventory_product', 'inventory_supplier']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
