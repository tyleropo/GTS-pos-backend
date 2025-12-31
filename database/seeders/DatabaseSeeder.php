<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create test user first
        User::factory()->create([
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'roles' => ['admin'],
        ]);

        // Seed POS data in correct order (respecting foreign key dependencies)
        $this->call([
            CategorySeeder::class,
            SupplierSeeder::class,
            ProductSeeder::class,
            CustomerSeeder::class,
            RepairSeeder::class,
            TransactionSeeder::class,
            PurchaseOrderSeeder::class,
            UserSeeder::class,
            AuditLogSeeder::class,
        ]);
    }
}
