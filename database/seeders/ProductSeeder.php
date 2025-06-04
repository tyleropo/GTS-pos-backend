<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Samsung Galaxy S23 Ultra',
                'category' => 'Mobile Phones & Smartphones',
                'brand' => 'Samsung',
                'description' => 'The ultimate flagship smartphone with a 200MP camera and powerful Snapdragon processor.',
                'stock_keeping_unit' => 'LPR-005',
                'stocks' => 64,
                'price' => 9256.78,
                'supplier_id' => 1,
                'barcode' => 'ABC123456',
            ],
            [
                'name' => 'ASUS ROG Zephyrus G14 Laptop',
                'category' => 'Laptops',
                'brand' => 'Asus',
                'description' => 'High-performance gaming laptop with AMD Ryzen 9 and RTX 4060 graphics.',
                'price' => 1599.99,
                'stock_keeping_unit' => 'WMS-009',
                'stocks' => 4,
                'supplier_id' => 3,
                'barcode' => 'XYZ789012',
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'category' => 'Anti-Static Equipment',
                'brand' => 'Sony',
                'description' => 'Premium noise-canceling headphones for immersive sound and comfort.',
                'price' => 349.99,
                'stock_keeping_unit' => 'EHD-007',
                'stocks' => 19,
                'supplier_id' => 2,
                'barcode' => 'LMN345678',
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'category' => 'Computer Input/Output Devices',
                'brand' => 'Logitech',
                'description' => 'Ergonomic wireless mouse with ultra-fast scrolling and customizable buttons.',
                'price' => 99.99,
                'stock_keeping_unit' => 'BTS-004',
                'stocks' => 37,
                'supplier_id' => 1,
                'barcode' => 'IOP746325',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
