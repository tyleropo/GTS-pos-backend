<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
                'description' => 'Electronic devices and accessories',
                'children' => [
                    ['name' => 'Mobile Phones', 'description' => 'Smartphones and feature phones'],
                    ['name' => 'Laptops', 'description' => 'Notebook computers'],
                    ['name' => 'Tablets', 'description' => 'Tablet computers'],
                    ['name' => 'Accessories', 'description' => 'Phone and laptop accessories'],
                ],
            ],
            [
                'name' => 'Computer Components',
                'description' => 'PC parts and peripherals',
                'children' => [
                    ['name' => 'Processors', 'description' => 'CPUs'],
                    ['name' => 'Graphics Cards', 'description' => 'GPUs'],
                    ['name' => 'Memory', 'description' => 'RAM modules'],
                    ['name' => 'Storage', 'description' => 'SSDs and HDDs'],
                ],
            ],
            [
                'name' => 'Audio & Video',
                'description' => 'Audio and video equipment',
                'children' => [
                    ['name' => 'Headphones', 'description' => 'Wired and wireless headphones'],
                    ['name' => 'Speakers', 'description' => 'Portable and home speakers'],
                    ['name' => 'Webcams', 'description' => 'USB webcams'],
                ],
            ],
            [
                'name' => 'Gaming',
                'description' => 'Gaming equipment',
                'children' => [
                    ['name' => 'Consoles', 'description' => 'Gaming consoles'],
                    ['name' => 'Controllers', 'description' => 'Game controllers'],
                    ['name' => 'Gaming Accessories', 'description' => 'Gaming peripherals'],
                ],
            ],
            [
                'name' => 'Networking',
                'description' => 'Network equipment',
                'children' => [
                    ['name' => 'Routers', 'description' => 'WiFi routers'],
                    ['name' => 'Switches', 'description' => 'Network switches'],
                    ['name' => 'Cables', 'description' => 'Network cables'],
                ],
            ],
        ];

        foreach ($categories as $categoryData) {
            $children = $categoryData['children'] ?? [];
            unset($categoryData['children']);

            $parent = Category::create($categoryData);

            foreach ($children as $childData) {
                $childData['parent_id'] = $parent->id;
                Category::create($childData);
            }
        }
    }
}
