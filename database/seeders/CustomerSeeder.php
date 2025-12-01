<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $firstNames = ['Juan', 'Maria', 'Jose', 'Ana', 'Pedro', 'Sofia', 'Miguel', 'Isabel', 'Carlos', 'Elena', 'Roberto', 'Carmen', 'Luis', 'Rosa', 'Antonio'];
        $lastNames = ['Santos', 'Reyes', 'Cruz', 'Bautista', 'Garcia', 'Gonzales', 'Mercado', 'Rodriguez', 'Ramos', 'Torres', 'Hernandez', 'Flores', 'Diaz', 'Martinez', 'Lopez'];
        $cities = ['Manila', 'Quezon City', 'Makati', 'Pasig', 'Taguig', 'Mandaluyong', 'Caloocan', 'Las Piñas', 'Parañaque', 'Pasay'];

        $customers = [];
        
        // Create 50 customers with varied data
        for ($i = 1; $i <= 50; $i++) {
            $firstName = $firstNames[array_rand($firstNames)];
            $lastName = $lastNames[array_rand($lastNames)];
            $city = $cities[array_rand($cities)];
            
            // Determine customer type based on distribution
            if ($i <= 5) {
                $customerType = 'vip';
                $totalSpent = rand(150000, 500000);
                $loyaltyPoints = rand(5000, 15000);
            } elseif ($i <= 15) {
                $customerType = 'wholesale';
                $totalSpent = rand(50000, 200000);
                $loyaltyPoints = rand(2000, 8000);
            } else {
                $customerType = 'regular';
                $totalSpent = rand(5000, 80000);
                $loyaltyPoints = rand(100, 3000);
            }
            
            $customers[] = [
                'customer_code' => 'CUST-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => strtolower($firstName . '.' . $lastName . $i . '@email.com'),
                'phone' => '+63-9' . rand(10, 99) . '-' . rand(100, 999) . '-' . rand(1000, 9999),
                'address' => rand(1, 500) . ' ' . ['Main St', 'Rizal Ave', 'EDSA', 'Taft Ave', 'España Blvd'][array_rand(['Main St', 'Rizal Ave', 'EDSA', 'Taft Ave', 'España Blvd'])],
                'city' => $city,
                'state' => 'Metro Manila',
                'postal_code' => rand(1000, 1900),
                'country' => 'Philippines',
                'customer_type' => $customerType,
                'loyalty_points' => $loyaltyPoints,
                'total_spent' => $totalSpent,
                'is_active' => $i <= 48, // 2 inactive customers
                'created_at' => now()->subDays(rand(1, 365)),
                'updated_at' => now()->subDays(rand(0, 30)),
            ];
        }

        foreach ($customers as $customer) {
            DB::table('customers')->insert($customer);
        }
    }
}
