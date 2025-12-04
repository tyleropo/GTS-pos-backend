<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = [
            [
                'name' => 'John Smith',
                'email' => 'john.smith@example.com',
                'phone' => '+1-555-0101',
                'address' => '123 Main St, Anytown, CA 12345',
                'type' => 'Regular',
                'status' => 'Active',
                'total_spent' => 2499.95,
                'orders' => 5,
                'last_purchase' => now()->subDays(3),
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.j@example.com',
                'phone' => '+1-555-0201',
                'address' => '456 Oak Ave, Somewhere, NY 67890',
                'type' => 'VIP',
                'status' => 'Active',
                'total_spent' => 8750.50,
                'orders' => 15,
                'last_purchase' => now()->subDays(1),
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'mbrown@example.com',
                'phone' => '+1-555-0301',
                'address' => '789 Pine Rd, Elsewhere, TX 54321',
                'type' => 'VIP',
                'status' => 'Active',
                'total_spent' => 12350.00,
                'orders' => 22,
                'last_purchase' => now()->subDays(5),
            ],
            [
                'name' => 'Emily Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '+1-555-0401',
                'address' => '321 Cedar Ln, Nowhere, FL 13579',
                'type' => 'Regular',
                'status' => 'Active',
                'total_spent' => 1299.99,
                'orders' => 3,
                'last_purchase' => now()->subDays(30),
            ],
            [
                'name' => 'David Wilson',
                'email' => 'dwilson@example.com',
                'phone' => '+1-555-0501',
                'address' => '654 Maple Dr, Anywhere, WA 97531',
                'type' => 'Regular',
                'status' => 'Active',
                'total_spent' => 3269.96,
                'orders' => 7,
                'last_purchase' => now()->subDays(10),
            ],
            [
                'name' => 'Jessica Taylor',
                'email' => 'jtaylor@example.com',
                'phone' => '+1-555-0601',
                'address' => '987 Birch Ct, Someplace, IL 24680',
                'type' => 'Regular',
                'status' => 'Inactive',
                'total_spent' => 549.98,
                'orders' => 2,
                'last_purchase' => now()->subDays(120),
            ],
            [
                'name' => 'Robert Martinez',
                'email' => 'rmartinez@example.com',
                'phone' => '+1-555-0701',
                'address' => '159 Elm St, Othertown, GA 86420',
                'type' => 'VIP',
                'status' => 'Active',
                'total_spent' => 15599.97,
                'orders' => 28,
                'last_purchase' => now()->subDays(2),
            ],
            [
                'name' => 'Jennifer Anderson',
                'email' => 'janderson@example.com',
                'phone' => '+1-555-0801',
                'address' => '753 Spruce Ave, Thatplace, MI 97531',
                'type' => 'Regular',
                'status' => 'Active',
                'total_spent' => 899.99,
                'orders' => 2,
                'last_purchase' => now()->subDays(15),
            ],
            [
                'name' => 'Christopher Thomas',
                'email' => 'cthomas@example.com',
                'phone' => '+1-555-0901',
                'address' => '246 Willow Rd, Thisplace, OR 13579',
                'type' => 'Regular',
                'status' => 'Active',
                'total_spent' => 4279.94,
                'orders' => 9,
                'last_purchase' => now()->subDays(7),
            ],
            [
                'name' => 'Amanda White',
                'email' => 'awhite@example.com',
                'phone' => '+1-555-1001',
                'address' => '864 Aspen Ln, Thattown, AZ 24680',
                'type' => 'Regular',
                'status' => 'Active',
                'total_spent' => 1689.98,
                'orders' => 4,
                'last_purchase' => now()->subDays(20),
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }

        $this->command->info('Created ' . count($customers) . ' customers');
    }
}
