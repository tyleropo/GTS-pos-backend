<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create users first
        $this->call(UserSeeder::class);
        
        // Dependencies
        $this->call(CategorySeeder::class);
        $this->call(SupplierSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(CustomerSeeder::class);
        
        // Transactions depend on products, customers, and users
        $this->call(TransactionSeeder::class);
        
        $this->command->info('All seeders completed successfully!');
    }
}
