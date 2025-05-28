<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'All',
            'All-in-One PCs',
            'Anti-Static Equipment',
            'Audio Equipment',
            'Bags & Sleeves',
            'Bluetooth & Smart Accessories',
            'Cable Management Tools',
            'Cameras & Camcorders',
            'Chargers & Adapters',
            'Cleaning Kits',
            'Computers',
            'Computer Accessories',
            'Computer Input/Output Devices',
            'Computer Parts',
            'Desktops',
            'Device Accessories',
            'Digital Content',
            'Drones & Action Cameras',
            'Electronic Repair Kits',
            'Extended Warranties',
            'External Hard Drives & SSDs',
            'General Accessories',
            'Gift Cards',
            'Headphones & Earbuds',
            'Home Appliances',
            'Gaming Consoles',           
            'Laptops',
            'Mini PCs',
            'Mobile Phones & Smartphones',
            'Multimeters',            
            'Phone Cases & Covers',
            'Power Banks',
            'Promotional Items',
            'Refurbished & Used',
            'Screen Protectors',
            'Servers & Workstations',
            'Smart Home & IoT',
            'Smart Plugs & Switches',
            'Software & Digital Products',
            'Soldering Tools',
            'Stands & Mounts',
            'Tablets & E-Readers',
            'Televisions',
            'Tools & Maintenance',
            'USB Hubs & Docking Stations',
            'Voltage Testers',
            'Wearables',
        ];

        foreach ($categories as $category) {
            DB::table('product_categories')->insert([
                'name' => $category,
            ]);
        }
    }
}
