<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Parent categories
            ['id' => 1, 'name' => 'Mobile Phones', 'description' => 'Smartphones and feature phones', 'parent_id' => null, 'is_active' => true],
            ['id' => 2, 'name' => 'Computers & Laptops', 'description' => 'Desktop computers, laptops, and tablets', 'parent_id' => null, 'is_active' => true],
            ['id' => 3, 'name' => 'Accessories', 'description' => 'Phone and computer accessories', 'parent_id' => null, 'is_active' => true],
            ['id' => 4, 'name' => 'Smart Devices', 'description' => 'Smart watches, bands, and wearables', 'parent_id' => null, 'is_active' => true],
            ['id' => 5, 'name' => 'Audio', 'description' => 'Headphones, speakers, and audio equipment', 'parent_id' => null, 'is_active' => true],
            ['id' => 6, 'name' => 'Gaming', 'description' => 'Gaming consoles and accessories', 'parent_id' => null, 'is_active' => true],
            
            // Mobile Phone subcategories
            ['id' => 7, 'name' => 'Android Phones', 'description' => 'Android smartphones', 'parent_id' => 1, 'is_active' => true],
            ['id' => 8, 'name' => 'iPhones', 'description' => 'Apple iPhones', 'parent_id' => 1, 'is_active' => true],
            ['id' => 9, 'name' => 'Feature Phones', 'description' => 'Basic mobile phones', 'parent_id' => 1, 'is_active' => true],
            
            // Computer subcategories
            ['id' => 10, 'name' => 'Laptops', 'description' => 'Portable computers', 'parent_id' => 2, 'is_active' => true],
            ['id' => 11, 'name' => 'Desktop PCs', 'description' => 'Desktop computers', 'parent_id' => 2, 'is_active' => true],
            ['id' => 12, 'name' => 'Tablets', 'description' => 'Tablet computers', 'parent_id' => 2, 'is_active' => true],
            
            // Accessories subcategories
            ['id' => 13, 'name' => 'Phone Cases', 'description' => 'Protective cases for phones', 'parent_id' => 3, 'is_active' => true],
            ['id' => 14, 'name' => 'Screen Protectors', 'description' => 'Tempered glass and film protectors', 'parent_id' => 3, 'is_active' => true],
            ['id' => 15, 'name' => 'Chargers & Cables', 'description' => 'Charging accessories', 'parent_id' => 3, 'is_active' => true],
            ['id' => 16, 'name' => 'Power Banks', 'description' => 'Portable chargers', 'parent_id' => 3, 'is_active' => true],
            ['id' => 17, 'name' => 'Memory Cards', 'description' => 'SD cards and storage', 'parent_id' => 3, 'is_active' => true],
            
            // Smart Devices subcategories
            ['id' => 18, 'name' => 'Smart Watches', 'description' => 'Wearable smart watches', 'parent_id' => 4, 'is_active' => true],
            ['id' => 19, 'name' => 'Fitness Bands', 'description' => 'Activity and fitness trackers', 'parent_id' => 4, 'is_active' => true],
            
            // Audio subcategories
            ['id' => 20, 'name' => 'Wireless Earbuds', 'description' => 'Bluetooth earbuds', 'parent_id' => 5, 'is_active' => true],
            ['id' => 21, 'name' => 'Headphones', 'description' => 'Over-ear and on-ear headphones', 'parent_id' => 5, 'is_active' => true],
            ['id' => 22, 'name' => 'Speakers', 'description' => 'Bluetooth and wired speakers', 'parent_id' => 5, 'is_active' => true],
            
            // Gaming subcategories
            ['id' => 23, 'name' => 'Consoles', 'description' => 'Gaming consoles', 'parent_id' => 6, 'is_active' => true],
            ['id' => 24, 'name' => 'Controllers', 'description' => 'Gaming controllers', 'parent_id' => 6, 'is_active' => true],
            ['id' => 25, 'name' => 'Gaming Headsets', 'description' => 'Headsets for gaming', 'parent_id' => 6, 'is_active' => true],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'id' => $category['id'],
                'name' => $category['name'],
                'description' => $category['description'],
                'parent_id' => $category['parent_id'],
                'is_active' => $category['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
