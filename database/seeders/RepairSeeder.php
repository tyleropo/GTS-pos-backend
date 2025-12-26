<?php

namespace Database\Seeders;

use App\Models\Repair;
use App\Models\Customer;
use Illuminate\Database\Seeder;

class RepairSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::limit(5)->get();

        if ($customers->count() < 5) {
            return; // Need customers first
        }

        Repair::create([
            'ticket_number' => 'REP-20241208-ABC123',
            'customer_id' => $customers[0]->id,
            'device' => 'iPhone 14 Pro',
            'serial_number' => 'F1234ABC5678',
            'status' => 'pending',
            'issue_description' => 'Cracked screen, touch not responsive in top left corner',
            'promised_at' => now()->addDays(3),
        ]);

        Repair::create([
            'ticket_number' => 'REP-20241208-DEF456',
            'customer_id' => $customers[1]->id,
            'device' => 'Samsung Galaxy S23',
            'serial_number' => 'R9876DEF4321',
            'status' => 'in_progress',
            'issue_description' => 'Battery drains quickly, phone gets hot',
            'promised_at' => now()->addDays(2),
        ]);

        Repair::create([
            'ticket_number' => 'REP-20241207-GHI789',
            'customer_id' => $customers[2]->id,
            'device' => 'iPad Pro 11"',
            'serial_number' => 'DM123GHI789',
            'status' => 'completed',
            'issue_description' => 'Charging port not working',
            'resolution' => 'Replaced charging port assembly, tested successfully',
            'promised_at' => now()->subDays(1),
        ]);

        Repair::create([
            'ticket_number' => 'REP-20241206-JKL012',
            'customer_id' => $customers[3]->id,
            'device' => 'MacBook Air M2',
            'serial_number' => 'C02A1JKL012',
            'status' => 'pending',
            'issue_description' => 'Keyboard keys stuck, liquid spill suspected',
            'promised_at' => now()->addDays(5),
        ]);

        Repair::create([
            'ticket_number' => 'REP-20241205-MNO345',
            'customer_id' => $customers[4]->id,
            'device' => 'Xiaomi 13 Pro',
            'serial_number' => NULL,
            'status' => 'in_progress',
            'issue_description' => 'Camera app crashes, rear camera not focusing',
            'promised_at' => now()->addDays(1),
        ]);

        // Overdue repair
        Repair::create([
            'ticket_number' => 'REP-20241201-PQR678',
            'customer_id' => $customers[0]->id,
            'device' => 'ASUS ROG Phone 7',
            'serial_number' => 'N4567PQR890',
            'status' => 'pending',
            'issue_description' => 'Screen flickering, display artifacts',
            'promised_at' => now()->subDays(3), // Overdue
        ]);
    }
}
