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
        Schema::create('repairs', function (Blueprint $table) {
            $table->uuid('id')->primary();
    $table->string('ticket_number')->unique();
    $table->foreignUuid('customer_id')->nullable()->constrained()->nullOnDelete();
    $table->string('device')->nullable();
    $table->string('serial_number')->nullable();
    $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
    $table->text('issue_description')->nullable();
    $table->text('resolution')->nullable();
    $table->timestamp('promised_at')->nullable();
    $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repairs');
    }
};
