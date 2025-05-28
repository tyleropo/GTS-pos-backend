<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProductBrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $brands = [
            'All',
            'Sony',
            'Asus',
            'Samsung',
            'Logitech',
        ];

        foreach ($brands as $brand) {
            DB::table('product_brands')->insert([
                'name' => $brand,
            ]);
        }
    }
}
