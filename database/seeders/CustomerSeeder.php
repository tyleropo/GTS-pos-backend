<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        // Retail customers
        Customer::create([
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.delacruz@gmail.com',
            'phone' => '+63 917 123 4567',
            'address' => '123 Mabini St, Manila City',
        ]);

        Customer::create([
            'name' => 'Maria Santos',
            'email' => 'maria.santos@yahoo.com',
            'phone' => '+63 918 234 5678',
            'address' => '456 Rizal Ave, Quezon City',
        ]);

        Customer::create([
            'name' => 'Pedro Gonzales',
            'email' => 'pedro.g@gmail.com',
            'phone' => '+63 919 345 6789',
            'address' => '789 Bonifacio St, Makati City',
        ]);

        // Business customers (B2B)
        Customer::create([
            'name' => 'Robert Tan',
            'email' => 'procurement@techcorp.ph',
            'phone' => '+63 920 456 7890',
            'address' => '321 BGC Drive, Taguig City',
            'company' => 'Tech Corp Philippines Inc.',
        ]);

        Customer::create([
            'name' => 'Lisa Reyes',
            'email' => 'purchasing@startupco.com.ph',
            'phone' => '+63 921 567 8901',
            'address' => '654 Ortigas Ave, Pasig City',
            'company' => 'StartupCo',
        ]);

        Customer::create([
            'name' => 'Michael Cruz',
            'email' => 'admin@smecompany.ph',
            'phone' => '+63 922 678 9012',
            'address' => '987 Shaw Blvd, Mandaluyong City',
            'company' => 'SME Company Ltd.',
        ]);

        // Walk-in customers (no email)
        Customer::create([
            'name' => 'Ana Garcia',
            'phone' => '+63 923 789 0123',
            'address' => '147 Quezon Ave, Quezon City',
        ]);

        Customer::create([
            'name' => 'Carlos Mendoza',
            'phone' => '+63 924 890 1234',
            'address' => '258 Taft Ave, Manila City',
        ]);
    }
}
