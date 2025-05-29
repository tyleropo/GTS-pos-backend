<?php

namespace Database\Seeders;

use App\Models\InventoryProduct;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventoryProduct::create([
            'product_id' => 1,
            'stock_keeping_unit' => 'LPR-005',
            'stocks' => 64,
            'supplier_id' => 1,
        ]);

        InventoryProduct::create([
            'product_id' => 2,
            'stock_keeping_unit' => 'WMS-009',
            'stocks' => 4,
            'supplier_id' => 3,
        ]);

        InventoryProduct::create([
            'product_id' => 3,
            'stock_keeping_unit' => 'EHD-007',
            'stocks' => 19,
            'supplier_id' => 2,
        ]);

        InventoryProduct::create([
            'product_id' => 4,
            'stock_keeping_unit' => 'BTS-004',
            'stocks' => 37,
            'supplier_id' => 1,
        ]);
    }
}
