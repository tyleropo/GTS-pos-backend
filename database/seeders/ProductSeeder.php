<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::whereNotNull('parent_id')->get();
        $suppliers = Supplier::all();

        if ($categories->isEmpty() || $suppliers->isEmpty()) {
            $this->command->warn('Please run CategorySeeder and SupplierSeeder first');
            return;
        }

        $products = [
            // Mobile Phones
            [
                'sku' => 'PHONE-001',
                'barcode' => '1234567890123',
                'name' => 'iPhone 15 Pro',
                'description' => 'Latest Apple flagship smartphone',
                'brand' => 'Apple',
                'model' => '15 Pro',
                'cost_price' => 899.00,
                'selling_price' => 1099.00,
                'markup_percentage' => 22.25,
                'tax_rate' => 8.00,
                'stock_quantity' => 25,
                'reorder_level' => 5,
                'max_stock_level' => 50,
                'warranty_period' => 12,
            ],
            [
                'sku' => 'PHONE-002',
                'barcode' => '1234567890124',
                'name' => 'Samsung Galaxy S24',
                'description' => 'Premium Android smartphone',
                'brand' => 'Samsung',
                'model' => 'Galaxy S24',
                'cost_price' => 749.00,
                'selling_price' => 949.00,
                'markup_percentage' => 26.70,
                'tax_rate' => 8.00,
                'stock_quantity' => 30,
                'reorder_level' => 8,
                'max_stock_level' => 60,
                'warranty_period' => 24,
            ],
            // Laptops
            [
                'sku' => 'LAP-001',
                'barcode' => '1234567890125',
                'name' => 'MacBook Pro 14"',
                'description' => 'Professional laptop with M3 chip',
                'brand' => 'Apple',
                'model' => 'MacBook Pro 14" M3',
                'cost_price' => 1599.00,
                'selling_price' => 1999.00,
                'markup_percentage' => 25.02,
                'tax_rate' => 8.00,
                'stock_quantity' => 12,
                'reorder_level' => 3,
                'max_stock_level' => 20,
                'warranty_period' => 12,
                'is_serialized' => true,
            ],
            [
                'sku' => 'LAP-002',
                'barcode' => '1234567890126',
                'name' => 'Dell XPS 15',
                'description' => 'High-performance Windows laptop',
                'brand' => 'Dell',
                'model' => 'XPS 15 9530',
                'cost_price' => 1299.00,
                'selling_price' => 1699.00,
                'markup_percentage' => 30.79,
                'tax_rate' => 8.00,
                'stock_quantity' => 15,
                'reorder_level' => 4,
                'max_stock_level' => 25,
                'warranty_period' => 12,
                'is_serialized' => true,
            ],
            // Tablets
            [
                'sku' => 'TAB-001',
                'barcode' => '1234567890127',
                'name' => 'iPad Air',
                'description' => 'Versatile tablet for work and play',
                'brand' => 'Apple',
                'model' => 'iPad Air 5th Gen',
                'cost_price' => 499.00,
                'selling_price' => 649.00,
                'markup_percentage' => 30.06,
                'tax_rate' => 8.00,
                'stock_quantity' => 20,
                'reorder_level' => 5,
                'max_stock_level' => 40,
                'warranty_period' => 12,
            ],
            // Accessories
            [
                'sku' => 'ACC-001',
                'name' => 'USB-C Cable 2m',
                'description' => 'High-quality USB-C charging cable',
                'brand' => 'Anker',
                'model' => 'PowerLine III',
                'cost_price' => 12.00,
                'selling_price' => 24.99,
                'markup_percentage' => 108.25,
                'tax_rate' => 8.00,
                'stock_quantity' => 150,
                'reorder_level' => 30,
                'max_stock_level' => 300,
                'warranty_period' => 18,
            ],
            [
                'sku' => 'ACC-002',
                'name' => 'Wireless Mouse',
                'description' => 'Ergonomic wireless mouse',
                'brand' => 'Logitech',
                'model' => 'MX Master 3S',
                'cost_price' => 69.00,
                'selling_price' => 99.99,
                'markup_percentage' => 44.91,
                'tax_rate' => 8.00,
                'stock_quantity' => 45,
                'reorder_level' => 10,
                'max_stock_level' => 80,
                'warranty_period' => 12,
            ],
            // Audio
            [
                'sku' => 'AUD-001',
                'name' => 'AirPods Pro',
                'description' => 'Active noise cancelling earbuds',
                'brand' => 'Apple',
                'model' => 'AirPods Pro 2nd Gen',
                'cost_price' => 189.00,
                'selling_price' => 249.00,
                'markup_percentage' => 31.75,
                'tax_rate' => 8.00,
                'stock_quantity' => 35,
                'reorder_level' => 10,
                'max_stock_level' => 60,
                'warranty_period' => 12,
            ],
            [
                'sku' => 'AUD-002',
                'name' => 'Sony WH-1000XM5',
                'description' => 'Premium noise-cancelling headphones',
                'brand' => 'Sony',
                'model' => 'WH-1000XM5',
                'cost_price' => 299.00,
                'selling_price' => 399.00,
                'markup_percentage' => 33.44,
                'tax_rate' => 8.00,
                'stock_quantity' => 18,
                'reorder_level' => 5,
                'max_stock_level' => 30,
                'warranty_period' => 12,
            ],
            // Gaming
            [
                'sku' => 'GAME-001',
                'barcode' => '1234567890128',
                'name' => 'PlayStation 5',
                'description' => 'Next-gen gaming console',
                'brand' => 'Sony',
                'model' => 'PS5',
                'cost_price' => 399.00,
                'selling_price' => 499.00,
                'markup_percentage' => 25.06,
                'tax_rate' => 8.00,
                'stock_quantity' => 8,
                'reorder_level' => 2,
                'max_stock_level' => 15,
                'warranty_period' => 12,
                'is_serialized' => true,
            ],
        ];

        foreach ($products as $productData) {
            // Assign random category and supplier
            $category = $categories->random();
            $supplier = $suppliers->random();

            $productData['category_id'] = $category->id;
            $productData['supplier_id'] = $supplier->id;

            Product::create($productData);
        }

        $this->command->info('Created ' . count($products) . ' products');
    }
}
