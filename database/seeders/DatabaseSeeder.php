<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Core data - no dependencies
        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(SupplierSeeder::class);
        
        // Products depend on categories and suppliers
        $this->call(ProductSeeder::class);
        
        // Product serials depend on products
        $this->call(ProductSerialSeeder::class);
        
        // Customers - no dependencies
        $this->call(CustomerSeeder::class);
        
        // Transactions depend on customers, users, and products
        $this->call(TransactionSeeder::class);
        
        // Purchase orders depend on suppliers, users, and products
        $this->call(PurchaseOrderSeeder::class);
        
        // Repairs depend on customers and users
        $this->call(RepairSeeder::class);
        
        // Inventory movements depend on products, users, transactions, and purchase orders
        $this->call(InventoryMovementSeeder::class);
    }
}
