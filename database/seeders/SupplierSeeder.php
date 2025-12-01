<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'supplier_code' => 'SUP-001',
                'company_name' => 'Samsung Philippines',
                'contact_person' => 'John Lee',
                'email' => 'orders@samsung.ph',
                'phone' => '+63-2-8555-7777',
                'address' => '5th Floor, One Global Place, 5th Avenue corner 25th Street',
                'city' => 'Taguig City',
                'state' => 'Metro Manila',
                'postal_code' => '1634',
                'country' => 'Philippines',
                'payment_terms' => 'Net 30',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-002',
                'company_name' => 'Apple Authorized Distributor',
                'contact_person' => 'Maria Garcia',
                'email' => 'supply@applestore.ph',
                'phone' => '+63-2-8888-8888',
                'address' => 'BGC Central Plaza Tower',
                'city' => 'Taguig City',
                'state' => 'Metro Manila',
                'postal_code' => '1630',
                'country' => 'Philippines',
                'payment_terms' => 'Net 45',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-003',
                'company_name' => 'Xiaomi Philippines Inc.',
                'contact_person' => 'Chen Wang',
                'email' => 'business@xiaomi.ph',
                'phone' => '+63-2-7777-6666',
                'address' => 'Skyrise 4B, IT Park',
                'city' => 'Cebu City',
                'state' => 'Cebu',
                'postal_code' => '6000',
                'country' => 'Philippines',
                'payment_terms' => 'Net 30',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-004',
                'company_name' => 'Huawei Technologies',
                'contact_person' => 'Li Zhang',
                'email' => 'sales@huawei.ph',
                'phone' => '+63-2-5555-4444',
                'address' => 'Tower 2, Ayala Avenue',
                'city' => 'Makati City',
                'state' => 'Metro Manila',
                'postal_code' => '1226',
                'country' => 'Philippines',
                'payment_terms' => 'Net 30',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-005',
                'company_name' => 'Realme Distribution Co.',
                'contact_person' => 'Raj Kumar',
                'email' => 'orders@realme.ph',
                'phone' => '+63-2-9999-3333',
                'address' => 'Commerce Plaza Building',
                'city' => 'Quezon City',
                'state' => 'Metro Manila',
                'postal_code' => '1100',
                'country' => 'Philippines',
                'payment_terms' => 'Net 30',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-006',
                'company_name' => 'Oppo Mobile Philippines',
                'contact_person' => 'Sarah Tan',
                'email' => 'wholesale@oppo.ph',
                'phone' => '+63-2-6666-5555',
                'address' => 'Eastwood Cyber Park',
                'city' => 'Quezon City',
                'state' => 'Metro Manila',
                'postal_code' => '1110',
                'country' => 'Philippines',
                'payment_terms' => 'Net 30',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-007',
                'company_name' => 'Vivo Philippines Corporation',
                'contact_person' => 'Michael Chen',
                'email' => 'supply@vivo.ph',
                'phone' => '+63-2-4444-3333',
                'address' => 'SM Cyber West',
                'city' => 'Quezon City',
                'state' => 'Metro Manila',
                'postal_code' => '1104',
                'country' => 'Philippines',
                'payment_terms' => 'Net 30',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-008',
                'company_name' => 'Accessory World Distributors',
                'contact_person' => 'Anna Santos',
                'email' => 'sales@accessoryworld.ph',
                'phone' => '+63-2-3333-2222',
                'address' => 'Greenhills Shopping Center',
                'city' => 'San Juan City',
                'state' => 'Metro Manila',
                'postal_code' => '1502',
                'country' => 'Philippines',
                'payment_terms' => 'Net 15',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-009',
                'company_name' => 'Dell Technologies Philippines',
                'contact_person' => 'Robert Johnson',
                'email' => 'orders@dell.ph',
                'phone' => '+63-2-8888-1111',
                'address' => 'Net Lima Building, 5th Avenue',
                'city' => 'Taguig City',
                'state' => 'Metro Manila',
                'postal_code' => '1634',
                'country' => 'Philippines',
                'payment_terms' => 'Net 45',
                'is_active' => true,
            ],
            [
                'supplier_code' => 'SUP-010',
                'company_name' => 'Lenovo Philippines Inc.',
                'contact_person' => 'David Wong',
                'email' => 'business@lenovo.ph',
                'phone' => '+63-2-7777-1111',
                'address' => 'Ayala North Exchange',
                'city' => 'Quezon City',
                'state' => 'Metro Manila',
                'postal_code' => '1105',
                'country' => 'Philippines',
                'payment_terms' => 'Net 30',
                'is_active' => true,
            ],
        ];

        foreach ($suppliers as $supplier) {
            DB::table('suppliers')->insert(array_merge($supplier, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
