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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'payable_id')) {
                $table->uuid('payable_id')->nullable()->after('id');
            }
            if (!Schema::hasColumn('payments', 'payable_type')) {
                $table->string('payable_type')->nullable()->after('payable_id');
            }
            if (!Schema::hasColumn('payments', 'type')) {
                $table->enum('type', ['inbound', 'outbound'])->default('outbound')->after('payable_type');
            }
        });

        // Migrate existing purchase_order_id data
        DB::table('payments')->whereNotNull('purchase_order_id')->update([
            'payable_id' => DB::raw('purchase_order_id'),
            'payable_type' => 'App\\Models\\PurchaseOrder',
            'type' => 'outbound'
        ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignUuid('purchase_order_id')->nullable()->constrained()->nullOnDelete();
        });

        // Restore data
        DB::table('payments')->where('payable_type', 'App\\Models\\PurchaseOrder')->update([
            'purchase_order_id' => DB::raw('payable_id')
        ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['payable_id', 'payable_type', 'type']);
        });
    }
};
