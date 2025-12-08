<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    public function run(): void
    {
        Supplier::create([
            'supplier_code' => 'SUP-001',
            'company_name' => 'Tech Distributors Inc.',
            'contact_person' => 'John Smith',
            'email' => 'john@techdist.com',
            'phone' => '+63 917 123 4567',
            'address' => '123 Business Ave, Makati City, Metro Manila',
        ]);

        Supplier::create([
            'supplier_code' => 'SUP-002',
            'company_name' => 'Mobile World Supply',
            'contact_person' => 'Maria Santos',
            'email' => 'maria@mobileworld.ph',
            'phone' => '+63 918 234 5678',
            'address' => '456 Commerce St, Quezon City, Metro Manila',
        ]);

        Supplier::create([
            'supplier_code' => 'SUP-003',
            'company_name' => 'Gadget Central Wholesale',
            'contact_person' => 'Robert Tan',
            'email' => 'robert@gadgetcentral.com',
            'phone' => '+63 919 345 6789',
            'address' => '789 Trade Blvd, Pasig City, Metro Manila',
        ]);

        Supplier::create([
            'supplier_code' => 'SUP-004',
            'company_name' => 'Accessory Hub Corp',
            'contact_person' => 'Lisa Cruz',
            'email' => 'lisa@accessoryhub.ph',
            'phone' => '+63 920 456 7890',
            'address' => '321 Market Rd, Taguig City, Metro Manila',
        ]);

        Supplier::create([
            'supplier_code' => 'SUP-005',
            'company_name' => 'Premium Electronics Co.',
            'contact_person' => 'David Garcia',
            'email' => 'david@premiumelec.com',
            'phone' => '+63 921 567 8901',
            'address' => '654 Industrial Park, Mandaluyong City, Metro Manila',
        ]);
    }
}
