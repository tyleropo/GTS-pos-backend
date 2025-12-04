<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_code' => 'SUP001',
                'company_name' => 'TechWorld Distributors',
                'contact_person' => 'John Smith',
                'email' => 'john@techworld.com',
                'phone' => '+1-555-0101',
                'phone_secondary' => '+1-555-0102',
                'address_street' => '123 Tech Avenue',
                'address_city' => 'San Francisco',
                'address_state' => 'CA',
                'address_postal_code' => '94102',
                'address_country' => 'USA',
                'payment_terms' => 'Net 30',
                'credit_limit' => 50000.00,
                'is_active' => true,
                'notes' => 'Primary electronics supplier',
            ],
            [
                'supplier_code' => 'SUP002',
                'company_name' => 'Global Electronics Inc',
                'contact_person' => 'Sarah Johnson',
                'email' => 'sarah@globalelec.com',
                'phone' => '+1-555-0201',
                'address_street' => '456 Commerce Street',
                'address_city' => 'New York',
                'address_state' => 'NY',
                'address_postal_code' => '10001',
                'address_country' => 'USA',
                'payment_terms' => 'Net 45',
                'credit_limit' => 75000.00,
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP003',
                'company_name' => 'Digital Components Ltd',
                'contact_person' => 'Michael Chen',
                'email' => 'michael@digitalcomp.com',
                'phone' => '+1-555-0301',
                'address_street' => '789 Innovation Way',
                'address_city' => 'Austin',
                'address_state' => 'TX',
                'address_postal_code' => '78701',
                'address_country' => 'USA',
                'payment_terms' => 'Net 30',
                'credit_limit' => 30000.00,
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP004',
                'company_name' => 'AccessoryHub Pro',
                'contact_person' => 'Emily Davis',
                'email' => 'emily@accessoryhub.com',
                'phone' => '+1-555-0401',
                'address_street' => '321 Market Plaza',
                'address_city' => 'Seattle',
                'address_state' => 'WA',
                'address_postal_code' => '98101',
                'address_country' => 'USA',
                'payment_terms' => 'Net 15',
                'credit_limit' => 20000.00,
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP005',
                'company_name' => 'Premium Parts Wholesale',
                'contact_person' => 'David Wilson',
                'email' => 'david@premiumparts.com',
                'phone' => '+1-555-0501',
                'address_street' => '555 Business Park Drive',
                'address_city' => 'Chicago',
                'address_state' => 'IL',
                'address_postal_code' => '60601',
                'address_country' => 'USA',
                'payment_terms' => 'Net 60',
                'credit_limit' => 100000.00,
                'is_active' => true,
                'notes' => 'Premium supplier for high-end products',
            ],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }
    }
}
