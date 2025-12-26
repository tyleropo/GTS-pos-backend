<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        // Root categories
        $electronics = Category::create([
            'name' => 'Electronics',
            'description' => 'Electronic devices and gadgets',
        ]);

        $accessories = Category::create([
            'name' => 'Accessories',
            'description' => 'Device accessories and peripherals',
        ]);

        $services = Category::create([
            'name' => 'Services',
            'description' => 'Repair and maintenance services',
        ]);

        // Sub-categories under Electronics
        Category::create([
            'name' => 'Smartphones',
            'description' => 'Mobile phones and smartphones',
            'parent_id' => $electronics->id,
        ]);

        Category::create([
            'name' => 'Laptops',
            'description' => 'Laptop computers',
            'parent_id' => $electronics->id,
        ]);

        Category::create([
            'name' => 'Tablets',
            'description' => 'Tablet devices',
            'parent_id' => $electronics->id,
        ]);

        Category::create([
            'name' => 'Smartwatches',
            'description' => 'Wearable smart devices',
            'parent_id' => $electronics->id,
        ]);

        // Sub-categories under Accessories
        Category::create([
            'name' => 'Cases',
            'description' => 'Device cases and covers',
            'parent_id' => $accessories->id,
        ]);

        Category::create([
            'name' => 'Chargers',
            'description' => 'Chargers and power adapters',
            'parent_id' => $accessories->id,
        ]);

        Category::create([
            'name' => 'Cables',
            'description' => 'USB cables and connectors',
            'parent_id' => $accessories->id,
        ]);

        Category::create([
            'name' => 'Screen Protectors',
            'description' => 'Screen guards and protectors',
            'parent_id' => $accessories->id,
        ]);
    }
}
