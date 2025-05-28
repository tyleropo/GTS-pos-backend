<?php

namespace Database\Seeders;

use App\Models\InventoryProducts;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        InventoryProducts::create([
            'inventory_product' => 1,
            'stock_keeping_unit' => 'LPR-005',
            'stocks' => 64,
            'inventory_supplier' => 1,
        ]);

        InventoryProducts::create([
            'inventory_product' => 2,
            'stock_keeping_unit' => 'WMS-009',
            'stocks' => 4,
            'inventory_supplier' => 3,
        ]);

        InventoryProducts::create([
            'inventory_product' => 3,
            'stock_keeping_unit' => 'EHD-007',
            'stocks' => 19,
            'inventory_supplier' => 2,
        ]);

        InventoryProducts::create([
            'inventory_product' => 4,
            'stock_keeping_unit' => 'BTS-004',
            'stocks' => 37,
            'inventory_supplier' => 1,
        ]);
    }
}
