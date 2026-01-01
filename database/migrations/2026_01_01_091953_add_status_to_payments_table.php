<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')->nullable()->after('type');
            $table->timestamp('status_updated_at')->nullable()->after('status');
        });

        // Backfill status for existing records based on type, payment_method, and is_deposited
        $this->backfillStatus();
    }

    /**
     * Backfill status values for existing payments
     */
    private function backfillStatus(): void
    {
        // For inbound payments (receivables)
        DB::table('payments')
            ->where('type', 'inbound')
            ->where('payment_method', 'check')
            ->where('is_deposited', true)
            ->update(['status' => 'deposited', 'status_updated_at' => DB::raw('date_deposited')]);

        DB::table('payments')
            ->where('type', 'inbound')
            ->where('payment_method', 'check')
            ->where('is_deposited', false)
            ->update(['status' => 'pending_deposit', 'status_updated_at' => DB::raw('date_received')]);

        DB::table('payments')
            ->where('type', 'inbound')
            ->where('payment_method', '!=', 'check')
            ->update(['status' => 'received', 'status_updated_at' => DB::raw('date_received')]);

        // For outbound payments (payables)
        DB::table('payments')
            ->where('type', 'outbound')
            ->where('payment_method', 'check')
            ->where('is_deposited', true)
            ->update(['status' => 'cleared', 'status_updated_at' => DB::raw('date_deposited')]);

        DB::table('payments')
            ->where('type', 'outbound')
            ->where('payment_method', 'check')
            ->where('is_deposited', false)
            ->update(['status' => 'issued', 'status_updated_at' => DB::raw('date_received')]);

        DB::table('payments')
            ->where('type', 'outbound')
            ->where('payment_method', '!=', 'check')
            ->update(['status' => 'paid', 'status_updated_at' => DB::raw('date_received')]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'status_updated_at']);
        });
    }
};
