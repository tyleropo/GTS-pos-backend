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
        Schema::table('suppliers', function (Blueprint $table) {
            // Add supplier code before name
            $table->string('supplier_code', 50)->unique()->after('id');
            
            // Rename existing columns
            $table->renameColumn('name', 'company_name');
            $table->renameColumn('place', 'address');
            $table->renameColumn('negotiator', 'contact_person');
            $table->renameColumn('contact', 'phone');
            
            // Add new contact and address fields
            $table->string('email')->nullable()->after('contact_person');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 100)->nullable()->after('city');
            $table->string('postal_code', 20)->nullable()->after('state');
            $table->string('country', 100)->nullable()->after('postal_code');
            $table->string('payment_terms', 100)->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('payment_terms');
            
            // Add indexes
            $table->index(['supplier_code', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropIndex(['supplier_code', 'is_active']);
            $table->dropColumn(['supplier_code', 'email', 'city', 'state', 'postal_code', 'country', 'payment_terms', 'is_active']);
            
            $table->renameColumn('company_name', 'name');
            $table->renameColumn('address', 'place');
            $table->renameColumn('contact_person', 'negotiator');
            $table->renameColumn('phone', 'contact');
        });
    }
};
