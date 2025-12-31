<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the users we want to convert to employees
        $neil = User::where('email', 'neil@gtsmarketing.com')->first();
        $liezl = User::where('email', 'liezl@gtsmarketing.com')->first();
        $jan = User::where('email', 'janbingo@gtsmarketing.com')->first();

        // Create employee record for Neil Opo
        // Roles: manager, cashier, technician -> Position: Store Manager
        if ($neil) {
            Employee::create([
                'first_name' => $neil->first_name,
                'last_name' => $neil->last_name,
                'email' => $neil->email,
                'phone' => $neil->phone,
                'position' => 'Store Manager',
                'department' => 'Operations',
                'salary' => 35000.00,
                'hire_date' => '2023-01-15',
                'status' => 'active',
                'user_id' => $neil->id,
            ]);
        }

        // Create employee record for Liezl Opo
        // Roles: manager, cashier -> Position: Assistant Manager
        if ($liezl) {
            Employee::create([
                'first_name' => $liezl->first_name,
                'last_name' => $liezl->last_name,
                'email' => $liezl->email,
                'phone' => $liezl->phone,
                'position' => 'Store Manager',
                'department' => 'Operations',
                'salary' => 30000.00,
                'hire_date' => '2023-02-01',
                'status' => 'active',
                'user_id' => $liezl->id,
            ]);
        }

        // Create employee record for Jan Bingo
        // Roles: cashier, technician -> Position: Sales Technician
        if ($jan) {
            Employee::create([
                'first_name' => $jan->first_name,
                'last_name' => $jan->last_name,
                'email' => $jan->email,
                'phone' => $jan->phone,
                'position' => 'Sales Technician',
                'department' => 'Sales & Service',
                'salary' => 20000.00,
                'hire_date' => '2023-03-10',
                'status' => 'active',
                'user_id' => $jan->id,
            ]);
        }
    }
}
