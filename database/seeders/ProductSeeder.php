<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $smartphones = Category::where('name', 'Smartphones')->first();
        $laptops = Category::where('name', 'Laptops')->first();
        $smartwatches = Category::where('name', 'Smartwatches')->first();
        $cases = Category::where('name', 'Cases')->first();
        $chargers = Category::where('name', 'Chargers')->first();
        $cables = Category::where('name', 'Cables')->first();
        
        $supplier1 = Supplier::where('supplier_code', 'SUP-001')->first();
        $supplier2 = Supplier::where('supplier_code', 'SUP-002')->first();
        $supplier3 = Supplier::where('supplier_code', 'SUP-003')->first();
        $supplier4 = Supplier::where('supplier_code', 'SUP-004')->first();

        $products = [
            // Smartphones - some low stock
            [
                'sku' => 'PHONE-001',
                'barcode' => '8806094932652',
                'name' => 'Samsung Galaxy S24 Ultra',
                'description' => '6.8" Dynamic AMOLED, Snapdragon 8 Gen 3, 200MP Camera',
                'category_id' => $smartphones->id,
                'supplier_id' => $supplier1->id,
                'brand' => 'Samsung',
                'model' => 'S24 Ultra',
                'cost_price' => 55000.00,
                'selling_price' => 69999.00,
                'markup_percentage' => 27.27,
                'tax_rate' => 12.00,
                'stock_quantity' => 25,
                'reorder_level' => 10,
                'max_stock_level' => 50,
                'unit_of_measure' => 'pcs',
                'weight' => 234,
                'warranty_period' => 12,
                'is_active' => true,
            ],
            [
                'sku' => 'PHONE-002',
                'barcode' => '194253406907',
                'name' => 'iPhone 15 Pro Max',
                'description' => '6.7" Super Retina XDR, A17 Pro, 48MP Camera System',
                'category_id' => $smartphones->id,
                'supplier_id' => $supplier1->id,
                'brand' => 'Apple',
                'model' => '15 Pro Max',
                'cost_price' => 65000.00,
                'selling_price' => 79990.00,
                'markup_percentage' => 23.06,
                'tax_rate' => 12.00,
                'stock_quantity' => 8, // Low stock
                'reorder_level' => 10,
                'max_stock_level' => 40,
                'unit_of_measure' => 'pcs',
                'weight' => 221,
                'warranty_period' => 12,
                'is_active' => true,
            ],
            [
                'sku' => 'PHONE-003',
                'barcode' => '6941812725931',
                'name' => 'Xiaomi 14 Pro',
                'description' => '6.73" AMOLED, Snapdragon 8 Gen 3, Leica Triple Camera',
                'category_id' => $smartphones->id,
                'supplier_id' => $supplier2->id,
                'brand' => 'Xiaomi',
                'model' => '14 Pro',
                'cost_price' => 38000.00,
                'selling_price' => 47999.00,
                'markup_percentage' => 26.31,
                'tax_rate' => 12.00,
                'stock_quantity' => 5, // Low stock
                'reorder_level' => 10,
                'max_stock_level' => 35,
                'unit_of_measure' => 'pcs',
                'weight' => 220,
                'warranty_period' => 12,
                'is_active' => true,
            ],

            // Laptops
            [
                'sku' => 'LAP-001',
                'barcode' => '195553821278',
                'name' => 'MacBook Pro 14" M3 Pro',
                'description' => '14.2" Liquid Retina XDR, M3 Pro chip, 18GB RAM, 512GB SSD',
                'category_id' => $laptops->id,
                'supplier_id' => $supplier1->id,
                'brand' => 'Apple',
                'model' => 'MacBook Pro 14"',
                'cost_price' => 115000.00,
                'selling_price' => 139990.00,
                'markup_percentage' => 21.73,
                'tax_rate' => 12.00,
                'stock_quantity' => 12,
                'reorder_level' => 5,
                'max_stock_level' => 20,
                'unit_of_measure' => 'pcs',
                'weight' => 1600,
                'warranty_period' => 12,
                'is_active' => true,
            ],
            [
                'sku' => 'LAP-002',
                'barcode' => '4711387126646',
                'name' => 'ASUS ROG Zephyrus G16',
                'description' => '16" QHD+ 240Hz, Intel Core Ultra 9, RTX 4070, 32GB RAM',
                'category_id' => $laptops->id,
                'supplier_id' => $supplier3->id,
                'brand' => 'ASUS',
                'model' => 'ROG Zephyrus G16',
                'cost_price' => 95000.00,
                'selling_price' => 119999.00,
                'markup_percentage' => 26.31,
                'tax_rate' => 12.00,
                'stock_quantity' => 7,
                'reorder_level' => 5,
                'max_stock_level' => 15,
                'unit_of_measure' => 'pcs',
                'weight' => 1950,
                'warranty_period' => 24,
                'is_active' => true,
            ],

            // Smartwatches
            [
                'sku' => 'WATCH-001',
                'barcode' => '194253906896',
                'name' => 'Apple Watch Ultra 2',
                'description' => '49mm Titanium Case, GPS + Cellular, Action Button',
                'category_id' => $smartwatches->id,
                'supplier_id' => $supplier1->id,
                'brand' => 'Apple',
                'model' => 'Watch Ultra 2',
                'cost_price' => 38000.00,
                'selling_price' => 49990.00,
                'markup_percentage' => 31.55,
                'tax_rate' => 12.00,
                'stock_quantity' => 15,
                'reorder_level' => 8,
                'max_stock_level' => 30,
                'unit_of_measure' => 'pcs',
                'weight' => 61.4,
                'warranty_period' => 12,
                'is_active' => true,
            ],

            // Accessories - Cases
            [
                'sku' => 'CASE-001',
                'barcode' => '8809946694551',
                'name' => 'Samsung Silicone Case S24 Ultra',
                'description' => 'Premium silicone case with soft interior lining',
                'category_id' => $cases->id,
                'supplier_id' => $supplier4->id,
                'brand' => 'Samsung',
                'model' => 'S24 Ultra Case',
                'cost_price' => 450.00,
                'selling_price' => 799.00,
                'markup_percentage' => 77.56,
                'tax_rate' => 12.00,
                'stock_quantity' => 50,
                'reorder_level' => 20,
                'max_stock_level' => 100,
                'unit_of_measure' => 'pcs',
                'weight' => 45,
                'warranty_period' => 6,
                'is_active' => true,
            ],
            [
                'sku' => 'CASE-002',
                'barcode' => '194253907725',
                'name' => 'iPhone 15 Pro Max Leather Case',
                'description' => 'Genuine leather case with MagSafe',
                'category_id' => $cases->id,
                'supplier_id' => $supplier4->id,
                'brand' => 'Apple',
                'model' => '15 Pro Max Leather',
                'cost_price' => 2200.00,
                'selling_price' => 3499.00,
                'markup_percentage' => 59.05,
                'tax_rate' => 12.00,
                'stock_quantity' => 2, // Very low stock
                'reorder_level' => 10,
                'max_stock_level' => 50,
                'unit_of_measure' => 'pcs',
                'weight' => 38,
                'warranty_period' => 6,
                'is_active' => true,
            ],

            // Chargers
            [
                'sku' => 'CHRG-001',
                'barcode' => '6941812727867',
                'name' => 'Xiaomi 120W HyperCharge Adapter',
                'description' => '120W GaN fast charger with USB-C cable',
                'category_id' => $chargers->id,
                'supplier_id' => $supplier4->id,
                'brand' => 'Xiaomi',
                'model' => '120W GaN',
                'cost_price' => 1200.00,
                'selling_price' => 1999.00,
                'markup_percentage' => 66.58,
                'tax_rate' => 12.00,
                'stock_quantity' => 35,
                'reorder_level' => 15,
                'max_stock_level' => 60,
                'unit_of_measure' => 'pcs',
                'weight' => 120,
                'warranty_period' => 12,
                'is_active' => true,
            ],

            // Cables
            [
                'sku' => 'CABLE-001',
                'barcode' => '194253906766',
                'name' => 'USB-C to Lightning Cable 1m',
                'description' => 'Apple USB-C to Lightning cable, fast charging support',
                'category_id' => $cables->id,
                'supplier_id' => $supplier4->id,
                'brand' => 'Apple',
                'model' => 'C to Lightning 1m',
                'cost_price' => 800.00,
                'selling_price' => 1299.00,
                'markup_percentage' => 62.38,
                'tax_rate' => 12.00,
                'stock_quantity' => 100,
                'reorder_level' => 30,
                'max_stock_level' => 200,
                'unit_of_measure' => 'pcs',
                'weight' => 25,
                'warranty_period' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
