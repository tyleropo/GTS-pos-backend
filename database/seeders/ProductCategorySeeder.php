<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Mobile Phones & Smartphones',
            'Tablets & E-Readers',
            'Wearables',
            'Televisions',
            'Audio Equipment',
            'Cameras & Camcorders',
            'Drones & Action Cameras',
            'Home Appliances',
            'Gaming Consoles',
            'Computers',
            'Laptops',
            'Desktops',
            'All-in-One PCs',
            'Mini PCs',
            'Servers & Workstations',
            'Device Accessories',
            'Phone Cases & Covers',
            'Screen Protectors',
            'Chargers & Adapters',
            'Headphones & Earbuds',
            'Power Banks',
            'Bluetooth & Smart Accessories',
            'Computer Accessories',
            'Computer Input/Output Devices',
            'Computer Parts',
            'External Hard Drives & SSDs',
            'USB Hubs & Docking Stations',
            'Stands & Mounts',
            'Smart Home & IoT',
            'Smart Plugs & Switches',
            'General Accessories',
            'Bags & Sleeves',
            'Cleaning Kits',
            'Tools & Maintenance',
            'Electronic Repair Kits',
            'Soldering Tools',
            'Voltage Testers',
            'Multimeters',
            'Cable Management Tools',
            'Anti-Static Equipment',
            'Digital Content',
            'Software & Digital Products',
            'Gift Cards',
            'Promotional Items',
            'Extended Warranties',
            'Refurbished & Used',
            'Refurbished Electronics',
        ];

        foreach ($categories as $category) {
            ProductCategory::create([
                'title' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
