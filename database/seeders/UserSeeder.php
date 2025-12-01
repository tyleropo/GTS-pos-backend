<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin users
        User::factory()->create([
            'name' => 'Tyler Christina D. Opo',
            'first_name' => 'Tyler Christina',
            'last_name' => 'Opo',
            'email' => 'fake1@admin.com',
            'password' => 'Hatdog123.',
            'role' => 'admin',
            'is_active' => true,
            'last_login_at' => now()->subDays(1),
        ]);

        User::factory()->create([
            'name' => 'Ma. Cristina M. Pasague',
            'first_name' => 'Ma. Cristina',
            'last_name' => 'Pasague',
            'email' => 'fake2@admin.com',
            'password' => 'Annyeong1.',
            'role' => 'admin',
            'is_active' => true,
            'last_login_at' => now()->subDays(2),
        ]);

        // Managers
        User::factory()->create([
            'name' => 'Jerico P. Paster',
            'first_name' => 'Jerico',
            'last_name' => 'Paster',
            'email' => 'fake3@admin.com',
            'password' => 'JuswaTheGreat1.',
            'role' => 'manager',
            'is_active' => true,
            'last_login_at' => now()->subHours(6),
        ]);

        User::factory()->create([
            'name' => 'Maria Santos',
            'first_name' => 'Maria',
            'last_name' => 'Santos',
            'email' => 'manager@pos.com',
            'password' => 'password',
            'role' => 'manager',
            'is_active' => true,
            'last_login_at' => now()->subHours(12),
        ]);

        // Cashiers
        User::factory()->create([
            'name' => 'Juan Dela Cruz',
            'first_name' => 'Juan',
            'last_name' => 'Dela Cruz',
            'email' => 'cashier1@pos.com',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => true,
            'last_login_at' => now()->subHours(2),
        ]);

        User::factory()->create([
            'name' => 'Anna Reyes',
            'first_name' => 'Anna',
            'last_name' => 'Reyes',
            'email' => 'cashier2@pos.com',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => true,
            'last_login_at' => now()->subHours(3),
        ]);

        User::factory()->create([
            'name' => 'Pedro Garcia',
            'first_name' => 'Pedro',
            'last_name' => 'Garcia',
            'email' => 'cashier3@pos.com',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => true,
            'last_login_at' => now()->subHours(5),
        ]);

        // Technicians
        User::factory()->create([
            'name' => 'Miguel Torres',
            'first_name' => 'Miguel',
            'last_name' => 'Torres',
            'email' => 'tech1@pos.com',
            'password' => 'password',
            'role' => 'technician',
            'is_active' => true,
            'last_login_at' => now()->subHours(4),
        ]);

        User::factory()->create([
            'name' => 'Rafael Diaz',
            'first_name' => 'Rafael',
            'last_name' => 'Diaz',
            'email' => 'tech2@pos.com',
            'password' => 'password',
            'role' => 'technician',
            'is_active' => true,
            'last_login_at' => now()->subHours(8),
        ]);

        // Inactive user for testing
        User::factory()->create([
            'name' => 'Inactive User',
            'first_name' => 'Inactive',
            'last_name' => 'User',
            'email' => 'inactive@pos.com',
            'password' => 'password',
            'role' => 'cashier',
            'is_active' => false,
            'last_login_at' => now()->subDays(30),
        ]);
    }
}
