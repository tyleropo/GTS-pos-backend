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
                'product_category' => 28,
                'product_brand' => 3,
                'description' => 'The ultimate flagship smartphone with a 200MP camera and powerful Snapdragon processor.',
                'specs' => '200MP Camera, 12GB RAM, 512GB Storage, 5000mAh Battery',
                'price' => 1199.99,
            ],
            [
                'name' => 'ASUS ROG Zephyrus G14 Laptop',
                'product_brand' => 2,
                'product_category' => 26,
                'description' => 'High-performance gaming laptop with AMD Ryzen 9 and RTX 4060 graphics.',
                'specs' => 'AMD Ryzen 9, 16GB RAM, 1TB SSD, NVIDIA RTX 4060',
                'price' => 1599.99,
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'product_category' => 3,
                'product_brand' => 1,
                'description' => 'Premium noise-canceling headphones for immersive sound and comfort.',
                'specs' => 'Bluetooth 5.2, 30-hour battery life, Active Noise Cancellation',
                'price' => 349.99,
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'product_category' => 11,
                'product_brand' => 4,
                'description' => 'Ergonomic wireless mouse with ultra-fast scrolling and customizable buttons.',
                'specs' => 'DPI 8000, USB-C charging, 70-day battery life, Multi-Device Support',
                'price' => 99.99,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
